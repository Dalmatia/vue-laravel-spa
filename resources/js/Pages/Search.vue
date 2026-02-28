<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import SearchHeaderSection from '../Components/Search/SearchHeaderSection.vue';
import FilterPanel from '../Components/Search/FilterPanel.vue';
import OutfitList from '../Components/Search/OutfitList.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import SelectColor from './SelectColor.vue';

import { useSearchOutfits } from '../src/composables/useSearchOutfits';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';
import { specialColors } from '../src/specialColors';

const openFilter = ref(false);
const openModal = ref(false);
const isMobile = ref(window.innerWidth < 768);
const sortOptions = { popular: '人気順', latest: '新着順', oldest: '古い順' };

const {
    outfits,
    isLoading,
    filters,
    sortOrder,
    mainCategories,
    subCategories,
    colors,
    seasons,
    fetchOutfits,
    filterByCategory,
    clearFilters,
} = useSearchOutfits();

const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();
const { getColorClass, getColorStyle } = specialColors();

const handleResize = () => (isMobile.value = window.innerWidth < 768);

const handleSortChange = () => fetchOutfits();

const handleFilterByCategory = () => {
    filterByCategory();
    openFilter.value = false;
};

const handleClearFilters = () => {
    clearFilters();
    openFilter.value = false;
};

const selectColor = (color) => {
    openModal.value = true;
    filters.value.color = color;
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    handleResize();
    ['outfit-created', 'outfit-updated', 'outfit-deleted'].forEach((e) =>
        window.addEventListener(e, fetchOutfits)
    );
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    ['outfit-created', 'outfit-updated', 'outfit-deleted'].forEach((e) =>
        window.removeEventListener(e, fetchOutfits)
    );
});
</script>

<template>
    <div
        class="box-border mx-auto max-w-6xl min-h-fit md:pl-[88px] lg:px-7 w-full"
    >
        <!-- ヘッダー -->
        <nav
            class="flex flex-col box-border items-stretch sticky top-0 bg-white z-[1]"
        >
            <header>
                <SearchHeaderSection
                    :isMobile="isMobile"
                    :sortOptions="sortOptions"
                    v-model:sortOrder="sortOrder"
                    v-model:openFilter="openFilter"
                    @sort-change="handleSortChange"
                />

                <!-- FilterPanel 共通 -->
                <transition
                    name="fade-slide"
                    enter-active-class="transition duration-200"
                    leave-active-class="transition duration-150"
                >
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
                        @clearFilters="handleClearFilters"
                        @filterByCategory="handleFilterByCategory"
                    />
                </transition>
            </header>
        </nav>

        <!-- OutfitList 共通 -->
        <div class="z-[1]">
            <OutfitList
                :isLoading="isLoading"
                :outfits="outfits"
                @openOutfitOverlay="toggleOutfitOverlay($event)"
            />
        </div>
    </div>

    <!-- Overlay & ColorPicker -->
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

<style scoped>
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}
.fade-slide-enter-to {
    opacity: 1;
    transform: translateY(0);
}
.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
