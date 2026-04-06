import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export function useSearchQuerySync({ filters, sortOrder, colors }) {
    const route = useRoute();
    const router = useRouter();
    const isInitialized = ref(false);

    const applyQueryToFilters = () => {
        const q = route.query;

        filters.value.gender = q.gender ? Number(q.gender) : 0;
        filters.value.mainCategory = q.mainCategory || '';
        filters.value.subCategory = q.subCategory || '';

        if (q.color) {
            const id = Number(q.color);
            filters.value.color = colors.value.find((c) => c.id === id) || null;
        } else {
            filters.value.color = null;
        }

        filters.value.season = q.season || '';
        sortOrder.value = q.sort || 'popular';
    };

    const updateQuery = (usePush = false) => {
        const query = {
            gender:
                filters.value.gender === 0 ? undefined : filters.value.gender,
            mainCategory: filters.value.mainCategory || undefined,
            subCategory: filters.value.subCategory || undefined,
            color: filters.value.color?.id || undefined,
            season: filters.value.season || undefined,
            sort: sortOrder.value !== 'popular' ? sortOrder.value : undefined,
        };

        router[usePush ? 'push' : 'replace']({ query });
    };

    watch(
        () => route.query,
        () => {
            if (!isInitialized.value) return;
            applyQueryToFilters();
        },
    );

    return {
        applyQueryToFilters,
        updateQuery,
        isInitialized,
    };
}
