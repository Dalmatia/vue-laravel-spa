import { usePaginatedFetch } from '../common/usePaginatedFetch';

export function useUserFetch(userId) {
    // ===== コーディネート一覧取得 =====
    const fetchUserApi = async ({ page }) => {
        const response = await axios.get(
            `/api/users/${userId.value}?page=${page}`,
        );

        return {
            items: response.data.outfits,
            hasMore: response.data.meta.has_more_pages,
        };
    };

    const {
        items: outfits,
        page: currentPage,
        hasMore: hasMorePages,
        isLoading,
        isFetchingMore,
        fetchInitial,
        fetchMore,
        reset,
    } = usePaginatedFetch(fetchUserApi);

    // ===== リフレッシュ =====
    const refreshUserOutfits = async () => {
        reset();

        await fetchInitial();
    };

    return {
        outfits,
        currentPage,
        hasMorePages,
        isLoading,

        fetchUserOutfits: fetchMore,
        refreshUserOutfits,
        reset,
        fetchInitial,
    };
}
