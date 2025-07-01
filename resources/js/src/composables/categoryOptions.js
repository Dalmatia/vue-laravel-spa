import { ref, watch } from 'vue';

export function useCategoryOptions(mainCategoryRef) {
    const mainCategories = ref([]);
    const subCategories = ref([]);

    const fetchAllCategories = async () => {
        try {
            const response = await axios.get('/api/enums');
            mainCategories.value = response.data.mainCategories || [];
            subCategories.value = response.data.subCategories || [];
        } catch (error) {
            console.error('Enum データの取得に失敗しました', error);
        }
    };

    const fetchSubCategories = async () => {
        try {
            const response = await axios.get(`/api/enums`);
            subCategories.value = response.data.subCategories || [];
        } catch (error) {
            console.error('サブカテゴリーの取得に失敗しました', error);
        }
    };

    // メインカテゴリが変更されたらサブカテゴリも更新
    watch(mainCategoryRef, () => {
        fetchSubCategories();
    });

    return {
        mainCategories,
        subCategories,
        fetchAllCategories,
    };
}
