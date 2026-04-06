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

    const ensureFilled = async (params) => {
        const isScreenFilled = () => {
            return document.body.scrollHeight > window.innerHeight;
        };

        const MAX_TRIES = 10;

        const loadUntilFilled = async (count = 0) => {
            if (count >= MAX_TRIES) return;
            if (isScreenFilled() || !hasMore.value) return;

            const prevLength = outfits.value.length;

            await fetchOutfits({
                ...params,
                isLoadMore: true,
            });

            if (outfits.value.length === prevLength) return;

            return loadUntilFilled(count + 1);
        };

        await loadUntilFilled();
    };

    const fetchInitialOutfits = async (params) => {
        // 初回ロード
        await fetchOutfits(params);
        // 画面が埋まるまで追加ロード
        await ensureFilled(params);
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
