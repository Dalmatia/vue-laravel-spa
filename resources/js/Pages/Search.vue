<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import SearchHeaderSection from '../Components/Search/SearchHeaderSection.vue';
import FilterPanel from '../Components/Search/FilterPanel.vue';
import OutfitList from '../Components/Search/OutfitList.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import SelectColor from './SelectColor.vue';
import GenderSelectModal from '../Components/Search/Modals/GenderSelectModal.vue';

import { useSearchOutfits } from '../src/composables/useSearchOutfits';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';
import { specialColors } from '../src/specialColors';

let timeout;
const openFilter = ref(false);
const openModal = ref(false);
const isMobile = ref(window.innerWidth < 768);
const sortOptions = { popular: '人気順', latest: '新着順', oldest: '古い順' };

const {
    outfits,
    isLoading,
    filters,
    sortOrder,
    genders,
    mainCategories,
    filteredSubCategories,
    colors,
    seasons,
    hasMore,
    isFetchingMore,
    fetchOutfits,
    resetAndFetch,
    filterByCategory,
    clearFilters,
} = useSearchOutfits();

const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();
const { getColorClass, getColorStyle } = specialColors();

const handleResize = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        isMobile.value = window.innerWidth < 768;
    }, 100);
};

const handleSortChange = async () => {
    await resetAndFetch();
};

const handleFilterByCategory = () => {
    filterByCategory();
    openFilter.value = false;
};

const handleClearFilters = () => {
    clearFilters();
    openFilter.value = false;
};

const openColorModal = () => {
    openModal.value = true;
};

const selectColor = (color) => {
    filters.value.color = color;
};

const isGenderModalOpen = ref(false);

const handleDeleted = () => {
    resetAndFetch();
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    handleResize();
    window.addEventListener('outfit-created', resetAndFetch);
    window.addEventListener('outfit-updated', resetAndFetch);
    window.addEventListener('outfit-deleted', handleDeleted);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('outfit-created', resetAndFetch);
    window.removeEventListener('outfit-updated', resetAndFetch);
    window.removeEventListener('outfit-deleted', handleDeleted);
    if (timeout) clearTimeout(timeout);
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
                        :genders="genders"
                        :mainCategories="mainCategories"
                        :filteredSubCategories="filteredSubCategories"
                        :seasons="seasons"
                        :openFilter="openFilter"
                        :openColorModal="openColorModal"
                        :getColorClass="getColorClass"
                        :getColorStyle="getColorStyle"
                        :isMobile="isMobile"
                        @open-gender-modal="isGenderModalOpen = true"
                        @request-clear="handleClearFilters"
                        @request-submit="handleFilterByCategory"
                    />
                </transition>
            </header>
        </nav>

        <!-- OutfitList 共通 -->
        <div class="z-[1]">
            <OutfitList
                :isMobile="isMobile"
                :isLoading="isLoading"
                :outfits="outfits"
                :hasMore="hasMore"
                :isFetchingMore="isFetchingMore"
                @openOutfitOverlay="toggleOutfitOverlay($event)"
                @loadMore="fetchOutfits(true)"
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
    <GenderSelectModal
        v-if="isGenderModalOpen"
        :genders="genders"
        :modelValue="filters.gender"
        @update:modelValue="filters.gender = $event"
        @close="isGenderModalOpen = false"
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
