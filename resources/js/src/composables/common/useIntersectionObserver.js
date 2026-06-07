import { onMounted, onUnmounted, watch, unref } from 'vue';

export function useIntersectionObserver({
    target,
    root = null,
    onIntersect,
    enabled = true,
    threshold = 0.5,
}) {
    let observer = null;

    const cleanup = () => {
        observer?.disconnect();
        observer = null;
    };

    const setup = () => {
        if (!target.value || !unref(enabled)) return;

        observer = new IntersectionObserver(
            async (entries) => {
                const entry = entries[0];

                if (entry.isIntersecting) {
                    await onIntersect();
                }
            },
            {
                root: unref(root),
                threshold,
            },
        );

        observer.observe(target.value);
    };

    watch([() => target.value, () => unref(root), () => unref(enabled)], () => {
        cleanup();
        setup();
    });

    onMounted(() => {
        setup();
    });

    onUnmounted(() => {
        cleanup();
    });

    return {
        cleanup,
    };
}
