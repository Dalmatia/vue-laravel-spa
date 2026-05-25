import { onMounted, onUnmounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useSearchFetch } from './useSearchFetch';
import { useSearchQuerySync } from './useSearchQuerySync';
import { createQueryKey } from './createQueryKey';
import { useInfinitePage } from '../common/useInfinitePage';

export function useSearchOutfits() {
    const route = useRoute();
    const fetchState = useSearchFetch();
    const queryState = useSearchQuerySync();
    const loadMoreTrigger = ref(null);

    const getParams = () => ({
        filters: queryState.filters.value,
        sortOrder: queryState.sortOrder.value,
    });

    const refreshSearchOutfits = async () => {
        fetchState.reset();

        await fetchState.fetchInitialOutfits(getParams());
        saveCache();
    };

    const { saveCache } = useInfinitePage({
        key: () => createQueryKey(route.query),
        watchSource: () => route.fullPath,
        fetchInitial: () => fetchState.fetchInitialOutfits(getParams()),
        fetchMore: () => fetchState.fetchMoreOutfits(getParams()),
        reset: fetchState.reset,

        items: fetchState.outfits,
        page: fetchState.page,
        hasMore: fetchState.hasMore,
        isLoading: fetchState.isLoading,
        isFetchingMore: fetchState.isFetchingMore,
        loadMoreTrigger,
    });

    onMounted(() => {
        window.addEventListener('outfit-created', refreshSearchOutfits);
        window.addEventListener('outfit-updated', refreshSearchOutfits);
        window.addEventListener('outfit-deleted', refreshSearchOutfits);
    });

    onUnmounted(() => {
        window.removeEventListener('outfit-created', refreshSearchOutfits);
        window.removeEventListener('outfit-updated', refreshSearchOutfits);
        window.removeEventListener('outfit-deleted', refreshSearchOutfits);
    });

    return {
        ...fetchState,
        ...queryState,
        loadMoreTrigger,
    };
}
