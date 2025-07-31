import { ref } from 'vue';
import { getEnumStore } from '@/stores/enum';

export function useOutfitItems() {
    const outfitItems = ref([]);
    const selectData = getEnumStore();

    // 投稿時に選択したアイテムIDから情報取得
    const fetchItemData = async (itemId) => {
        try {
            const response = await axios.get(`/api/items/${itemId}`);
            const itemData = response.data;

            return {
                data: itemData,
                category: selectData.getSubCategoryName(itemData.sub_category),
                color: selectData.getColor(itemData.color),
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
                    .map(async (itemType) => {
                        const itemData = await fetchItemData(itemType.id);
                        return { label: itemType.label, ...itemData };
                    })
            );

            outfitItems.value = items;
        } catch (error) {
            console.error('コーディネートアイテムの取得に失敗しました:', error);
        }
    };

    return {
        selectData,
        outfitItems,
        fetchItems,
    };
}
