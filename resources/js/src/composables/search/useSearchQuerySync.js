import { computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInitEnums } from '../useInitEnums';
import { useSearchQueryStore } from '../../../stores/searchQueryStore';

export function useSearchQuerySync() {
    const route = useRoute();
    const router = useRouter();
    const searchQueryStore = useSearchQueryStore();
    const { colors } = useInitEnums();

    const filters = computed(() => {
        const q = route.query;

        const colorId = q.color ? Number(q.color) : null;

        const selectedColor =
            colorId && colors.value.length
                ? colors.value.find((c) => c.id === colorId) || null
                : null;

        return {
            gender: q.gender ? Number(q.gender) : 0,
            mainCategory: q.mainCategory || '',
            subCategory: q.subCategory || '',
            color: selectedColor,
            season: q.season || '',
        };
    });

    const sortOrder = computed(() => {
        return route.query.sort || 'popular';
    });

    const updateQuery = (newFilters, newSort, usePush = true) => {
        const query = {
            gender: newFilters.gender || undefined,
            mainCategory: newFilters.mainCategory || undefined,
            subCategory: newFilters.subCategory || undefined,
            color: newFilters.color?.id || undefined,
            season: newFilters.season || undefined,
            sort: newSort !== 'popular' ? newSort : undefined,
        };

        if (JSON.stringify(query) === JSON.stringify(route.query)) return;
        router[usePush ? 'push' : 'replace']({ query });
    };

    watch(
        () => route.query,
        (newQuery) => {
            searchQueryStore.setQuery(newQuery);
        },
        { immediate: true, deep: true },
    );

    return {
        filters,
        sortOrder,
        updateQuery,
    };
}
