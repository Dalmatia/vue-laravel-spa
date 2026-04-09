import { onMounted } from 'vue';
import { useSearchFetch } from './useSearchFetch';
import { useSearchFilters } from './useSearchFilters';
import { useSearchQuerySync } from './useSearchQuerySync';

export function useSearchOutfits() {
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

        fetchState.reset();

        await fetchState.fetchInitialOutfits({
            filters: filtersState.filters.value,
            sortOrder: filtersState.sortOrder.value,
        });
    };

    onMounted(async () => {
        await fetchState.fetchInitialOutfits({
            filters: queryState.filters.value,
            sortOrder: queryState.sortOrder.value,
        });
    });

    return {
        ...filtersState,
        ...fetchState,
        applyFilters,
    };
}
