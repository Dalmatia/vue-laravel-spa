<script setup>
import { ref, computed } from 'vue';
import ChevronUp from 'vue-material-design-icons/ChevronUp.vue';
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';

const props = defineProps({
    currentSelect: Date, // 現在の選択状態
    maxYear: Number, // 選択可能な最大年（通常は現在の年）
});

const emit = defineEmits(['update:currentSelect', 'close']);

const selectedYear = ref(props.currentSelect.getFullYear());
const selectedMonth = ref(props.currentSelect.getMonth() + 1);
const tempYear = ref(selectedYear.value);

const showYearSelection = ref(false);
const yearPageStart = ref(selectedYear.value - 10 + 1);

const yearRange = computed(() =>
    Array.from({ length: 12 }, (_, i) => yearPageStart.value + i).filter(
        (year) => year <= props.maxYear
    )
);

const applySelection = () => {
    const date = new Date(selectedYear.value, selectedMonth.value - 1, 1);
    emit('update:currentSelect', date);
    emit('close');
};

const applyYearSelection = () => {
    selectedYear.value = tempYear.value;
    showYearSelection.value = false;
};

const changeYearPage = (direction) => {
    if (direction === 'next' && yearPageStart.value + 12 <= props.maxYear) {
        yearPageStart.value += 12;
    } else if (
        direction === 'prev' &&
        yearPageStart.value - 12 >= props.maxYear - 100
    ) {
        yearPageStart.value -= 12;
    }
};
</script>

<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50"
        @click.self="$emit('close')"
    >
        <div
            class="bg-white p-4 md:p-6 rounded shadow-lg w-80 md:w-[75%] xl:w-[55%] max-w-full text-sm md:text-base xl:text-lg"
        >
            <!-- 年選択 -->
            <div v-if="showYearSelection">
                <div class="flex justify-between items-center mb-4">
                    <button
                        @click="changeYearPage('prev')"
                        class="text-lg md:text-xl"
                    >
                        ←
                    </button>
                    <span class="font-bold">
                        {{ yearPageStart }} -
                        {{ Math.min(yearPageStart + 11, props.maxYear) }}
                    </span>
                    <button
                        @click="changeYearPage('next')"
                        :class="{
                            'opacity-25 cursor-not-allowed':
                                yearRange[yearRange.length - 1] >=
                                props.maxYear,
                        }"
                        :disabled="
                            yearRange[yearRange.length - 1] >= props.maxYear
                        "
                        class="text-lg md:text-xl"
                    >
                        →
                    </button>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="year in yearRange"
                        :key="year"
                        @click="tempYear = year"
                        :class="[
                            'py-2 px-3 rounded text-sm md:text-base',
                            { 'bg-blue-500 text-white': year === tempYear },
                        ]"
                    >
                        {{ year }}
                    </button>
                </div>
                <div class="flex justify-end space-x-2 mt-5">
                    <button
                        class="px-3 py-1 md:px-4 md:py-2 bg-gray-200 rounded text-sm md:text-base"
                        @click="showYearSelection = false"
                    >
                        キャンセル
                    </button>
                    <button
                        class="px-3 py-1 md:px-4 md:py-2 bg-blue-500 text-white rounded text-sm md:text-base"
                        @click="applyYearSelection()"
                    >
                        適用
                    </button>
                </div>
            </div>

            <!-- 月選択 -->
            <div v-else>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <button
                            class="font-bold text-lg md:text-xl"
                            @click="showYearSelection = true"
                        >
                            {{ selectedYear }}年
                        </button>
                        <button
                            @click="selectedYear--"
                            class="w-4 h-4 md:w-5 md:h-5"
                        >
                            <ChevronUp />
                        </button>
                        <button
                            @click="selectedYear++"
                            :class="[
                                'w-4 h-4 md:w-5 md:h-5',
                                {
                                    'opacity-25 cursor-not-allowed':
                                        selectedYear === props.maxYear,
                                },
                            ]"
                            :disabled="selectedYear === props.maxYear"
                        >
                            <ChevronDown />
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="month in 12"
                        :key="month"
                        @click="selectedMonth = month"
                        :class="[
                            'py-2 px-3 rounded text-sm md:text-base',
                            {
                                'bg-blue-500 text-white':
                                    month === selectedMonth,
                            },
                        ]"
                    >
                        {{ month }}月
                    </button>
                </div>
                <div class="flex justify-end space-x-2 mt-5">
                    <button
                        class="px-3 py-1 md:px-4 md:py-2 bg-gray-200 rounded text-sm md:text-base"
                        @click="$emit('close')"
                    >
                        キャンセル
                    </button>
                    <button
                        class="px-3 py-1 md:px-4 md:py-2 bg-blue-500 text-white rounded text-sm md:text-base"
                        @click="applySelection()"
                    >
                        適用
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
