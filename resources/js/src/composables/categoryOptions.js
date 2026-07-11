import { useEnumStore } from '../../stores/enum';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

export function useCategoryOptions(mainCategoryRef) {
    const enumStore = useEnumStore();
    const { mainCategories, subCategories } = storeToRefs(enumStore);

    const filteredSubCategories = computed(() => {
        if (!mainCategoryRef()) return [];

        return subCategories.value[mainCategoryRef()] ?? [];
    });

    return {
        mainCategories,
        subCategories: filteredSubCategories,
    };
}
