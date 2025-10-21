import { ref } from 'vue';

const mainCategories = ref([]);
const subCategoriesMap = ref({});
const isLoaded = ref(false);

export function useCategoryData() {
    // メインカテゴリーをAPIから取得
    const fetchMainCategories = async () => {
        if (mainCategories.value.length > 0) return;
        const { data } = await axios.get('/api/main_categories');
        mainCategories.value = data.mainCategories;
    };

    // サブカテゴリーをAPIから取得
    const fetchSubCategories = async (mainCategoryId) => {
        if (subCategoriesMap.value[mainCategoryId]) return;
        const { data } = await axios.get(
            `/api/main_categories/${mainCategoryId}/sub_categories`
        );
        subCategoriesMap.value[mainCategoryId] = data.subCategories;
    };

    // 名前変換
    const getMainCategoryName = (id) => {
        const found = mainCategories.value.find((cat) => cat.id === Number(id));
        return found ? found.name : '不明なカテゴリ';
    };

    const getSubCategoryName = (mainId, subId) => {
        const subs = subCategoriesMap.value[mainId] || [];
        const found = subs.find((sub) => sub.id === Number(subId));
        return found ? found.name : '不明なサブカテゴリ';
    };

    // 初期化（App起動時など）
    const initEnums = async () => {
        if (isLoaded.value) return;
        await fetchMainCategories();
        isLoaded.value = true;
    };

    return {
        mainCategories,
        subCategoriesMap,
        fetchMainCategories,
        fetchSubCategories,
        getMainCategoryName,
        getSubCategoryName,
        initEnums,
    };
}
