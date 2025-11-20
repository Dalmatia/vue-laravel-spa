<script setup>
import SortDropdown from './SortDropdown.vue';
import TopNavUser from '../../Layouts/TopNavUser.vue';
import Sort from 'vue-material-design-icons/SortVariant.vue';
import Filter from 'vue-material-design-icons/Tune.vue';
import Close from 'vue-material-design-icons/Close.vue';
import { defineProps } from 'vue';

const props = defineProps({
    isMobile: Boolean,
    sortOrder: String,
    sortOptions: Object,
    openFilter: Boolean,
});
const emit = defineEmits([
    'update:sortOrder',
    'update:openFilter',
    'sort-change',
]);

const toggleFilter = () => emit('update:openFilter', !props.openFilter);
const onSortChange = (val) => {
    emit('update:sortOrder', val);
    emit('sort-change', val);
};
</script>

<template>
    <div>
        <!-- デスクトップ -->
        <div
            v-if="!isMobile"
            class="relative flex flex-col h-auto md:w-[680px] lg:w-[880px]"
        >
            <h1 class="text-center mb-5 text-lg font-semibold">
                コーディネート検索
            </h1>
            <div
                class="flex justify-between items-center border-t border-gray-200 h-11 px-4"
            >
                <SortDropdown
                    :sortOrder="sortOrder"
                    :sortOptions="sortOptions"
                    @update:sortOrder="onSortChange"
                >
                    <template #icon><Sort :size="27" /></template>
                </SortDropdown>
                <button
                    class="p-0 bg-transparent border-none cursor-pointer"
                    @click="toggleFilter"
                >
                    <Filter v-if="!openFilter" :size="27" />
                    <Close v-else :size="27" />
                </button>
            </div>
        </div>

        <!-- モバイル -->
        <div
            v-else
            class="bg-white flex flex-wrap text-[16px] font-semibold fixed top-0 left-0 right-0 z-[1] border-b border-solid"
        >
            <TopNavUser :title="'コーディネート検索'">
                <template #left>
                    <SortDropdown
                        :sortOrder="sortOrder"
                        :sortOptions="sortOptions"
                        @update:sortOrder="onSortChange"
                    >
                        <template #icon><Sort :size="27" /></template>
                    </SortDropdown>
                </template>
                <template #right>
                    <button
                        class="p-0 bg-transparent border-none cursor-pointer flex items-center"
                        @click="toggleFilter"
                    >
                        <Filter v-if="!openFilter" :size="27" />
                        <Close v-else :size="27" />
                    </button>
                </template>
            </TopNavUser>
        </div>
    </div>
</template>
