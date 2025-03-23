<script setup>
import { onMounted, ref } from 'vue';

import SelectCity from './SelectCity.vue';

const showModal = ref(false);
const selectedCity = ref({
    region_id: null,
    prefecture_id: null,
    city_id: null,
    name: '',
    latitude: null,
    longitude: null,
});
const selectedTab = ref('today');
const isLoading = ref(true);
const weather = ref(null);

const fetchSavedCity = async () => {
    try {
        const response = await axios.get('/api/getSavedCity');
        if (response.data) {
            selectedCity.value = {
                region_id: response.data.region_id,
                prefecture_id: response.data.prefecture_id,
                city_id: response.data.city_id,
                name: response.data.city_name,
                latitude: response.data.latitude,
                longitude: response.data.longitude,
            };
        }
    } catch (error) {
        console.error('保存された市区町村の取得に失敗しました:', error);
    }
};

// 選択された地域データを処理する関数
const handleCitySaved = (event) => {
    selectedCity.value = {
        region_id: event.region_id,
        prefecture_id: event.prefecture_id,
        city_id: event.city_id,
        name: event.city.name,
        latitude: event.city.latitude,
        longitude: event.city.longitude,
    };
    fetchWeather();
    showModal.value = false; // モーダルを閉じる
};

const fetchWeather = async () => {
    isLoading.value = true;
    try {
        const weatherResponse = await axios.get(`/api/weather`, {
            params: {
                latitude: selectedCity.value.latitude,
                longitude: selectedCity.value.longitude,
            },
        });

        weather.value = weatherResponse.data.weather;
    } catch (error) {
        console.error('天気情報の取得に失敗しました。', error);
        weather.value = {
            error: true,
            message:
                error.response?.data?.message ||
                '天気情報を取得できませんでした。',
        };
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await fetchSavedCity();
    await fetchWeather();
});
</script>

<template>
    <div class="mb-6">
        <!-- コーデ予報セクション -->
        <div class="flex items-center justify-between">
            <div class="text-center flex-1">
                <h2 class="font-bold text-lg">コーディネート予報</h2>
                <p v-if="selectedCity" class="text-gray-500 text-md mt-1">
                    {{ selectedCity.name }}
                </p>
            </div>
            <button class="text-blue-500 self-start" @click="showModal = true">
                地域設定
            </button>
        </div>
        <div v-if="showModal">
            <SelectCity
                @city-saved="handleCitySaved"
                @close="showModal = false"
            />
        </div>
        <div
            v-if="isLoading"
            class="flex items-center justify-center border py-14"
        >
            <span aria-live="polite" role="status">
                <svg
                    class="animate-spin h-5 w-5 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8z"
                    ></path>
                </svg>
            </span>
            <span class="ml-2 text-gray-500">天気情報を取得中...</span>
        </div>

        <h2 v-else-if="weather.error" class="text-center text-red-500 py-14">
            {{ weather.message }}
        </h2>

        <div v-else>
            <!-- タブ切り替え -->
            <div class="flex border-b mb-4 justify-between">
                <button
                    v-for="tab in ['today', 'tomorrow']"
                    :key="tab"
                    :class="[
                        'px-4 py-2 text-base font-medium mx-auto',
                        selectedTab === tab
                            ? 'border-b-2 border-blue-500 text-blue-500'
                            : 'text-gray-500',
                    ]"
                    @click="selectedTab = tab"
                >
                    {{
                        tab === 'today'
                            ? `今日 ${weather.today.date}`
                            : `明日 ${weather.tomorrow.date}`
                    }}
                </button>
            </div>

            <!-- 今日の予報 -->
            <div v-if="selectedTab === 'today'" class="border p-4">
                <h2 class="font-bold text-base mb-2">今日の天気:</h2>
                <div class="flex justify-center">
                    <div class="text-7xl text-center mr-4">
                        {{ weather.today.weather_icon }}
                    </div>
                    <div class="flex flex-col">
                        <p class="text-sm font-bold">
                            {{ weather.today.description }}
                        </p>
                        <div class="flex">
                            <p class="text-lg font-semibold mr-2 text-red-500">
                                {{ weather.today.max_temp }}°C
                            </p>
                            |
                            <p class="text-lg font-semibold ml-2 text-blue-500">
                                {{ weather.today.min_temp }}°C
                            </p>
                        </div>
                        <span class="flex text-sm font-bold">
                            降水確率:
                            <p class="ml-4">
                                {{ weather.today.precipitation_probability }}%
                            </p>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 明日の予報 -->
            <div v-else class="border p-4">
                <h2 class="font-bold text-base mb-2">明日の天気:</h2>
                <div class="flex justify-center">
                    <div class="text-7xl text-center mr-4">
                        {{ weather.tomorrow.weather_icon }}
                    </div>
                    <div class="flex flex-col">
                        <p class="text-sm font-bold">
                            {{ weather.tomorrow.description }}
                        </p>
                        <div class="flex">
                            <p class="text-lg font-semibold mr-2 text-red-500">
                                {{ weather.tomorrow.max_temp }}°C
                            </p>
                            |
                            <p class="text-lg font-semibold ml-2 text-blue-500">
                                {{ weather.tomorrow.min_temp }}°C
                            </p>
                        </div>
                        <span class="flex text-sm font-bold">
                            降水確率:
                            <p class="ml-4">
                                {{
                                    weather.tomorrow.precipitation_probability
                                }}%
                            </p>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
