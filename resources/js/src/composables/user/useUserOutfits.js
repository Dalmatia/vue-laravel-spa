import { ref, onMounted, onUnmounted } from 'vue';
import { useIntersectionObserver } from '../common/useIntersectionObserver';
import { useUserFetch } from './useUserFetch';
import { usePageCacheStore } from '../../../stores/pageCacheStore';
import { useScrollContainer } from '../dom/useScrollContainer';
import { useInfinitePage } from '../common/useInfinitePage';

export function useUserOutfits(userId) {
    const loadMoreTrigger = ref(null);

    const fetchState = useUserFetch(userId);

    useInfinitePage({
        key: () => `user:${userId.value}`,
        watchSource: () => userId.value,

        fetchInitial: () => fetchState.fetchInitial(),
        fetchMore: () => fetchState.fetchUserOutfits(),
        reset: fetchState.reset,

        items: fetchState.outfits,
        page: fetchState.currentPage,
        hasMore: fetchState.hasMorePages,
        isLoading: fetchState.isLoading,
        loadMoreTrigger,
    });

    onMounted(() => {
        window.addEventListener(
            'outfit-created',
            fetchState.refreshUserOutfits,
        );

        window.addEventListener(
            'outfit-updated',
            fetchState.refreshUserOutfits,
        );

        window.addEventListener(
            'outfit-deleted',
            fetchState.refreshUserOutfits,
        );
    });

    onUnmounted(() => {
        window.removeEventListener(
            'outfit-created',
            fetchState.refreshUserOutfits,
        );

        window.removeEventListener(
            'outfit-updated',
            fetchState.refreshUserOutfits,
        );

        window.removeEventListener(
            'outfit-deleted',
            fetchState.refreshUserOutfits,
        );
    });

    return {
        ...fetchState,
        loadMoreTrigger,
    };
}
