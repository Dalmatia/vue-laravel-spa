import { ref } from 'vue';
import { useCategoryData } from '../useCategoryData';
import { useEnumStore } from '@/stores/enum';
import { OUTFIT_ROLE_META } from '@/src/constants/outfitRoles';

export function useOutfitItemDisplay() {
    const outfitItems = ref([]);
    const categoryData = useCategoryData();
    const enumStore = useEnumStore();

    // コーディネートに使用したアイテム情報取得
    const buildOutfitItemsDisplay = async (outfit) => {
        try {
            await enumStore.fetchEnums();
            await categoryData.fetchMainCategories();

            if (!outfit.items) {
                outfitItems.value = [];
                return;
            }

            // 必要なサブカテゴリを取得
            for (const item of outfit.items) {
                await categoryData.fetchSubCategories(item.main_category);
            }

            outfitItems.value = outfit.items
                .map((item) => ({
                    role: item.pivot?.role,
                    label: categoryData.getMainCategoryName(item.main_category),
                    data: item,
                    category: categoryData.getSubCategoryName(
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
