import { useEnumStore } from '../../stores/enum';

export function useCategoryData() {
    const enumStore = useEnumStore();

    // メインカテゴリーをAPIから取得
    const loadEnums = async () => {
        await enumStore.fetchEnums();
    };

    // 名前変換
    const getMainCategoryName = (id) => {
        const found = enumStore.mainCategories.find(
            (cat) => cat.id === Number(id),
        );
        return found ? found.name : '不明なカテゴリ';
    };

    const getSubCategoryName = (mainId, subId) => {
        const subs = enumStore.subCategories[mainId] ?? [];
        const found = subs.find((sub) => sub.id === Number(subId));
        return found ? found.name : '不明なサブカテゴリ';
    };

    return {
        loadEnums,
        getMainCategoryName,
        getSubCategoryName,
    };
}
