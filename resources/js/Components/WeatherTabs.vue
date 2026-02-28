<!-- src/components/WeatherTabs.vue -->
<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    selectedTab: { type: String, required: true },
    formattedWeather: { type: Object, required: true },
});

const emit = defineEmits(['change-tab']);

const tabs = [
    { key: 'today', label: '今日' },
    { key: 'tomorrow', label: '明日' },
];
</script>

<template>
    <div class="flex border-b mb-4 justify-between">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            class="px-4 py-2 text-base font-medium mx-auto transition-colors duration-150"
            :class="
                selectedTab === tab.key
                    ? 'border-b-2 border-blue-500 text-blue-500'
                    : 'text-gray-500 hover:text-blue-400'
            "
            @click="emit('change-tab', tab.key)"
        >
            {{ tab.label }}
            <span v-if="formattedWeather[tab.key]" class="ml-1">
                {{ formattedWeather[tab.key].displayDate }}
            </span>
        </button>
    </div>
</template>
