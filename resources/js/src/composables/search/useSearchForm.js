import { computed, ref, watch } from 'vue';

export function useSearchForm(searchState, subCategories) {
    const localFilters = ref({ ...searchState.filters.value });
    const localSortOrder = ref(searchState.sortOrder.value);

    // メインカテゴリーに応じたサブカテゴリーの絞り込み
    const filteredSubCategory = computed(() => {
        const main = localFilters.value.mainCategory;

        if (!main) {
            return Object.values(subCategories.value).flat();
        }

        return subCategories.value[main] || [];
    });

    const applyFilters = () => {
        searchState.updateQuery(localFilters.value, localSortOrder.value);
    };

    const clearFilters = () => {
        const empty = {
            gender: 0,
            mainCategory: '',
            subCategory: '',
            color: null,
            season: '',
        };

        localFilters.value = empty;
        searchState.updateQuery(empty, localSortOrder.value);
    };

    const selectColor = (color) => {
        if (localFilters.value.color?.id === color?.id) {
            localFilters.value.color = null;
        } else {
            localFilters.value.color = color;
        }
    };

    watch(
        searchState.filters,
        (f) => {
            localFilters.value = { ...f };
        },
        { immediate: true },
    );

    watch(
        searchState.sortOrder,
        (s) => {
            localSortOrder.value = s;
        },
        { immediate: true },
    );

    return {
        localFilters,
        localSortOrder,
        filteredSubCategory,
        applyFilters,
        clearFilters,
        selectColor,
    };
}
