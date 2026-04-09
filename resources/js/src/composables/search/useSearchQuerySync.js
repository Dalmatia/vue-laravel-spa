import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInitEnums } from '../useInitEnums';

export function useSearchQuerySync() {
    const route = useRoute();
    const router = useRouter();
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

        router[usePush ? 'push' : 'replace']({ query });
    };

    return {
        filters,
        sortOrder,
        updateQuery,
    };
}
