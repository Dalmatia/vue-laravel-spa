<script setup>
import { ref, onMounted, onUnmounted, reactive } from 'vue';
import axios from 'axios';
import { getEnumStore } from '../stores/enum';

import ShowItemOverlay from '../Components/Items/ShowItemOverlay.vue';

let currentItem = ref(null);
let openOverlay = ref(false);
const emit = defineEmits(['close']);
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

const openItemOverlay = (item) => {
    currentItem.value = item;
    openOverlay.value = true;
};

// 登録アイテムの削除
const deleteItem = (object) => {
    let url = '';
    if (object.deleteType === 'Item') {
        url = `/api/items/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                openOverlay.value = false;
                fetchItems();
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

onMounted(() => {
    fetchItems();
    window.addEventListener('item-created', fetchItems);
    window.addEventListener('item-updated', fetchItems);
});

onUnmounted(() => {
    window.removeEventListener('item-created', fetchItems);
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
                    class="grid grid-cols-2 md:grid-cols-3 items-center justify-center cursor-pointer relative"
                >
                    <div
                        v-for="item in mainCategoryItems.slice(0, 4)"
                        :key="item.id"
                    >
                        <img
                            v-if="item.file"
                            :src="item.file"
                            class="flex-shrink-0 aspect-square mx-auto z-0 object-cover cursor-pointer"
                            @click="openItemOverlay(item)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ShowItemOverlay
        v-if="openOverlay"
        :item="currentItem"
        @delete-selected="deleteItem($event)"
        @close-overlay="openOverlay = false"
    />
</template>

<!-- <div
        v-for="item in items"
        :key="item.id"
        class="flex items-center justify-center cursor-pointer relative"
    >
        <img
            v-if="item.file"
            :src="item.file"
            class="aspect-square mx-auto z-0 object-cover cursor-pointer"
            @click="openItemOverlay(item)"
        />
    </div> -->
