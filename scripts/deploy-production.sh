#!/usr/bin/env bash

set -Eeuo pipefail

# ==========================================
# Production deployment
# ==========================================

PROJECT_ROOT="/home/ssm-user/vue-laravel-spa"
RELEASES_DIR="${PROJECT_ROOT}/releases"
CURRENT_LINK="${PROJECT_ROOT}/current"

GIT_REMOTE="origin"
GIT_BRANCH="main"
COMPOSE_PROJECT="vue-laravel-spa"

HEALTHCHECK_URL="${HEALTHCHECK_URL:-http://127.0.0.1/up}"
HEALTHCHECK_ATTEMPTS=24
HEALTHCHECK_INTERVAL=5

EXPECTED_VOLUMES=(
    "vue-laravel-spa_public_dummy_uploads"
    "vue-laravel-spa_public_file_uploads"
    "vue-laravel-spa_public_item_uploads"
    "vue-laravel-spa_public_outfit_uploads"
    "vue-laravel-spa_public_user_uploads"
)

STAGING_DIR=""
TARGET_SHA=""
RELEASE_DIR=""
RELEASE_CREATED=0

LOCK_DIR="${PROJECT_ROOT}/.deploy-production.lock"
LOCK_ACQUIRED=0

CURRENT_LINK_TMP=""
PREVIOUS_CURRENT_EXISTS=0

ROLLBACK_COMPOSE_DIR=""
ROLLBACK_IMAGE_TAG=""
ROLLBACK_REQUIRED=0
ROLLBACK_FAILED=0

# ==========================================
# Logging
# ==========================================

log() {
    printf '[deploy] %s\n' "$1"
}

fail() {
    printf '[deploy] ERROR: %s\n' "$1" >&2
    exit 1
}

# ==========================================
# Compose helper
# ==========================================

run_compose() {
    local compose_dir="$1"
    local image_tag="$2"

    shift 2

    DEPLOY_IMAGE_TAG="$image_tag" \
    COMPOSE_PARALLEL_LIMIT=1 \
    docker compose \
        -p "$COMPOSE_PROJECT" \
        --project-directory "$compose_dir" \
        --env-file "$PROJECT_ROOT/.env" \
        -f "$compose_dir/compose.prod.yml" \
        "$@"
}

# ==========================================
# Volume mapping validation
# ==========================================

validate_volume_mapping() {
    local compose_dir="$1"
    local image_tag="$2"
    local volume
    local logical_name
    local output

    output="$(
        run_compose "$compose_dir" "$image_tag" config --format json
    )" || return 1

    for volume in "${EXPECTED_VOLUMES[@]}"; do
        logical_name="${volume#vue-laravel-spa_}"

        if ! printf '%s' "$output" \
            | jq -e \
                --arg logical "$logical_name" \
                --arg expected "$volume" \
                '.volumes[$logical].name == $expected' \
                >/dev/null; then
            log "Invalid volume mapping: ${logical_name}"
            return 1
        fi
    done

    return 0
}

# ==========================================
# Image validation
# ==========================================

verify_images() {
    local image_tag="$1"

    docker image inspect \
        "daily-outfit-app:${image_tag}" \
        >/dev/null 2>&1 \
        || return 1

    docker image inspect \
        "daily-outfit-nginx:${image_tag}" \
        >/dev/null 2>&1 \
        || return 1

    return 0
}

# ==========================================
# Service and HTTP health check
# ==========================================

wait_for_healthy() {
    local compose_dir="$1"
    local image_tag="$2"

    local attempt
    local service
    local container_id
    local running
    local services_running

    log "Waiting for application health check."

    for ((attempt = 1; attempt <= HEALTHCHECK_ATTEMPTS; attempt++)); do
        services_running=1

        for service in app reverb nginx; do
            container_id="$(
                run_compose "$compose_dir" "$image_tag" ps -q "$service" \
                    2>/dev/null
            )" || services_running=0

            if [[ -z "$container_id" ]]; then
                services_running=0
                break
            fi

            running="$(
                docker inspect \
                    --format '{{.State.Running}}' \
                    "$container_id" 2>/dev/null
            )" || services_running=0

            if [[ "$running" != "true" ]]; then
                services_running=0
                break
            fi
        done

        if [[ "$services_running" -eq 1 ]] \
            && curl -fsS \
                --max-time 5 \
                --output /dev/null \
                "$HEALTHCHECK_URL" 2>/dev/null; then

            log "All services are running."
            log "HTTP health check passed."
            return 0
        fi

        log "Health check attempt ${attempt}/${HEALTHCHECK_ATTEMPTS} failed."

        sleep "$HEALTHCHECK_INTERVAL"
    done

    log "Health check timed out."
    return 1
}

# ==========================================
# Restore current release pointer
# ==========================================

restore_current_pointer() {
    local current_target

    current_target="$(
        readlink -f -- "$CURRENT_LINK" 2>/dev/null || true
    )"

    if [[ "$current_target" != "$RELEASE_DIR" ]]; then
        return 0
    fi

    if [[ "$PREVIOUS_CURRENT_EXISTS" -eq 1 ]]; then
        local restore_tmp="${PROJECT_ROOT}/.current-rollback.$$"

        if ! ln -s "$ROLLBACK_COMPOSE_DIR" "$restore_tmp"; then
            return 1
        fi

        if ! mv -Tf -- "$restore_tmp" "$CURRENT_LINK"; then
            rm -f -- "$restore_tmp"
            return 1
        fi
    else
        if ! rm -f -- "$CURRENT_LINK"; then
            return 1
        fi
    fi

    log "Previous current release pointer restored."
    return 0
}

# ==========================================
# Cleanup and automatic rollback
# ==========================================

cleanup() {
    local exit_code=$?

    trap - EXIT
    trap '' INT TERM
    set +e

    # Remove only the staging directory created by this script.
    if [[ -n "$STAGING_DIR" \
        && ( -e "$STAGING_DIR" || -L "$STAGING_DIR" ) ]]; then

        case "$STAGING_DIR" in
            "${RELEASES_DIR}/.staging-${TARGET_SHA}".*)
                log "Removing incomplete staging directory."

                if ! rm -rf -- "$STAGING_DIR"; then
                    log "WARNING: Could not remove staging directory."
                fi
                ;;
            *)
                log "WARNING: Unexpected staging path. Skipping cleanup."
                ;;
        esac
    fi

    # Remove an incomplete temporary current pointer.
    if [[ -n "$CURRENT_LINK_TMP" \
        && ( -e "$CURRENT_LINK_TMP" || -L "$CURRENT_LINK_TMP" ) ]]; then
        rm -f -- "$CURRENT_LINK_TMP"
    fi

    # Roll back if production update failed.
    if [[ "$ROLLBACK_REQUIRED" -eq 1 ]]; then
        log "Deployment failed after production update started."
        log "Starting automatic rollback."

        if run_compose \
            "$ROLLBACK_COMPOSE_DIR" \
            "$ROLLBACK_IMAGE_TAG" \
            up -d --no-build app reverb nginx; then

            if wait_for_healthy \
                "$ROLLBACK_COMPOSE_DIR" \
                "$ROLLBACK_IMAGE_TAG"; then

                log "Rollback completed successfully."

                if ! restore_current_pointer; then
                    log "ERROR: Could not restore current release pointer."
                    ROLLBACK_FAILED=1
                fi
            else
                log "ERROR: Rollback health check failed."
                ROLLBACK_FAILED=1
            fi
        else
            log "ERROR: Rollback Compose update failed."
            ROLLBACK_FAILED=1
        fi
    fi

    # Remove only the release directory created by this invocation.
    # Keep it when rollback failed or current still points to it.
    if [[ "$exit_code" -ne 0 && "$RELEASE_CREATED" -eq 1 ]]; then

        if [[ "$ROLLBACK_FAILED" -eq 1 ]]; then
            log "Keeping failed release for manual investigation."

        else
            current_target="$(
                readlink -f -- "$CURRENT_LINK" 2>/dev/null || true
            )"

            if [[ "$current_target" == "$RELEASE_DIR" ]]; then
                log "ERROR: Current pointer still references the failed release."
                log "Keeping release and deployment lock for manual investigation."
                ROLLBACK_FAILED=1

            elif [[ "$RELEASE_DIR" != "${RELEASES_DIR}/${TARGET_SHA}" ]]; then
                log "ERROR: Unexpected release path. Skipping cleanup."
                ROLLBACK_FAILED=1

            elif [[ -L "$RELEASE_DIR" ]]; then
                log "ERROR: Release path is a symbolic link. Skipping cleanup."
                ROLLBACK_FAILED=1

            elif [[ -d "$RELEASE_DIR" ]]; then
                log "Removing failed release created by this deployment."

                if rm -rf -- "$RELEASE_DIR"; then
                    log "Failed release directory removed."
                else
                    log "ERROR: Could not remove failed release."
                    ROLLBACK_FAILED=1
                fi

            elif [[ -e "$RELEASE_DIR" ]]; then
                log "ERROR: Unexpected release path type. Skipping cleanup."
                ROLLBACK_FAILED=1
            fi
        fi
    fi

    # Release the lock only when rollback is safe.
    if [[ "$LOCK_ACQUIRED" -eq 1 ]]; then
        if [[ "$ROLLBACK_FAILED" -eq 1 ]]; then
            log "CRITICAL: Deployment lock is being retained."
            log "Manual investigation is required."
        elif ! rmdir -- "$LOCK_DIR" 2>/dev/null; then
            log "WARNING: Could not remove deployment lock."
        fi
    fi

    if [[ "$exit_code" -ne 0 ]]; then
        log "Deployment failed."
    fi

    exit "$exit_code"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# ==========================================
# 1. Input validation
# ==========================================

if [[ $# -ne 1 ]]; then
    fail "Usage: deploy-production.sh <40-character-commit-sha>"
fi

TARGET_SHA="$1"

if [[ ! "$TARGET_SHA" =~ ^[0-9a-f]{40}$ ]]; then
    fail "Invalid commit SHA."
fi

RELEASE_DIR="${RELEASES_DIR}/${TARGET_SHA}"

# ==========================================
# 2. Environment validation
# ==========================================

[[ -d "$PROJECT_ROOT" ]] \
    || fail "Project directory not found."

[[ -d "$PROJECT_ROOT/.git" ]] \
    || fail "Git repository not found."

[[ -f "$PROJECT_ROOT/.env" ]] \
    || fail "Production .env not found."

[[ ! -L "$PROJECT_ROOT/.env" ]] \
    || fail "Production .env must be a regular file."

[[ -r "$PROJECT_ROOT/.env" ]] \
    || fail "Production .env is not readable."

if [[ -L "$RELEASES_DIR" ]]; then
    fail "Releases directory must not be a symbolic link."
elif [[ -e "$RELEASES_DIR" ]]; then
    [[ -d "$RELEASES_DIR" ]] \
        || fail "Releases path exists but is not a directory."
else
    mkdir -p "$RELEASES_DIR" \
        || fail "Could not create releases directory."
fi

command -v docker >/dev/null 2>&1 \
    || fail "Docker is unavailable."

command -v git >/dev/null 2>&1 \
    || fail "Git is unavailable."

command -v tar >/dev/null 2>&1 \
    || fail "tar is unavailable."

command -v jq >/dev/null 2>&1 \
    || fail "jq is unavailable."

command -v curl >/dev/null 2>&1 \
    || fail "curl is unavailable."

command -v readlink >/dev/null 2>&1 \
    || fail "readlink is unavailable."

command -v mv >/dev/null 2>&1 \
    || fail "mv is unavailable."

# Confirm that the expected repository is being used.
GIT_ROOT="$(
    git -c safe.directory="$PROJECT_ROOT" \
        -C "$PROJECT_ROOT" rev-parse --show-toplevel
)"

[[ "$GIT_ROOT" == "$PROJECT_ROOT" ]] \
    || fail "Unexpected Git repository root."

# ==========================================
# 3. Deployment lock
# ==========================================

if ! mkdir "$LOCK_DIR" 2>/dev/null; then
    fail "Another deployment may be running. Check the existing lock."
fi

LOCK_ACQUIRED=1

log "Deployment lock acquired."

# ==========================================
# 4. Check existing release and current pointer
# ==========================================

if [[ -e "$RELEASE_DIR" || -L "$RELEASE_DIR" ]]; then
    fail "Release already exists: ${TARGET_SHA}"
fi

if [[ -e "$CURRENT_LINK" && ! -L "$CURRENT_LINK" ]]; then
    fail "Current release path exists but is not a symbolic link."
fi

# ==========================================
# 5. Determine rollback target
# ==========================================

if [[ -L "$CURRENT_LINK" ]]; then
    log "Existing release pointer found."

    ROLLBACK_COMPOSE_DIR="$(
        readlink -f -- "$CURRENT_LINK"
    )" || fail "Could not resolve current release pointer."

    RELEASES_REAL="$(readlink -f -- "$RELEASES_DIR")"

    [[ -d "$ROLLBACK_COMPOSE_DIR" ]] \
        || fail "Current release directory is missing."

    [[ "$(dirname "$ROLLBACK_COMPOSE_DIR")" == "$RELEASES_REAL" ]] \
        || fail "Current release points outside releases directory."

    ROLLBACK_IMAGE_TAG="$(basename "$ROLLBACK_COMPOSE_DIR")"

    [[ "$ROLLBACK_IMAGE_TAG" =~ ^[0-9a-f]{40}$ ]] \
        || fail "Current release has an invalid SHA."

    PREVIOUS_CURRENT_EXISTS=1
else
    log "No previous release pointer found."
    log "Using existing production configuration for rollback."

    ROLLBACK_COMPOSE_DIR="$PROJECT_ROOT"
    ROLLBACK_IMAGE_TAG="latest"

    [[ -f "$PROJECT_ROOT/compose.prod.yml" ]] \
        || fail "Existing production Compose file is unavailable."
fi

# Validate rollback configuration and images before building.
run_compose \
    "$ROLLBACK_COMPOSE_DIR" \
    "$ROLLBACK_IMAGE_TAG" \
    config --quiet \
    || fail "Rollback Compose configuration is invalid."

verify_images "$ROLLBACK_IMAGE_TAG" \
    || fail "Rollback images are unavailable."

validate_volume_mapping \
    "$ROLLBACK_COMPOSE_DIR" \
    "$ROLLBACK_IMAGE_TAG" \
    || fail "Rollback volume mapping is invalid."

log "Rollback target verified."

# ==========================================
# 6. Fetch GitHub main
# ==========================================

log "Fetching origin/main."

git -c safe.directory="$PROJECT_ROOT" \
    -C "$PROJECT_ROOT" fetch \
    --no-tags \
    "$GIT_REMOTE" \
    "refs/heads/${GIT_BRANCH}:refs/remotes/${GIT_REMOTE}/${GIT_BRANCH}"

# ==========================================
# 7. Verify target commit
# ==========================================

log "Checking target commit."

if ! git -c safe.directory="$PROJECT_ROOT" \
    -C "$PROJECT_ROOT" cat-file -e \
    "${TARGET_SHA}^{commit}" 2>/dev/null; then
    fail "Target commit does not exist."
fi

if ! git -c safe.directory="$PROJECT_ROOT" \
    -C "$PROJECT_ROOT" merge-base --is-ancestor \
    "$TARGET_SHA" \
    "refs/remotes/${GIT_REMOTE}/${GIT_BRANCH}"; then
    fail "Target commit is not contained in origin/main."
fi

log "Target commit verified."

# ==========================================
# 8. Create staging directory
# ==========================================

STAGING_DIR="$(
    mktemp -d "${RELEASES_DIR}/.staging-${TARGET_SHA}.XXXXXX"
)"

log "Staging directory created."

# ==========================================
# 9. Extract source archive
# ==========================================

log "Extracting source archive."

git -c safe.directory="$PROJECT_ROOT" \
    -C "$PROJECT_ROOT" archive "$TARGET_SHA" \
    | tar -x -C "$STAGING_DIR"

# ==========================================
# 9.1 Set staging directory permissions
# ==========================================

chmod 755 "$STAGING_DIR" \
    || fail "Could not set staging directory permissions."

# ==========================================
# 10. Validate required release files
# ==========================================

[[ -f "$STAGING_DIR/compose.prod.yml" ]] \
    || fail "Compose file missing from target commit."

[[ -f "$STAGING_DIR/.dockerignore" ]] \
    || fail ".dockerignore missing from target commit."

[[ -f "$STAGING_DIR/docker/php/Dockerfile.prod" ]] \
    || fail "Production Dockerfile not found."

[[ -f "$STAGING_DIR/docker/nginx/default.prod.conf" ]] \
    || fail "Production Nginx configuration not found."

# ==========================================
# 11. Link production environment
# ==========================================

ln -s ../../.env "$STAGING_DIR/.env"

[[ -L "$STAGING_DIR/.env" ]] \
    || fail "Environment symlink creation failed."

# ==========================================
# 12. Validate candidate Compose configuration
# ==========================================

log "Validating candidate Compose configuration."

run_compose \
    "$STAGING_DIR" \
    "$TARGET_SHA" \
    config --quiet

# ==========================================
# 13. Verify candidate image tags
# ==========================================

IMAGE_OUTPUT="$(
    run_compose \
        "$STAGING_DIR" \
        "$TARGET_SHA" \
        config --images
)"

EXPECTED_APP_IMAGE="daily-outfit-app:${TARGET_SHA}"
EXPECTED_NGINX_IMAGE="daily-outfit-nginx:${TARGET_SHA}"

APP_COUNT="$(
    printf '%s\n' "$IMAGE_OUTPUT" \
        | grep -Fxc "$EXPECTED_APP_IMAGE" || true
)"

NGINX_COUNT="$(
    printf '%s\n' "$IMAGE_OUTPUT" \
        | grep -Fxc "$EXPECTED_NGINX_IMAGE" || true
)"

[[ "$APP_COUNT" -eq 2 ]] \
    || fail "Unexpected application image configuration."

[[ "$NGINX_COUNT" -eq 1 ]] \
    || fail "Unexpected Nginx image configuration."

# ==========================================
# 14. Verify existing Docker volumes
# ==========================================

log "Checking existing Docker volumes."

for volume in "${EXPECTED_VOLUMES[@]}"; do
    if ! docker volume inspect "$volume" >/dev/null 2>&1; then
        fail "Required existing volume is missing: ${volume}"
    fi
done

# ==========================================
# 15. Verify candidate Compose volume mapping
# ==========================================

validate_volume_mapping \
    "$STAGING_DIR" \
    "$TARGET_SHA" \
    || fail "Candidate volume mapping is invalid."

log "Candidate configuration verified."

# ==========================================
# 16. Finalize release directory
# ==========================================

log "Finalizing release."

if [[ -e "$RELEASE_DIR" || -L "$RELEASE_DIR" ]]; then
    fail "Release directory appeared during deployment."
fi

if ! mv -Tn -- "$STAGING_DIR" "$RELEASE_DIR"; then
    fail "Could not finalize release directory."
fi

if [[ -e "$STAGING_DIR" || -L "$STAGING_DIR" ]]; then
    fail "Release finalization was skipped or incomplete."
fi

[[ -d "$RELEASE_DIR" && ! -L "$RELEASE_DIR" ]] \
    || fail "Final release directory is invalid."

RELEASE_CREATED=1

STAGING_DIR=""

log "Release prepared successfully."

# ==========================================
# 17. Build application image
# ==========================================

log "Building application image."

run_compose \
    "$RELEASE_DIR" \
    "$TARGET_SHA" \
    build app

log "Application image build completed."

# ==========================================
# 18. Build Nginx image
# ==========================================

log "Building Nginx image."

run_compose \
    "$RELEASE_DIR" \
    "$TARGET_SHA" \
    build nginx

log "Nginx image build completed."

# ==========================================
# 19. Verify newly built images
# ==========================================

verify_images "$TARGET_SHA" \
    || fail "Newly built images are unavailable."

log "New images verified."

# ==========================================
# 20. Update production containers
# ==========================================

log "Starting production container update."

# From this point, failures require rollback.
ROLLBACK_REQUIRED=1

run_compose \
    "$RELEASE_DIR" \
    "$TARGET_SHA" \
    up -d --no-build app reverb nginx

log "Compose update command completed."

# ==========================================
# 21. Health check
# ==========================================

if ! wait_for_healthy "$RELEASE_DIR" "$TARGET_SHA"; then
    fail "New deployment failed health checks."
fi

# ==========================================
# 22. Update current release pointer
# ==========================================

log "Updating current release pointer."

CURRENT_LINK_TMP="${PROJECT_ROOT}/.current-${TARGET_SHA}.$$"

if [[ -e "$CURRENT_LINK_TMP" || -L "$CURRENT_LINK_TMP" ]]; then
    fail "Temporary current pointer already exists."
fi

ln -s "$RELEASE_DIR" "$CURRENT_LINK_TMP"

mv -Tf -- "$CURRENT_LINK_TMP" "$CURRENT_LINK"

CURRENT_LINK_TMP=""

# Deployment is now considered successful.
ROLLBACK_REQUIRED=0

# ==========================================
# 23. Deployment completed
# ==========================================

log "Production deployment completed successfully."
log "Commit: ${TARGET_SHA}"
log "Release directory: ${RELEASE_DIR}"
log "Current release: ${CURRENT_LINK}"
