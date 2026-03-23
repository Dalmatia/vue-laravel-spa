import { ref, onMounted } from 'vue';

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
    const mainCategories = ref([]);
    const subCategories = ref([]);
    const colors = ref([]);
    const seasons = ref([]);

    const page = ref(1);
    const hasMore = ref(true);
    const isFetchingMore = ref(false);

    const fetchOutfits = async (isLoadMore = false) => {
        if (isLoadMore) {
            if (!hasMore.value) return;
            isFetchingMore.value = true;
        } else {
            isLoading.value = true;
            page.value = 1;
        }

        try {
            const response = await axios.get('/api/outfits', {
                params: {
                    ...filters.value,
                    sort: sortOrder.value,
                    page: page.value,
                },
            });
            const newOutfits = response.data.outfits;

            if (isLoadMore) {
                outfits.value.push(...newOutfits);
            } else {
                outfits.value = newOutfits;
            }

            hasMore.value = response.data.meta.has_more;
            page.value++;
        } finally {
            isLoading.value = false;
            isFetchingMore.value = false;
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

    const resetAndFetch = async () => {
        outfits.value = [];
        page.value = 1;
        hasMore.value = true;

        window.scrollTo({ top: 0 });
        await fetchOutfits();
    };

    onMounted(async () => {
        if (outfits.value.length === 0) {
            await Promise.all([fetchOutfits(), getEnums()]);
        } else {
            isLoading.value = false;
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
        hasMore,
        isFetchingMore,
        fetchOutfits,
        resetAndFetch,
    };
}
