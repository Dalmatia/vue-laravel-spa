import { ref } from 'vue';

export function useItems() {
    const items = ref([]);
    const isLoading = ref(false);
    const hasError = ref(false);

    // 登録アイテムの表示
    const fetchItems = async () => {
        isLoading.value = true;

        try {
            const response = await axios.get('/api/items');

            items.value = response.data.items;
        } catch (error) {
            console.error(error);
            hasError.value = true;
        } finally {
            setTimeout(() => {
                isLoading.value = false;
            }, 300);
        }
    };

    return {
        items,
        isLoading,
        hasError,
        fetchItems,
    };
}
