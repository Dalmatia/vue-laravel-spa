import { ref } from 'vue';
import { useOutfitApi } from './useOutfitApi';

export function useOutfitDetail(initialOutfit) {
    const { getOutfit } = useOutfitApi();
    const outfit = ref(initialOutfit);
    const user = ref(initialOutfit?.user || {});

    const fetchOutfit = async () => {
        try {
            const response = await getOutfit(outfit.value.id);
            outfit.value = response.outfit;
            user.value = response.user;
        } catch (error) {
            console.error('コーディネートの取得に失敗しました:', error);
        }
    };

    return {
        outfit,
        user,
        fetchOutfit,
    };
}
