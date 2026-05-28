import { watch, nextTick, onMounted, onUnmounted } from 'vue';

import { usePageCacheStore } from '../../../stores/pageCacheStore';
import { useScrollContainer } from '../dom/useScrollContainer';
import { useIntersectionObserver } from './useIntersectionObserver';
import { useDebounce } from '../utils/useDebounce';

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
    let requestId = 0;

    const cacheStore = usePageCacheStore();

    const { scrollTo, getScrollTop, addScrollListener, removeScrollListener } =
        useScrollContainer();

    const CACHE_TTL = 1000 * 60 * 30;

    // スクロール保存
    const saveScroll = useDebounce(() => {
        if (!cacheStore.has(key())) return;

        cacheStore.updateScroll(key(), getScrollTop());
    }, 100);

    // キャッシュ保存
    const saveCache = () => {
        cacheStore.set(key(), {
            items: [...items.value],
            page: page.value,
            hasMore: hasMore.value,
            scrollY: getScrollTop(),
            savedAt: Date.now(),
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

            const currentRequestId = requestId;

            await fetchMore();
            if (currentRequestId !== requestId) return;
            saveCache();
        },
    });

    // ===== route / user watch =====
    watch(
        watchSource,
        async () => {
            const currentRequestId = ++requestId;
            let cached = cacheStore.get(key());

            if (cached) {
                const isExpired = Date.now() - cached.savedAt > CACHE_TTL;

                if (isExpired) {
                    cacheStore.remove(key());
                    cached = null;
                }
            }

            // cache restore
            if (cached) {
                restoreCache(cached);

                await nextTick();

                await new Promise((resolve) => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(resolve);
                    });
                });

                if (currentRequestId !== requestId) return;
                scrollTo(cached.scrollY ?? 0);

                return;
            }

            // fresh fetch
            reset();
            await fetchInitial();
            if (currentRequestId !== requestId) return;
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
        saveScroll.cancel?.();
    });

    return { saveCache };
}
