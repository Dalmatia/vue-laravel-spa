import { watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSearchFetch } from './useSearchFetch';
import { useSearchQuerySync } from './useSearchQuerySync';
import { usePageCacheStore } from '../../../stores/pageCacheStore';
import { createQueryKey } from './createQueryKey';
import { useScrollContainer } from '../dom/useScrollContainer';

export function useSearchOutfits() {
    const route = useRoute();
    const fetchState = useSearchFetch();
    const queryState = useSearchQuerySync();
    const cacheStore = usePageCacheStore();
    const { scrollTo, getScrollTop, addScrollListener, removeScrollListener } =
        useScrollContainer();

    const saveScroll = () => {
        const key = createQueryKey(route.query);
        if (!cacheStore.has(key)) return;

        cacheStore.updateScroll(key, getScrollTop());
    };

    const getParams = () => ({
        filters: queryState.filters.value,
        sortOrder: queryState.sortOrder.value,
    });

    const loadMore = async () => {
        await fetchState.fetchMoreOutfits(getParams());
        await nextTick();

        const key = createQueryKey(route.query);

        // キャッシュ更新
        cacheStore.set(key, {
            outfits: [...fetchState.outfits.value],
            page: fetchState.page.value,
            hasMore: fetchState.hasMore.value,
            scrollY: getScrollTop(),
        });
    };

    const restoreFromCache = (cached) => {
        fetchState.outfits.value = [...cached.outfits];
        fetchState.page.value = cached.page;
        fetchState.hasMore.value = cached.hasMore;

        fetchState.isLoading.value = false;
        fetchState.isFetchingMore.value = false;
    };

    watch(
        () => route.fullPath,
        async () => {
            const key = createQueryKey(route.query);

            // キャッシュ復元
            if (cacheStore.has(key)) {
                const cached = cacheStore.get(key);
                restoreFromCache(cached);

                await nextTick();
                requestAnimationFrame(() => {
                    scrollTo(cached.scrollY ?? 0);
                });

                return;
            }

            // 新規fetch
            fetchState.reset();

            await fetchState.fetchInitialOutfits(getParams());

            // キャッシュ保存
            cacheStore.set(key, {
                outfits: [...fetchState.outfits.value],
                page: fetchState.page.value,
                hasMore: fetchState.hasMore.value,
                scrollY: 0,
            });

            // フィルタ変更時はトップへ
            scrollTo(0);
        },
        { immediate: true },
    );

    onMounted(async () => {
        addScrollListener(saveScroll);
    });

    onUnmounted(async () => {
        await nextTick();
        removeScrollListener(saveScroll);
    });

    return {
        ...fetchState,
        ...queryState,
        loadMore,
    };
}
