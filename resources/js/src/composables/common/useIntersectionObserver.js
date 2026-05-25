import { onMounted, onUnmounted, watch, unref } from 'vue';

export function useIntersectionObserver({
    target,
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
                threshold,
            },
        );

        observer.observe(target.value);
    };

    watch(
        () => target.value,
        () => {
            cleanup();
            setup();
        },
    );

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
