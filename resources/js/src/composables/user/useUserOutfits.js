import { ref, watch, onMounted, onUnmounted } from 'vue';

export function useUserOutfits(userId) {
    const outfits = ref([]);

    const currentPage = ref(1);
    const hasMorePages = ref(true);
    const isLoading = ref(false);

    const loadMoreTrigger = ref(null);

    let observer = null;
    let prevUserId = null;

    // ===== コーディネート一覧取得 =====
    const fetchUserOutfits = async (page = currentPage.value) => {
        if (isLoading.value || !hasMorePages.value) return;

        isLoading.value = true;

        try {
            const response = await axios.get(
                `/api/users/${userId.value}?page=${page}`,
            );

            outfits.value.push(...response.data.outfits);

            currentPage.value = response.data.meta.current_page + 1;

            hasMorePages.value = response.data.meta.has_more_pages;
        } catch (error) {
            console.error('コーディネート一覧の取得に失敗しました:', error);
        } finally {
            isLoading.value = false;
        }
    };

    // ===== ページネーションリセット =====
    const resetPagination = () => {
        outfits.value = [];

        currentPage.value = 1;

        hasMorePages.value = true;

        isLoading.value = false;
    };

    // ===== リフレッシュ =====
    const refreshUserOutfits = async () => {
        resetPagination();

        await fetchUserOutfits();
    };

    // ===== 無限スクロール =====
    const setupIntersectionObserver = () => {
        observer = new IntersectionObserver(
            async (entries) => {
                const entry = entries[0];

                if (
                    entry.isIntersecting &&
                    hasMorePages.value &&
                    !isLoading.value
                ) {
                    await fetchUserOutfits();
                }
            },
            {
                threshold: 0.5,
            },
        );

        if (loadMoreTrigger.value) {
            observer.observe(loadMoreTrigger.value);
        }
    };

    // ===== route user切替 =====
    watch(
        () => userId.value,
        async (newId) => {
            if (newId && newId !== prevUserId) {
                prevUserId = newId;

                resetPagination();

                await fetchUserOutfits();
            }
        },
        {
            immediate: true,
        },
    );

    onMounted(() => {
        setupIntersectionObserver();

        window.addEventListener('outfit-created', refreshUserOutfits);

        window.addEventListener('outfit-updated', refreshUserOutfits);

        window.addEventListener('outfit-deleted', refreshUserOutfits);
    });

    onUnmounted(() => {
        observer?.disconnect();

        window.removeEventListener('outfit-created', refreshUserOutfits);

        window.removeEventListener('outfit-updated', refreshUserOutfits);

        window.removeEventListener('outfit-deleted', refreshUserOutfits);
    });

    return {
        outfits,

        currentPage,
        hasMorePages,
        isLoading,

        loadMoreTrigger,

        fetchUserOutfits,
        refreshUserOutfits,
    };
}
