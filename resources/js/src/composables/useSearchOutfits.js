import { ref, onMounted, computed, watch } from 'vue';

export function useSearchOutfits() {
    const outfits = ref([]);
    const isLoading = ref(true);
    const filters = ref({
        gender: 0,
        mainCategory: '',
        subCategory: '',
        color: null,
        season: '',
    });
    const sortOrder = ref('popular');
    const genders = ref([]);
    const mainCategories = ref([]);
    const subCategories = ref({});
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
                    gender:
                        filters.value.gender === 0
                            ? null
                            : filters.value.gender,
                    color: filters.value.color?.id || null,
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
            genders.value = response.data.genders;
            mainCategories.value = response.data.mainCategories;
            subCategories.value = response.data.subCategories;
            colors.value = response.data.colors;
            seasons.value = response.data.seasons;
        } catch (err) {
            console.error('Enum取得失敗', err);
        }
    };

    const filteredSubCategories = computed(() => {
        if (!filters.value.mainCategory) return [];
        return subCategories.value[filters.value.mainCategory] || [];
    });

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
            gender: 0,
            mainCategory: '',
            subCategory: '',
            color: null,
            season: '',
        };
        resetAndFetch();
    };

    onMounted(async () => {
        if (outfits.value.length === 0) {
            await Promise.all([fetchOutfits(), getEnums()]);
        } else {
            isLoading.value = false;
        }
    });

    watch(
        () => filters.value.mainCategory,
        () => {
            filters.value.subCategory = '';
        },
    );

    return {
        outfits,
        isLoading,
        filters,
        sortOrder,
        genders,
        mainCategories,
        filteredSubCategories,
        colors,
        seasons,
        hasMore,
        isFetchingMore,
        fetchOutfits,
        resetAndFetch,
        filterByCategory,
        clearFilters,
    };
}
