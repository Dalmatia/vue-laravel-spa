import { ref } from 'vue';
import { useOutfitApi } from '../outfit/useOutfitApi';

export function useSearchFetch() {
    const { getOutfits } = useOutfitApi();

    const outfits = ref([]);
    const isLoading = ref(true);
    const isFetchingMore = ref(false);
    const page = ref(1);
    const hasMore = ref(true);

    const fetchOutfits = async ({ filters, sortOrder, isLoadMore = false }) => {
        if (isLoadMore) {
            if (!hasMore.value) return;
            isFetchingMore.value = true;
        } else {
            isLoading.value = true;
            page.value = 1;
        }

        try {
            const { outfits: newOutfits, hasMore: newHasMore } =
                await getOutfits({
                    filters,
                    sort: sortOrder,
                    page: page.value,
                });

            if (isLoadMore) {
                outfits.value.push(...newOutfits);
            } else {
                outfits.value = newOutfits;
            }

            hasMore.value = newHasMore;
            page.value++;
        } finally {
            isLoading.value = false;
            isFetchingMore.value = false;
        }
    };

    const fetchInitialOutfits = async (params) => {
        // 初回ロード
        await fetchOutfits(params);

        // 1回だけ追加ロード
        const isScreenFilled = () => {
            return document.body.scrollHeight > window.innerHeight;
        };

        if (!isScreenFilled() && hasMore.value) {
            await fetchOutfits({
                ...params,
                isLoadMore: true,
            });
        }
    };

    const reset = () => {
        outfits.value = [];
        page.value = 1;
        hasMore.value = true;
        isLoading.value = false;
        isFetchingMore.value = false;
    };

    return {
        outfits,
        isLoading,
        isFetchingMore,
        hasMore,
        fetchOutfits,
        fetchInitialOutfits,
        reset,
    };
}
