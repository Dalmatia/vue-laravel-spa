import { ref, computed } from 'vue';
import { useInitEnums } from '../useInitEnums';

export function useSearchFilters() {
    const filters = ref({
        gender: 0,
        mainCategory: '',
        subCategory: '',
        color: null,
        season: '',
    });

    const sortOrder = ref('popular');

    const { genders, mainCategories, subCategories, colors, seasons } =
        useInitEnums();

    // メインカテゴリに応じてサブカテゴリをフィルタリングする
    const filteredSubCategories = computed(() => {
        if (!filters.value.mainCategory) return [];
        return subCategories.value[filters.value.mainCategory] || [];
    });

    // 指定した条件をクリアする
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
