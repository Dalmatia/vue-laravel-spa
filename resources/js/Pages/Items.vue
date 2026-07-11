<script setup>
import { onMounted } from 'vue';
import { useItems } from '../src/composables/item/useItems';
import { useEnumStore } from '../stores/enum';

const { categorizedItems, navigateToCategory } = useItems();

const enumStore = useEnumStore();
const { fetchEnums, getMainCategoryName } = enumStore;

onMounted(async () => {
    await fetchEnums();
});
</script>

<template>
    <div class="grid md:gap-4 gap-1 grid-cols-2 relative">
        <!-- カテゴリごとにアイテムを表示 -->
        <div
            v-for="(mainCategoryItems, mainCategoryId) in categorizedItems"
            :key="mainCategoryId"
        >
            <h2 class="text-xs mb-1 font-semibold">
                {{ getMainCategoryName(mainCategoryId) }}
            </h2>
            <!-- カテゴリー毎にフォルダー分け -->
            <div class="border border-gray-300 p-2 rounded-md mb-4">
                <div
                    class="grid grid-cols-3 items-center justify-center cursor-pointer relative"
                    @click="navigateToCategory(mainCategoryId)"
                >
                    <div
                        v-for="item in mainCategoryItems.slice(0, 6)"
                        :key="item.id"
                    >
                        <img
                            v-if="item.file"
                            :src="item.file"
                            loading="lazy"
                            decoding="async"
                            class="flex-shrink-0 aspect-square mx-auto z-0 object-cover cursor-pointer"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
