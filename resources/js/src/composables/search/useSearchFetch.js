import { ref } from 'vue';
import { useOutfitApi } from '../outfit/useOutfitApi';

export function useSearchFetch() {
    const { getOutfits } = useOutfitApi();

    const outfits = ref([]);
    const isLoading = ref(true);
    const isFetchingMore = ref(false);
    const page = ref(1);
    const hasMore = ref(true);

    // リクエスト管理
    let currentRequestId = 0;

    const fetchOutfits = async ({
        filters,
        sortOrder,
        isLoadMore = false,
        requestId,
    }) => {
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

            if (requestId !== currentRequestId) return;

            if (isLoadMore) {
                outfits.value.push(...newOutfits);
            } else {
                outfits.value = newOutfits;
            }

            hasMore.value = newHasMore;
            page.value++;
        } finally {
            if (requestId === currentRequestId) {
                isLoading.value = false;
                isFetchingMore.value = false;
            }
        }
    };

    // 初回ロードと、フィルタ変更後のロード
    const fetchInitialOutfits = async (params) => {
        const requestId = ++currentRequestId;

        await fetchOutfits({ ...params, requestId });

        // 途中で別リクエストが来たら中断
        if (requestId !== currentRequestId) return;

        // 画面埋まらなければ追加ロード
        const isScreenFilled = () =>
            document.body.scrollHeight > window.innerHeight;

        while (!isScreenFilled() && hasMore.value) {
            await fetchOutfits({
                ...params,
                isLoadMore: true,
                requestId,
            });
            if (requestId !== currentRequestId) return;
        }
    };

    const fetchMoreOutfits = async (params) => {
        const requestId = currentRequestId; // 今のを使う

        await fetchOutfits({
            ...params,
            isLoadMore: true,
            requestId,
        });
    };

    const reset = () => {
        outfits.value = [];
        page.value = 1;
        hasMore.value = true;
        isLoading.value = false;
        isFetchingMore.value = false;

        currentRequestId++; // これで全てのリクエストを無効化
    };

    return {
        outfits,
        isLoading,
        isFetchingMore,
        hasMore,
        page,
        fetchOutfits,
        fetchInitialOutfits,
        fetchMoreOutfits,
        reset,
    };
}
