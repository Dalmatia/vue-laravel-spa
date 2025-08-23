<script setup>
import dayjs from 'dayjs';

defineProps({
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
</script>

<template>
    <div
        class="flex-1 min-h-[125px] border-r-[1px] border-b-[1px] border-solid border-gray-300 text-center cursor-pointer"
        :class="[
            // 月表示: 当月以外をグレー
            { 'bg-gray-200': !isWeekMode && currentMonth !== day.month },
            // 日曜 → 赤
            { 'text-red-500': dayjs(day.fullDate).day() === 0 },
            // 土曜 → 青
            { 'text-blue-500': dayjs(day.fullDate).day() === 6 },
        ]"
        @click="emit('select', day.fullDate)"
    >
        {{ day.day }}
        <a v-if="day.outfit" role="button" tabindex="0">
            <img
                class="w-20 h-24 my-auto mx-auto"
                :src="day.outfit"
                :alt="`${day.month}の${day.day}日のコーディネート`"
            />
        </a>
    </div>
</template>
