import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export function useCategoryItems(mainCategoryId) {
    const route = useRoute();
    const router = useRouter();

    const items = ref([]);
    const totalItems = ref(0);

    const isLoading = ref(false);
    const currentPage = ref(1);
    const lastPage = ref(1);

    // 登録アイテムの表示
    const fetchItems = async (page = 1) => {
        try {
            isLoading.value = true;

            const response = await axios.get(
                `/api/items/category/${mainCategoryId.value}`,
                {
                    params: { page },
                },
            );

            items.value = response.data.data;
            totalItems.value = response.data.total;

            currentPage.value = response.data.current_page;
            lastPage.value = response.data.last_page;
        } catch (error) {
            console.error(error);
        } finally {
            isLoading.value = false;
        }
    };

    const goToPage = async (page) => {
        await router.replace({
            query: {
                ...route.query,
                page,
            },
        });
    };

    const goToNextPage = () => {
        if (currentPage.value >= lastPage.value) return;

        goToPage(currentPage.value + 1);
    };

    const goToPrevPage = () => {
        if (currentPage.value <= 1) return;

        goToPage(currentPage.value - 1);
    };

    // 登録アイテムの削除
    const deleteItem = async (id) => {
        const targetPage =
            items.value.length === 1 && currentPage.value > 1
                ? currentPage.value - 1
                : currentPage.value;

        await axios.delete(`/api/items/${id}`);

        fetchItems(targetPage);
    };

    watch(
        [() => route.query.page, mainCategoryId],
        ([page, categoryId]) => {
            if (!categoryId) return;

            fetchItems(Number(page ?? 1));
        },
        { immediate: true },
    );

    return {
        items,
        totalItems,
        isLoading,

        currentPage,
        lastPage,

        fetchItems,
        deleteItem,

        goToNextPage,
        goToPrevPage,
    };
}
