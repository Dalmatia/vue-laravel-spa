import { ref, watch } from 'vue';

export function useCategoryOptions(mainCategoryRef) {
    const mainCategories = ref([]);
    const subCategories = ref([]);

    const fetchAllCategories = async () => {
        try {
            const response = await axios.get('/api/main_categories');
            mainCategories.value = response.data.mainCategories || [];
        } catch (error) {
            console.error('Enum データの取得に失敗しました', error);
        }
    };

    const fetchSubCategories = async (mainCategoryId) => {
        if (!mainCategoryId) {
            subCategories.value = [];
            return;
        }

        try {
            const response = await axios.get(
                `/api/main_categories/${mainCategoryId}/sub_categories`
            );
            subCategories.value = response.data.subCategories || [];
        } catch (error) {
            console.error('サブカテゴリーの取得に失敗しました', error);
        }
    };

    // メインカテゴリが変更されたらサブカテゴリも更新
    watch(
        mainCategoryRef,
        (newVal) => {
            fetchSubCategories(newVal);
        },
        { immediate: true }
    );

    return {
        mainCategories,
        subCategories,
        fetchAllCategories,
    };
}
