import { ref } from 'vue';
import { useOutfitApi } from '../outfit/useOutfitApi';
import { usePaginatedFetch } from '../common/usePaginatedFetch';

export function useSearchFetch() {
    const { getOutfits } = useOutfitApi();

    const fetchSearchApi = async ({ page, filters, sortOrder }) => {
        const { outfits, hasMore } = await getOutfits({
            filters,
            sort: sortOrder,
            page,
        });

        return {
            items: outfits,
            hasMore,
        };
    };

    const {
        items: outfits,
        page,
        hasMore,
        isLoading,
        isFetchingMore,
        fetchInitial,
        fetchMore,
        reset,
    } = usePaginatedFetch(fetchSearchApi);

    // 初回ロードと、フィルタ変更後のロード
    const fetchInitialOutfits = async (params) => {
        await fetchInitial(params);

        // 画面埋まらなければ追加ロード
        const isScreenFilled = () =>
            document.body.scrollHeight > window.innerHeight;

        while (!isScreenFilled() && hasMore.value) {
            await fetchMore(params);
        }
    };

    const fetchMoreOutfits = async (params) => {
        await fetchMore(params);
    };

    return {
        outfits,
        isLoading,
        isFetchingMore,
        hasMore,
        page,
        fetchInitialOutfits,
        fetchMoreOutfits,
        reset,
    };
}
