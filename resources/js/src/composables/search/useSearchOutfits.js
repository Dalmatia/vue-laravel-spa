import { ref, onMounted, watch, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSearchFetch } from './useSearchFetch';
import { useSearchQuerySync } from './useSearchQuerySync';

export function useSearchOutfits() {
    let isFirstLoad = true;
    const scrollY = ref(0);
    const route = useRoute();
    const fetchState = useSearchFetch();
    const queryState = useSearchQuerySync();

    const saveScroll = () => {
        scrollY.value = window.scrollY;
    };

    const restoreScroll = () => {
        window.scrollTo(0, scrollY.value);
    };

    const getParams = () => ({
        filters: queryState.filters.value,
        sortOrder: queryState.sortOrder.value,
    });

    const loadMore = () => {
        fetchState.fetchOutfits({
            ...getParams(),
            isLoadMore: true,
        });
    };

    watch(
        () => route.fullPath,
        async () => {
            fetchState.reset();

            await fetchState.fetchInitialOutfits(getParams());

            if (isFirstLoad) {
                restoreScroll();
                isFirstLoad = false;
            } else {
                window.scrollTo(0, 0); // フィルタ変更時はトップへ
            }
        },
        { immediate: true },
    );

    onMounted(async () => {
        window.addEventListener('scroll', saveScroll);
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', saveScroll);
    });

    return {
        ...fetchState,
        ...queryState,
        loadMore,
    };
}
