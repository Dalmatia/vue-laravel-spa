<script setup>
import SearchHeaderSection from '../Components/Search/SearchHeaderSection.vue';
import FilterPanel from '../Components/Search/FilterPanel.vue';
import OutfitList from '../Components/Search/OutfitList.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import SelectColor from './SelectColor.vue';
import GenderSelectModal from '../Components/Search/Modals/GenderSelectModal.vue';

import { useSearchOutfits } from '../src/composables/search/useSearchOutfits';
import { useSearchUI } from '../src/composables/search/useSearchUI';
import { useResponsive } from '../src/composables/search/useResponsive';
import { useOutfitEvents } from '../src/composables/outfit/useOutfitEvents';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';
import { specialColors } from '../src/specialColors';

const sortOptions = {
    popular: '人気順',
    latest: '新着順',
    oldest: '古い順',
};

const search = useSearchOutfits();

const ui = useSearchUI(search.applyFilters, search.clearFilters);

const { isMobile } = useResponsive();

useOutfitEvents(search.applyFilters);

const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();

const { getColorClass, getColorStyle } = specialColors();

const handleSortChange = async () => {
    await search.applyFilters();
};

const openColorModal = () => {
    ui.openModal.value = true;
};

const selectColor = (color) => {
    if (search.filters.value.color?.id === color?.id) {
        // 同じ色をもう一度選ぶ → 選択解除
        search.filters.value.color = null;
    } else {
        // 新しい色を選択
        search.filters.value.color = color;
    }
};
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
                    v-model:sortOrder="search.sortOrder.value"
                    v-model:openFilter="ui.openFilter.value"
                    @sort-change="handleSortChange"
                />

                <!-- フィルターパネル -->
                <transition
                    name="fade-slide"
                    enter-active-class="transition duration-200"
                    leave-active-class="transition duration-150"
                >
                    <FilterPanel
                        :filters="search.filters.value"
                        :genders="search.genders.value"
                        :mainCategories="search.mainCategories.value"
                        :subCategories="search.filteredSubCategories.value"
                        :seasons="search.seasons.value"
                        :openFilter="ui.openFilter.value"
                        :openColorModal="openColorModal"
                        :getColorClass="getColorClass"
                        :getColorStyle="getColorStyle"
                        :isMobile="isMobile"
                        @open-gender-modal="ui.isGenderModalOpen.value = true"
                        @request-clear="ui.handleClearFilters"
                        @request-submit="ui.handleFilterByCategory"
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
                @loadMore="
                    search.fetchOutfits({
                        filters: search.filters.value,
                        sortOrder: search.sortOrder.value,
                        isLoadMore: true,
                    })
                "
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
        v-if="ui.openModal.value"
        :colors="search.colors.value"
        :selectedColor="search.filters.color?.id"
        @color-selected="selectColor($event)"
        @close="ui.openModal.value = false"
    />

    <GenderSelectModal
        v-if="ui.isGenderModalOpen.value"
        :genders="search.genders.value"
        :modelValue="search.filters.value.gender"
        @update:modelValue="search.filters.value.gender = $event"
        @close="ui.isGenderModalOpen.value = false"
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
