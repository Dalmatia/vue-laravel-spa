<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useFollowStore } from '../../stores/follow';

import SortDropdown from './SortDropdown.vue';
import FilterPanel from './FilterPanel.vue';
import OutfitList from './OutfitList.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import SelectColor from '../SelectColor.vue';
import { specialColors } from '../../src/specialColors';
import { useOutfitOverlay } from '../../src/composables/useOutfitOverlay';

import Sort from 'vue-material-design-icons/SortVariant.vue';
import Filter from 'vue-material-design-icons/Tune.vue';
import Close from 'vue-material-design-icons/Close.vue';
import axios from 'axios';

let openFilter = ref(false);
const outfits = ref([]);
const followStore = useFollowStore();
const { getColorClass, getColorStyle } = specialColors();
const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();

// 検索する項目毎のデータの取得
const mainCategories = ref([]);
const subCategories = ref([]);
const colors = ref([]);
const seasons = ref([]);

const filters = ref({
    mainCategory: '',
    subCategory: '',
    color: null,
    season: '',
});

const openModal = ref(false);
const sortOptions = {
    popular: '人気順',
    latest: '新着順',
    oldest: '古い順',
};
const sortOrder = ref('popular');
const isLoading = ref(true);
const isMobile = ref(window.innerWidth < 768);

const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
};

// 投稿したコーディネートの表示
const fetchOutfits = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/outfits', {
            params: { ...filters.value, sort: sortOrder.value },
        });
        outfits.value = response.data.outfits;
        // 各ユーザーのフォロー状態をチェック
        const follows = outfits.value.map((outfit) => outfit.user.id);
        await followStore.fetchFollowStatus(follows);
    } catch (error) {
        console.error('コーディネート一覧の取得に失敗しました。', error);
    } finally {
        isLoading.value = false;
    }
};

const getEnums = async () => {
    try {
        const response = await axios.get('/api/enums');
        mainCategories.value = response.data.mainCategories;
        subCategories.value = response.data.subCategories;
        colors.value = response.data.colors;
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

const selectColor = (color) => {
    openModal.value = true;
    filters.value.color = color;
};

const filterByCategory = () => {
    fetchOutfits();
    openFilter.value = false;
};

// 指定した条件をクリアする
const clearFilters = () => {
    filters.value = {
        mainCategory: '',
        subCategory: '',
        color: null,
        season: '',
    };
    filterByCategory();
};

onMounted(async () => {
    window.addEventListener('resize', handleResize);
    handleResize();

    try {
        await Promise.all([fetchOutfits(), getEnums()]);
    } catch (error) {
        console.error('データの取得に失敗しました。', error);
    }

    window.addEventListener('outfit-created', fetchOutfits);
    window.addEventListener('outfit-updated', fetchOutfits);
    window.addEventListener('outfit-deleted', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('outfit-created', fetchOutfits);
    window.removeEventListener('outfit-updated', fetchOutfits);
    window.removeEventListener('outfit-deleted', fetchOutfits);
});
</script>

<template>
    <div
        class="box-border mx-auto max-w-6xl min-h-fit md:pl-[88px] lg:px-7 w-full"
    >
        <!-- デスクトップ用レイアウト -->
        <div class="hidden md:block md:w-[650px] lg:w-[880px]">
            <!-- ヘッダー部分 -->
            <nav
                class="flex flex-col box-border items-stretch sticky top-0 bg-white"
            >
                <div class="relative flex flex-col h-auto">
                    <header class="relative flex flex-col h-auto">
                        <h1 class="text-center mb-5 text-lg font-semibold">
                            コーディネート検索
                        </h1>
                        <div
                            class="flex justify-between items-center border-t border-gray-200"
                        >
                            <div
                                class="flex items-center justify-between w-full px-4 h-11"
                            >
                                <SortDropdown
                                    v-model:sortOrder="sortOrder"
                                    :sortOptions="sortOptions"
                                    @update:sortOrder="fetchOutfits()"
                                >
                                    <template #icon>
                                        <Sort :size="27" />
                                    </template>
                                </SortDropdown>
                                <button
                                    class="p-0 bg-transparent border-none cursor-pointer"
                                    @click="openFilter = !openFilter"
                                >
                                    <div
                                        class="p-0 bg-transparent border-none cursor-pointer"
                                    >
                                        <Filter v-if="!openFilter" :size="27" />
                                        <Close v-else :size="27" />
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- 絞り込み検索ドロップダウンメニュー -->
                        <FilterPanel
                            :filters="filters"
                            :mainCategories="mainCategories"
                            :subCategories="subCategories"
                            :seasons="seasons"
                            :openFilter="openFilter"
                            :selectColor="selectColor"
                            :getColorClass="getColorClass"
                            :getColorStyle="getColorStyle"
                            :isMobile="isMobile"
                            @clearFilters="clearFilters"
                            @filterByCategory="filterByCategory"
                        />
                        <!-- ドロップダウンメニューここまで -->
                    </header>
                </div>
            </nav>
            <!-- ヘッダーここまで -->

            <!-- コーディネート表示部分 -->
            <div id="outfit_section">
                <OutfitList
                    :isLoading="isLoading"
                    :outfits="outfits"
                    @openOutfitOverlay="toggleOutfitOverlay($event)"
                />
            </div>
            <!-- コーディネート表示部分ここまで -->
        </div>
        <!-- モバイル用レイアウト -->
        <div class="block md:hidden">
            <!-- ヘッダー部分 -->
            <nav class="relative flex-col items-stretch box-border">
                <header
                    class="bg-white flex flex-wrap text-[16px] font-semibold left-0 md:left-20 xl:left-64 fixed right-0 top-0 z-[1] border-b border-solid md:pl-0 xl:pl-0"
                >
                    <div
                        class="fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300"
                    >
                        <div class="items-center flex basis-8 flex-row">
                            <SortDropdown
                                v-model:sortOrder="sortOrder"
                                :sortOptions="sortOptions"
                                @update:sortOrder="fetchOutfits()"
                            >
                                <template #icon>
                                    <Sort :size="27" />
                                </template>
                            </SortDropdown>
                        </div>
                        <h1 class="text-center">コーディネート検索</h1>
                        <div class="flex items-center">
                            <button
                                class="p-0 bg-transparent border-none cursor-pointer flex items-center"
                                type="button"
                                @click="openFilter = !openFilter"
                            >
                                <Filter v-if="!openFilter" :size="27" />

                                <Close v-if="openFilter" :size="27" />
                            </button>
                        </div>
                    </div>
                </header>
            </nav>
            <!-- ヘッダーここまで -->

            <!-- 絞り込み検索ドロップダウンメニュー -->
            <FilterPanel
                :filters="filters"
                :mainCategories="mainCategories"
                :subCategories="subCategories"
                :seasons="seasons"
                :openFilter="openFilter"
                :selectColor="selectColor"
                :getColorClass="getColorClass"
                :getColorStyle="getColorStyle"
                :isMobile="isMobile"
                @clearFilters="clearFilters"
                @filterByCategory="filterByCategory"
            />
            <!-- ドロップダウンメニューここまで -->

            <!-- コーディネート表示部分 -->
            <div id="outfit" class="z-[1]">
                <OutfitList
                    :isLoading="isLoading"
                    :outfits="outfits"
                    @openOutfitOverlay="toggleOutfitOverlay($event)"
                />
            </div>
            <!-- コーディネート表示部分ここまで -->
        </div>
    </div>
    <ShowOutfitOverlay
        v-if="overlayState.open"
        :outfit="overlayState.currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="toggleOutfitOverlay(null)"
    />
    <SelectColor
        v-if="openModal"
        :colors="colors"
        :selectedColor="filters.color?.id"
        @color-selected="selectColor($event)"
        @close="openModal = false"
    />
</template>
