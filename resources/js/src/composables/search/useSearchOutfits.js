import { onMounted } from 'vue';
import { useSearchFetch } from './useSearchFetch';
import { useSearchFilters } from './useSearchFilters';
import { useSearchQuerySync } from './useSearchQuerySync';

export function useSearchOutfits() {
    const filtersState = useSearchFilters();
    const fetchState = useSearchFetch();

    const { applyQueryToFilters, updateQuery, isInitialized } =
        useSearchQuerySync({
            filters: filtersState.filters,
            sortOrder: filtersState.sortOrder,
            colors: filtersState.colors,
        });

    const applyFilters = async () => {
        updateQuery(true);
        fetchState.reset();
        await fetchState.fetchInitialOutfits({
            filters: filtersState.filters.value,
            sortOrder: filtersState.sortOrder.value,
        });
    };

    onMounted(async () => {
        applyQueryToFilters();

        await fetchState.fetchInitialOutfits({
            filters: filtersState.filters.value,
            sortOrder: filtersState.sortOrder.value,
        });

        isInitialized.value = true;
    });

    return {
        ...filtersState,
        ...fetchState,
        applyFilters,
    };
}
