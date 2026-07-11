import { ref } from 'vue';
import { useEnumStore } from '@/stores/enum';
import { OUTFIT_ROLE_META } from '@/src/constants/outfitRoles';

export function useOutfitItemDisplay() {
    const outfitItems = ref([]);
    const enumStore = useEnumStore();

    // コーディネートに使用したアイテム情報取得
    const buildOutfitItemsDisplay = async (outfit) => {
        try {
            await enumStore.fetchEnums();

            if (!outfit.items) {
                outfitItems.value = [];
                return;
            }

            outfitItems.value = outfit.items
                .map((item) => ({
                    role: item.pivot?.role,
                    label: enumStore.getMainCategoryName(item.main_category),
                    data: item,
                    category: enumStore.getSubCategoryName(
                        item.main_category,
                        item.sub_category,
                    ),
                    color: enumStore.getColor(item.color),
                }))
                .sort((a, b) => {
                    const orderA =
                        OUTFIT_ROLE_META[a.role]?.displayOrder ?? 999;

                    const orderB =
                        OUTFIT_ROLE_META[b.role]?.displayOrder ?? 999;

                    return orderA - orderB;
                });
        } catch (error) {
            console.error('コーディネートアイテムの取得に失敗しました:', error);
        }
    };

    return {
        enumStore,
        outfitItems,
        buildOutfitItemsDisplay,
    };
}
