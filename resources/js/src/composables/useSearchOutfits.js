import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useFollowStore } from '@/stores/follow';
export function useSearchOutfits() {
    const outfits = ref([]);
    const isLoading = ref(true);
    const filters = ref({
        mainCategory: '',
        subCategory: '',
        color: null,
        season: '',
    });
    const sortOrder = ref('popular');
    // 検索する項目毎のデータの取得
    const mainCategories = ref([]);
    const subCategories = ref([]);
    const colors = ref([]);
    const seasons = ref([]);
    const followStore = useFollowStore();

    // 投稿したコーディネートの表示
    const fetchOutfits = async () => {
        isLoading.value = true;
        try {
            const response = await axios.get('/api/outfits', {
                params: { ...filters.value, sort: sortOrder.value },
            });
            outfits.value = response.data.outfits;
            const follows = outfits.value.map((o) => o.user.id);
            await followStore.fetchFollowStatus(follows);
        } catch (err) {
            console.error('コーディネート一覧取得失敗', err);
        } finally {
            isLoading.value = false;
        }
    };

    const getEnums = async () => {
        try {
            const response = await axios.get('/api/enums');
            mainCategories.value = response.data.mainCategories;
            subCategories.value = response.data.subCategories;
            colors.value = response.data.colors;
            seasons.value = response.data.seasons;
        } catch (err) {
            console.error('Enum取得失敗', err);
        }
    };
    const filterByCategory = () => {
        fetchOutfits();
    };

    // 指定した条件をクリアする
    const clearFilters = () => {
        filters.value = {
            mainCategory: '',
            subCategory: '',
            color: null,
            season: '',
        };
        filterByCategory();
    };

    onMounted(async () => {
        try {
            await Promise.all([fetchOutfits(), getEnums()]);
        } catch (error) {
            console.error('データの取得に失敗しました。', error);
        }
    });

    return {
        outfits,
        isLoading,
        filters,
        sortOrder,
        mainCategories,
        subCategories,
        colors,
        seasons,
        fetchOutfits,
        filterByCategory,
        clearFilters,
    };
}
