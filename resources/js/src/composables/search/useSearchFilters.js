import { ref, computed, watch } from 'vue';
import { useInitEnums } from '../useInitEnums';

export function useSearchFilters(sourceFilters, sourceSort) {
    const filters = ref({ ...sourceFilters.value });

    const sortOrder = ref(sourceSort.value);

    const { genders, mainCategories, subCategories, colors, seasons } =
        useInitEnums();

    watch(
        sourceFilters,
        (newVal) => {
            filters.value = { ...newVal };
        },
        { immediate: true },
    );

    watch(
        sourceSort,
        (val) => {
            sortOrder.value = val;
        },
        { immediate: true },
    );

    const filteredSubCategories = computed(() => {
        if (!filters.value.mainCategory) return [];
        return subCategories.value[filters.value.mainCategory] || [];
    });

    const clearFilters = () => {
        filters.value = {
            gender: 0,
            mainCategory: '',
            subCategory: '',
            color: null,
            season: '',
        };
    };

    return {
        filters,
        sortOrder,
        genders,
        mainCategories,
        subCategories,
        colors,
        seasons,
        filteredSubCategories,
        clearFilters,
    };
}
