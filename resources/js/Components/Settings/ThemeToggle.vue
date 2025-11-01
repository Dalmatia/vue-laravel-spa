<script setup>
import { onMounted, watch } from 'vue';
import { useThemeStore } from '../../stores/theme';

import Light from 'vue-material-design-icons/WeatherSunny.vue';
import Dark from 'vue-material-design-icons/WeatherNight.vue';

const themeStore = useThemeStore();

watch(
    () => themeStore.isDark,
    () => {
        themeStore.applyTheme();
    }
);

onMounted(() => {
    themeStore.initTheme();
});
</script>

<template>
    <section class="border rounded-lg p-4 bg-white shadow-sm">
        <h2 class="text-lg font-semibold mb-3">テーマ</h2>
        <div class="flex items-center space-x-3">
            <!-- トグル全体 -->
            <button
                @click="themeStore.isDark = !themeStore.isDark"
                class="relative w-16 h-8 rounded-full transition-colors duration-300 focus:outline-none"
                :class="themeStore.isDark ? 'bg-gray-600' : 'bg-yellow-400'"
            >
                <!-- スライド丸 -->
                <div
                    class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-md transition-all duration-300"
                    :class="
                        themeStore.isDark ? 'translate-x-8' : 'translate-x-0'
                    "
                ></div>

                <!-- アイコン -->
                <Light
                    class="absolute top-1 left-1 text-yellow-500"
                    v-if="!themeStore.isDark"
                />

                <Dark
                    class="absolute top-1 right-1 text-gray-200"
                    v-if="themeStore.isDark"
                />
            </button>

            <span class="text-gray-700 dark:text-gray-200">
                ダークモードを有効にする
            </span>
        </div>
    </section>
</template>
