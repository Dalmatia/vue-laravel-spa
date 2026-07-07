<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useCategoryData } from '../src/composables/useCategoryData';
import { useCategoryItems } from '../src/composables/item/useCategoryItems';
import { useItemOverlay } from '../src/composables/item/useItemOverlay';
import { useItemEvents } from '../src/composables/item/useItemEvents';

import CategoryItemsHeader from '../Components/CategoryItems/CategoryItemsHeader.vue';
import CategoryItemGrid from '../Components/CategoryItems/CategoryItemGrid.vue';
import CategoryPagination from '../Components/CategoryItems/CategoryPagination.vue';
import ShowItemOverlay from '../Components/Items/ShowItemOverlay.vue';

const route = useRoute();
const authStore = useAuthStore();

const { loadEnums, getMainCategoryName, getSubCategoryName } =
    useCategoryData();

const mainCategoryId = ref(0);

const {
    items,
    totalItems,
    isLoading,
    currentPage,
    lastPage,
    fetchCategoryItems,
    deleteItem,
    goToNextPage,
    goToPrevPage,
} = useCategoryItems(mainCategoryId);

const { currentItem, openOverlay, openItemOverlay, closeItemOverlay } =
    useItemOverlay();

// 登録アイテムの削除
const handleDeleteItem = async (object) => {
    if (object.deleteType !== 'Item') return;
    await deleteItem(object.id);
    closeItemOverlay();
};

const refreshItems = () => {
    fetchCategoryItems(currentPage.value);
};

useItemEvents(refreshItems);

const itemSubCategoryName = (item) =>
    getSubCategoryName(mainCategoryId.value, item.sub_category);

onMounted(async () => {
    // ページ遷移時にパラメータを取得
    const routeParams = route.params;
    await loadEnums();
    if (routeParams.mainCategory) {
        mainCategoryId.value = Number(routeParams.mainCategory);
    }
});
</script>

<template>
    <div
        id="CategorizedItemPage"
        class="w-full max-w-[1000px] lg:ml-0 md:ml-[10px] md:pl-20 px-4 md:w-[90vw]"
    >
        <div id="contentsBody" class="pt-0">
            <div id="main_content">
                <!-- ヘッダーここから -->
                <CategoryItemsHeader
                    :userName="authStore.user.name"
                    :userImage="authStore.user.file"
                    :mainCategoryName="getMainCategoryName(mainCategoryId)"
                    :totalItems="totalItems"
                />
                <!-- ヘッダーここまで -->

                <div id="item-list" class="relative z-[1]">
                    <!-- アイテムリスト -->
                    <CategoryItemGrid
                        :items="items"
                        :subCategoryName="itemSubCategoryName"
                        @item-click="openItemOverlay($event)"
                    />
                    <!-- アイテムリストここまで -->
                </div>

                <!-- ページネーション部分 -->
                <CategoryPagination
                    :currentPage="currentPage"
                    :lastPage="lastPage"
                    @next-page="goToNextPage()"
                    @prev-page="goToPrevPage()"
                />
                <!-- ページネーション部分ここまで -->
            </div>
        </div>
    </div>

    <ShowItemOverlay
        v-if="openOverlay"
        :item="currentItem"
        @delete-selected="handleDeleteItem($event)"
        @close-overlay="closeItemOverlay()"
    />
</template>
