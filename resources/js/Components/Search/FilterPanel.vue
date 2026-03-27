<script setup>
import GenderSection from './FilterSections/GenderSection.vue';
import MainCategorySection from './FilterSections/MainCategorySection.vue';
import SubCategorySection from './FilterSections/SubCategorySection.vue';
import ColorSection from './FilterSections/ColorSection.vue';
import SeasonSection from './FilterSections/SeasonSection.vue';
import FilterActions from './FilterSections/FilterActions.vue';

const props = defineProps({
    filters: Object,
    genders: Array,
    mainCategories: Array,
    filteredSubCategories: Array,
    seasons: Array,
    openFilter: Boolean,
    isMobile: Boolean,
    openColorModal: Function,
    getColorClass: Function,
    getColorStyle: Function,
});

const emit = defineEmits([
    'open-gender-modal',
    'request-clear',
    'request-submit',
]);
</script>

<template>
    <div
        v-if="openFilter"
        :class="[
            isMobile
                ? 'fixed top-14 pt-1 z-40 overflow-hidden'
                : 'absolute top-20',
            'left-0 w-full bg-white',
        ]"
    >
        <form class="w-full p-4">
            <!-- 性別 -->
            <GenderSection
                v-model="filters.gender"
                :isMobile="isMobile"
                :genders="genders"
                @open="$emit('open-gender-modal')"
            />

            <!-- メインカテゴリー -->
            <MainCategorySection
                v-model="filters.mainCategory"
                :isMobile="isMobile"
                :mainCategories="mainCategories"
            />

            <!-- サブカテゴリー -->
            <SubCategorySection
                v-model="filters.subCategory"
                :isMobile="isMobile"
                :subCategories="filteredSubCategories"
            />

            <!-- カラー -->
            <ColorSection
                :isMobile="isMobile"
                :color="filters.color"
                :getColorClass="getColorClass"
                :getColorStyle="getColorStyle"
                @open-color-modal="openColorModal"
            />

            <!-- シーズン -->
            <SeasonSection
                v-model="filters.season"
                :isMobile="isMobile"
                :seasons="seasons"
            />

            <!-- 共通のアクション -->
            <FilterActions
                :isMobile="isMobile"
                @request-clear="emit('request-clear')"
                @request-submit="emit('request-submit')"
            />
        </form>
    </div>
</template>
