<script setup>
import { ref, onMounted, onUnmounted, reactive } from 'vue';
import axios from 'axios';
import { getEnumStore } from '../stores/enum';
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();
const userId = route.params.id;
const items = ref([]);

// カテゴリごとにアイテムを分類するためのデータ構造
const categorizedItems = reactive({});
const getCategoryName = getEnumStore();

// 登録アイテムの表示
const fetchItems = async () => {
    try {
        const response = await axios.get('/api/items');
        items.value = response.data.items;

        // カテゴリごとにアイテムを分類
        categorizeItems();
    } catch (error) {
        console.error(error);
    }
};

// カテゴリごとにアイテムを分類する関数
const categorizeItems = () => {
    // カテゴリごとのデータを一度クリア
    for (const key in categorizedItems) {
        delete categorizedItems[key];
    }

    items.value.forEach((item) => {
        // 新しいアイテムだけを分類
        if (!categorizedItems[item.main_category]) {
            categorizedItems[item.main_category] = [];
        }
        categorizedItems[item.main_category].push(item);
    });
};

// フォルダークリック時にページ遷移
const navigateToCategory = (mainCategoryName) => {
    const path = `/user/${userId}/items/${mainCategoryName}`;
    router.push(path);
};

onMounted(() => {
    fetchItems();
    window.addEventListener('item-registered', fetchItems);
    window.addEventListener('item-updated', fetchItems);
});

onUnmounted(() => {
    window.removeEventListener('item-registered', fetchItems);
    window.removeEventListener('item-updated', fetchItems);
});
</script>

<template>
    <div class="grid md:gap-4 gap-1 grid-cols-2 relative">
        <!-- カテゴリごとにアイテムを表示 -->
        <div
            v-for="(mainCategoryItems, mainCategoryName) in categorizedItems"
            :key="mainCategoryName"
        >
            <h2 class="text-xs mb-1 font-semibold">
                {{ getCategoryName.getMainCategoryName(mainCategoryName) }}
            </h2>
            <!-- カテゴリー毎にフォルダー分け -->
            <div class="border border-gray-300 p-2 rounded-md mb-4">
                <div
                    class="grid grid-cols-3 items-center justify-center cursor-pointer relative"
                    @click="navigateToCategory(mainCategoryName)"
                >
                    <div
                        v-for="item in mainCategoryItems.slice(0, 6)"
                        :key="item.id"
                    >
                        <img
                            v-if="item.file"
                            :src="item.file"
                            class="flex-shrink-0 aspect-square mx-auto z-0 object-cover cursor-pointer"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
