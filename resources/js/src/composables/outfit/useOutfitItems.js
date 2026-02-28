import { ref } from 'vue';
import { useCategoryData } from '../useCategoryData';
import { useEnumStore } from '@/stores/enum';
import axios from 'axios';

export function useOutfitItems() {
    const outfitItems = ref([]);
    const categoryData = useCategoryData();
    const enumStore = useEnumStore();

    // 投稿時に選択したアイテムIDから情報取得
    const fetchItemData = async (itemId) => {
        try {
            const response = await axios.get(`/api/items/${itemId}`);
            const itemData = response.data;

            // サブカテゴリーを事前取得（キャッシュされていれば API 呼ばれない）
            await categoryData.fetchSubCategories(itemData.main_category);

            // enumStore がロード済みか確認、未ロードならロード
            if (!enumStore.loaded) {
                await enumStore.fetchEnums();
            }

            return {
                data: itemData,
                category: categoryData.getSubCategoryName(
                    itemData.main_category,
                    itemData.sub_category
                ),
                color: enumStore.getColor(itemData.color),
            };
        } catch (error) {
            console.error('アイテムデータの取得に失敗しました:', error);
            return null;
        }
    };

    // コーディネートに使用したアイテム情報取得
    const fetchItems = async (outfit) => {
        try {
            const itemTypes = [
                { label: 'トップス', id: outfit.tops },
                { label: 'アウター', id: outfit.outer },
                { label: 'ボトムス', id: outfit.bottoms },
                { label: 'シューズ', id: outfit.shoes },
            ];

            const items = await Promise.all(
                itemTypes
                    .filter((itemType) => itemType.id)
                    .map((itemType) =>
                        fetchItemData(itemType.id).then((itemData) => ({
                            label: itemType.label,
                            ...itemData,
                        }))
                    )
            );

            outfitItems.value = items;
        } catch (error) {
            console.error('コーディネートアイテムの取得に失敗しました:', error);
        }
    };

    return {
        enumStore,
        outfitItems,
        fetchItems,
    };
}
