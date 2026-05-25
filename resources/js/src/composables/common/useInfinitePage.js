import { watch, nextTick, onMounted, onUnmounted } from 'vue';

import { usePageCacheStore } from '../../../stores/pageCacheStore';
import { useScrollContainer } from '../dom/useScrollContainer';
import { useIntersectionObserver } from './useIntersectionObserver';

export function useInfinitePage({
    key,
    watchSource,

    fetchInitial,
    fetchMore,
    reset,

    items,
    page,
    hasMore,
    isLoading,
    isFetchingMore,

    loadMoreTrigger,
}) {
    const cacheStore = usePageCacheStore();

    const { scrollTo, getScrollTop, addScrollListener, removeScrollListener } =
        useScrollContainer();

    // スクロール保存
    const saveScroll = () => {
        if (!cacheStore.has(key())) return;

        cacheStore.updateScroll(key(), getScrollTop());
    };

    // キャッシュ保存
    const saveCache = () => {
        cacheStore.set(key(), {
            items: [...items.value],
            page: page.value,
            hasMore: hasMore.value,
            scrollY: getScrollTop(),
        });
    };

    // キャッシュ復元
    const restoreCache = (cached) => {
        items.value = [...cached.items];
        page.value = cached.page;
        hasMore.value = cached.hasMore;
        isLoading.value = false;

        if (isFetchingMore) {
            isFetchingMore.value = false;
        }
    };

    // 無限スクロール
    useIntersectionObserver({
        target: loadMoreTrigger,
        enabled: hasMore,

        onIntersect: async () => {
            if (isLoading.value) return;

            if (isFetchingMore?.value) return;

            await fetchMore();

            saveCache();
        },
    });

    // ===== route / user watch =====
    watch(
        watchSource,
        async () => {
            const cached = cacheStore.get(key());

            // cache restore
            if (cached) {
                restoreCache(cached);

                await nextTick();

                await new Promise((resolve) => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(resolve);
                    });
                });

                scrollTo(cached.scrollY ?? 0);

                return;
            }

            // fresh fetch
            reset();

            await fetchInitial();

            saveCache();

            scrollTo(0);
        },
        { immediate: true },
    );

    onMounted(() => {
        addScrollListener(saveScroll);
    });

    onUnmounted(() => {
        removeScrollListener(saveScroll);
    });

    return { saveCache };
}
