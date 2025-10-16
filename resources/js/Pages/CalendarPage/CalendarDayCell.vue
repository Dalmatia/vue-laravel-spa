<script setup>
import { computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    day: {
        type: Object,
        required: true,
    },
    currentMonth: {
        type: String,
        required: false,
        default: null,
    },
    isWeekMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select']);
const isToday = computed(() =>
    dayjs().isSame(dayjs(props.day.fullDate), 'day')
);
</script>

<template>
    <div
        class="flex-1 min-h-[125px] border-r-[1px] border-b-[1px] border-solid border-gray-300 text-center cursor-pointer"
        :class="[
            // 月表示: 当月以外をグレー
            { 'bg-gray-200': !isWeekMode && currentMonth !== day.month },
        ]"
        @click="emit('select', day.fullDate)"
    >
        <span class="relative inline-flex items-center justify-center w-full">
            <!-- 今日マーク（丸背景） -->
            <span
                v-if="isToday"
                class="absolute rounded-full bg-gray-300 h-6 w-6 md:h-8 md:w-8"
            ></span>

            <!-- 日付 -->
            <span
                class="z-10 font-medium"
                :class="[
                    // 今日が日曜の場合
                    isToday && dayjs(day.fullDate).day() === 0
                        ? 'text-red-500'
                        : // 今日が土曜の場合
                        isToday && dayjs(day.fullDate).day() === 6
                        ? 'text-blue-500'
                        : // 今日（平日）
                        isToday
                        ? 'text-gray-900'
                        : // 今日以外 → 通常の曜日色
                        dayjs(day.fullDate).day() === 0
                        ? 'text-red-500'
                        : dayjs(day.fullDate).day() === 6
                        ? 'text-blue-500'
                        : 'text-gray-900',
                ]"
            >
                {{ day.day }}
            </span>
        </span>

        <!-- コーディネート画像 -->
        <a v-if="day.outfit" role="button" tabindex="0">
            <img
                class="w-20 h-24 my-auto mx-auto"
                :src="day.outfit"
                :alt="`${day.month}の${day.day}日のコーディネート`"
            />
        </a>
    </div>
</template>
