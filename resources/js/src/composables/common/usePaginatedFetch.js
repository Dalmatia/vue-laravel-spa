import { ref } from 'vue';

export function usePaginatedFetch(fetchApi) {
    const items = ref([]);
    const page = ref(1);
    const hasMore = ref(true);
    const isLoading = ref(false);
    const isFetchingMore = ref(false);

    // ===== 初回取得 =====
    const fetchInitial = async (params = {}) => {
        isLoading.value = true;
        page.value = 1;

        try {
            const response = await fetchApi({
                page: page.value,
                ...params,
            });

            items.value = response.items;
            hasMore.value = response.hasMore;
            page.value++;
        } finally {
            isLoading.value = false;
        }
    };

    // ===== 追加取得 =====
    const fetchMore = async (params = {}) => {
        if (isFetchingMore.value || !hasMore.value) return;

        isFetchingMore.value = true;

        try {
            const response = await fetchApi({
                page: page.value,
                ...params,
            });

            items.value.push(...response.items);

            hasMore.value = response.hasMore;

            page.value++;
        } finally {
            isFetchingMore.value = false;
        }
    };

    // ===== リセット =====
    const reset = () => {
        items.value = [];
        page.value = 1;
        hasMore.value = true;
        isLoading.value = false;
        isFetchingMore.value = false;
    };

    return {
        items,
        page,
        hasMore,
        isLoading,
        isFetchingMore,
        fetchInitial,
        fetchMore,
        reset,
    };
}
