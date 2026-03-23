import { ref, onMounted } from 'vue';

let cachedState = null;

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

    const page = ref(1);
    const hasMore = ref(true);
    const isFetchingMore = ref(false);

    // 投稿したコーディネートの表示
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
        } catch (err) {
            console.error('コーディネート一覧取得失敗', err);
        } finally {
            isLoading.value = false;
            isFetchingMore.value = false;
            saveCache();
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

    const saveCache = () => {
        cachedState = {
            outfits: [...outfits.value],
            page: page.value,
            hasMore: hasMore.value,
            filters: { ...filters.value },
            sortOrder: sortOrder.value,
            scrollY: window.scrollY,
        };
    };

    const clearCache = () => {
        cachedState = null;
    };

    const isSameCondition = () => {
        if (!cachedState) return false;

        return (
            JSON.stringify(cachedState.filters) ===
                JSON.stringify(filters.value) &&
            cachedState.sortOrder === sortOrder.value
        );
    };

    const restoreCache = () => {
        if (!cachedState || !isSameCondition()) return false;

        outfits.value = [...cachedState.outfits];
        page.value = cachedState.page;
        hasMore.value = cachedState.hasMore;
        filters.value = { ...cachedState.filters };
        sortOrder.value = cachedState.sortOrder;

        requestAnimationFrame(() => {
            window.scrollTo(0, cachedState.scrollY);
        });

        return true;
    };

    const resetAndFetch = async () => {
        outfits.value = [];
        page.value = 1;
        hasMore.value = true;

        window.scrollTo({ top: 0 });
        await fetchOutfits();
    };

    const filterByCategory = () => {
        resetAndFetch();
    };

    // 指定した条件をクリアする
    const clearFilters = () => {
        filters.value = {
            mainCategory: '',
            subCategory: '',
            color: null,
            season: '',
        };
        resetAndFetch();
    };

    onMounted(async () => {
        const restored = restoreCache();
        if (restored) {
            isLoading.value = false;
            getEnums();
        } else {
            await Promise.all([fetchOutfits(), getEnums()]);
        }
    });

    return {
        clearCache,
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
        clearCache,
        resetAndFetch,
        filterByCategory,
        clearFilters,
    };
}
