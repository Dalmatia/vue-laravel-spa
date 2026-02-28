<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    weather: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <div v-if="weather" class="border p-4 rounded-lg shadow-sm bg-white">
        <div class="flex justify-center items-center">
            <div class="text-6xl mr-4">{{ weather.weather_icon }}</div>
            <div>
                <p class="font-bold">{{ weather.description }}</p>
                <p>
                    <span class="text-red-500 font-bold"
                        >{{ weather.max_temp }}°C</span
                    >
                    /
                    <span class="text-blue-500 font-bold"
                        >{{ weather.min_temp }}°C</span
                    >
                </p>
                <p class="text-sm font-semibold">
                    降水確率: {{ weather.precipitation_probability }}%
                </p>
            </div>
        </div>
    </div>

    <div v-else class="text-center text-gray-400 py-8">
        天気情報を読み込めませんでした
    </div>
</template>
