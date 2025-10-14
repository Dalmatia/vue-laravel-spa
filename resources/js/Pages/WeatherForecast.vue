<script setup>
import { onMounted, ref, watch } from 'vue';
import { useWeatherForecast } from '../src/composables/useWeatherForecast';
import { useClothingAdvice } from '../src/composables/useClothingAdvice';
import SelectCity from '../Components/SelectCity.vue';
import WeatherTabs from '../Components/WeatherTabs.vue';
import WeatherCard from '../Components/WeatherCard.vue';
import AdviceSection from '../Components/AdviceSection.vue';

const {
    selectedCity,
    weather,
    isLoading,
    error,
    formattedWeather,
    fetchSavedCity,
    fetchWeather,
} = useWeatherForecast();

const {
    selectedTpo,
    selectedTab,
    advice,
    isAdviceLoading,
    fetchClothingAdvice,
} = useClothingAdvice(selectedCity, weather);

const showModal = ref(false);

const handleCitySaved = (city) => {
    selectedCity.value = city;
    fetchWeather();
    showModal.value = false;
};

onMounted(async () => {
    await fetchSavedCity();
    await fetchWeather();
    await fetchClothingAdvice();
});
</script>

<template>
    <div class="mb-6">
        <!-- ヘッダー -->
        <div class="flex items-center justify-between">
            <div class="text-center flex-1">
                <h2 class="font-bold text-lg">コーディネート予報</h2>
                <p v-if="selectedCity" class="text-gray-500 text-md mt-1">
                    {{ selectedCity.name }}
                </p>
            </div>
            <button
                @click="showModal = true"
                :disabled="isLoading"
                class="text-blue-500 self-start px-2 py-1 transition"
                :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
            >
                地域設定
            </button>
        </div>

        <SelectCity
            v-if="showModal"
            @city-saved="handleCitySaved($event)"
            @close="showModal = false"
        />

        <div
            v-if="isLoading"
            class="flex items-center justify-center border py-14"
        >
            <span class="ml-2 text-gray-500">天気情報を取得中...</span>
        </div>

        <h2 v-else-if="weather?.error" class="text-center text-red-500 py-14">
            {{ weather.message }}
        </h2>

        <div v-else>
            <!-- 日付タブ -->
            <WeatherTabs
                :selected-tab="selectedTab"
                :formatted-weather="formattedWeather"
                @change-tab="(tab) => (selectedTab = tab)"
            />

            <!-- 天気データ -->
            <WeatherCard :weather="weather[selectedTab]" />

            <!-- AI服装アドバイス -->
            <AdviceSection
                :selected-tpo="selectedTpo"
                :advice="advice"
                :is-advice-loading="isAdviceLoading"
                @change-tpo="(tpo) => (selectedTpo = tpo)"
            />
        </div>
    </div>
</template>
