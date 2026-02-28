<script setup>
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';
import Grid from 'vue-material-design-icons/Grid.vue';
import ColumnOutline from 'vue-material-design-icons/ViewColumnOutline.vue';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

defineProps({
    displayDate: { type: String, required: true },
    viewMode: { type: String, required: true },
});

const emit = defineEmits([
    'toggle-month-picker', // 年月ピッカー開閉
    'change-view', // 表示モード変更
    'prev-month', // 前の月
    'next-month', // 次の月
    'prev-week', // 前の週
    'next-week', // 次の週
]);

const baseButtonClass =
    'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded';
const activeClass = 'bg-gray-500 text-white';
</script>

<template>
    <div class="flex flex-wrap justify-between p-3">
        <!-- 年月 -->
        <h2
            class="text-xl cursor-pointer flex items-center"
            @click="emit('toggle-month-picker')"
        >
            {{ displayDate }}
            <ChevronDown />
        </h2>

        <!-- 表示切り替えボタン -->
        <div class="flex justify-end space-x-2">
            <button
                :class="[
                    baseButtonClass,
                    { [activeClass]: viewMode === 'month' },
                ]"
                @click="emit('change-view', 'month')"
            >
                <div class="flex items-center">
                    <Grid class="mr-1" />
                    月
                </div>
            </button>
            <button
                :class="[
                    baseButtonClass,
                    { [activeClass]: viewMode === 'week' },
                ]"
                @click="emit('change-view', 'week')"
            >
                <div class="flex items-center">
                    <ColumnOutline class="mr-1" />
                    週
                </div>
            </button>
        </div>
    </div>
    <div class="flex justify-between">
        <button
            class="text-gray-500"
            @click="
                viewMode === 'month' ? emit('prev-month') : emit('prev-week')
            "
        >
            <ChevronLeft :size="40" />
        </button>
        <button
            class="text-gray-500"
            @click="
                viewMode === 'month' ? emit('next-month') : emit('next-week')
            "
        >
            <ChevronRight :size="40" />
        </button>
    </div>
</template>
