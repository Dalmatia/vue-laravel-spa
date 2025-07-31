import { ref } from 'vue';

export function useOutfitDetail(initialOutfit) {
    const outfit = ref(initialOutfit);
    const user = ref(initialOutfit?.user || {});

    const fetchOutfit = async () => {
        try {
            const response = await axios.get(`/api/outfit/${outfit.value.id}`);
            outfit.value = response.data.outfit;
            user.value = response.data.user;
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
