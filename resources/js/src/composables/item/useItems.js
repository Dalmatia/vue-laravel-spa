import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useItemEvents } from './useItemEvents';

export function useItems() {
    const items = ref([]);
    const isLoading = ref(false);
    const hasError = ref(false);
    const route = useRoute();
    const router = useRouter();
    const userId = route.params.id;

    // 登録アイテムの表示
    const fetchItems = async () => {
        isLoading.value = true;
        hasError.value = false;

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

    // カテゴリごとに分類したアイテム一覧
    const categorizedItems = computed(() => {
        return items.value.reduce((categories, item) => {
            if (!categories[item.main_category]) {
                categories[item.main_category] = [];
            }

            categories[item.main_category].push(item);
            return categories;
        }, {});
    });

    // フォルダークリック時にページ遷移
    const navigateToCategory = (mainCategoryId) => {
        const path = `/user/${userId}/items/${mainCategoryId}`;
        router.push(path);
    };

    useItemEvents(fetchItems);

    onMounted(() => {
        fetchItems();
    });

    return {
        items,
        isLoading,
        hasError,
        fetchItems,
        categorizedItems,
        navigateToCategory,
    };
}
