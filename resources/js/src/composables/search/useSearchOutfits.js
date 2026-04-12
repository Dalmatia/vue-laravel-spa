import { ref, onMounted, watch, onUnmounted } from 'vue';
import { useSearchFetch } from './useSearchFetch';
import { useSearchFilters } from './useSearchFilters';
import { useSearchQuerySync } from './useSearchQuerySync';

export function useSearchOutfits() {
    let isFirstLoad = true;
    const scrollY = ref(0);
    const fetchState = useSearchFetch();
    const queryState = useSearchQuerySync();

    const filtersState = useSearchFilters(
        queryState.filters,
        queryState.sortOrder,
    );

    const applyFilters = async () => {
        queryState.updateQuery(
            filtersState.filters.value,
            filtersState.sortOrder.value,
            true,
        );
    };

    const saveScroll = () => {
        scrollY.value = window.scrollY;
    };

    const restoreScroll = () => {
        window.scrollTo(0, scrollY.value);
    };

    watch(
        () => [queryState.filters.value, queryState.sortOrder.value],
        async () => {
            fetchState.reset();

            await fetchState.fetchInitialOutfits({
                filters: queryState.filters.value,
                sortOrder: queryState.sortOrder.value,
            });

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
        ...filtersState,
        ...fetchState,
        applyFilters,
    };
}
