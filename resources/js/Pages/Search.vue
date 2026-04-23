<script setup>
import SearchHeaderSection from '../Components/Search/SearchHeaderSection.vue';
import FilterPanel from '../Components/Search/FilterPanel.vue';
import OutfitList from '../Components/Search/OutfitList.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import SelectColor from './SelectColor.vue';
import GenderSelectModal from '../Components/Search/Modals/GenderSelectModal.vue';

import { useSearchOutfits } from '../src/composables/search/useSearchOutfits';
import { useSearchForm } from '../src/composables/search/useSearchForm';
import { useInitEnums } from '../src/composables/useInitEnums';
import { useResponsive } from '../src/composables/search/useResponsive';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';
import { specialColors } from '../src/specialColors';
import { useSearchUI } from '../src/composables/search/useSearchUI';

const sortOptions = {
    popular: '人気順',
    latest: '新着順',
    oldest: '古い順',
};

const search = useSearchOutfits();

const { genders, mainCategories, subCategories, colors, seasons } =
    useInitEnums();

const {
    localFilters,
    localSortOrder,
    filteredSubCategory,
    applyFilters,
    clearFilters,
    selectColor,
} = useSearchForm(search, subCategories);

const { isMobile } = useResponsive();

const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();

const { getColorClass, getColorStyle } = specialColors();

const {
    openFilter,
    openModal,
    isGenderModalOpen,
    openColorModal,
    handleFilterByCategory,
    handleClearFilters,
} = useSearchUI(applyFilters, clearFilters);
</script>

<template>
    <div
        class="box-border mx-auto max-w-6xl min-h-fit md:pl-[88px] lg:px-7 w-full md:-mt-6"
    >
        <!-- ヘッダー -->
        <nav class="flex flex-col sticky top-0 bg-white z-10 border-b">
            <header>
                <SearchHeaderSection
                    :isMobile="isMobile"
                    :sortOptions="sortOptions"
                    v-model:sortOrder="localSortOrder"
                    v-model:openFilter="openFilter"
                    @sort-change="applyFilters"
                />

                <!-- フィルターパネル -->
                <transition
                    name="fade-slide"
                    enter-active-class="transition duration-200"
                    leave-active-class="transition duration-150"
                >
                    <FilterPanel
                        :filters="localFilters"
                        :genders="genders"
                        :mainCategories="mainCategories"
                        :subCategories="filteredSubCategory"
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

        <!-- コーディネート一覧 -->
        <div class="z-[1] md:pt-5">
            <OutfitList
                :isMobile="isMobile"
                :isLoading="search.isLoading.value"
                :outfits="search.outfits.value"
                :hasMore="search.hasMore.value"
                :isFetchingMore="search.isFetchingMore.value"
                @openOutfitOverlay="toggleOutfitOverlay($event)"
                @loadMore="search.loadMore"
            />
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
        :selectedColor="localFilters.color?.id"
        @color-selected="selectColor($event)"
        @close="openModal = false"
    />

    <GenderSelectModal
        v-if="isGenderModalOpen"
        :genders="genders"
        :modelValue="localFilters.gender"
        @update:modelValue="localFilters.gender = $event"
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
