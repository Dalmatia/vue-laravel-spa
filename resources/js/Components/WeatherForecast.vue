<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { getEnumStore } from '../stores/enum';
import SelectCity from './SelectCity.vue';
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';

const enumStore = getEnumStore();
const showModal = ref(false);
const selectedCity = ref(null);
const selectedTab = ref('today');
const selectedTpo = ref('casual');
const isLoading = ref(true);
const isAdviceLoading = ref(false);
const weather = ref(null);
const advice = ref(null);
const cache = ref({});

const formatCity = (cityData) => ({
    region_id: cityData.region_id,
    prefecture_id: cityData.prefecture_id,
    city_id: cityData.city_id,
    name: cityData.city?.name ?? cityData.city_name,
    latitude: cityData.city?.latitude ?? cityData.latitude,
    longitude: cityData.city?.longitude ?? cityData.longitude,
});

const fetchSavedCity = async () => {
    try {
        const response = await axios.get('/api/get_saved_location');
        if (response.data) {
            selectedCity.value = formatCity(response.data);
        }
    } catch (error) {
        console.error('保存された市区町村の取得に失敗しました:', error);
    }
};

// 日付表示用のフォーマット
const formattedWeather = computed(() => {
    if (!weather.value) return {};
    const result = {};
    for (const label of ['today', 'tomorrow']) {
        const w = weather.value[label];
        if (!w) continue;
        // 表示用：n/j
        result[label] = {
            ...w,
            displayDate:
                new Date(w.date).getMonth() +
                1 +
                '/' +
                new Date(w.date).getDate(),
        };
    }
    return result;
});

// TPOを含む服装アドバイスの取得
const fetchClothingAdvice = async () => {
    if (!weather.value || !selectedTab.value) return;
    const date = weather.value[selectedTab.value]?.date;
    const tpo = selectedTpo.value;
    const cacheKey = `${selectedCity.value?.city_id}-${tpo}-${date}`;

    if (cache.value[cacheKey]) {
        advice.value = cache.value[cacheKey];
        return;
    }

    isAdviceLoading.value = true;
    advice.value = null;
    const w = weather.value[selectedTab.value];
    const weatherData = {
        max: w.max_temp,
        min: w.min_temp,
        pop: w.precipitation_probability,
        avgPop: w.precipitation_probability,
        humidityAvg: w.humidity ?? 60,
        windAvg: w.wind_speed ?? 2,
    };

    try {
        const { data } = await axios.post('/api/clothing_advice', {
            weatherData,
            tpo,
            targetDate: date,
        });
        advice.value = data;
        cache.value[cacheKey] = data;
    } catch (error) {
        console.error('服装アドバイス取得に失敗しました:', error);
        advice.value = {
            error: true,
            message:
                error.response?.data?.message ||
                '服装アドバイスを取得できませんでした。',
        };
    } finally {
        isAdviceLoading.value = false;
    }
};

const fetchWeather = async () => {
    if (!selectedCity.value) return;
    isLoading.value = true;
    try {
        const weatherResponse = await axios.get(`/api/weather`, {
            params: {
                latitude: selectedCity.value.latitude,
                longitude: selectedCity.value.longitude,
            },
        });
        weather.value = weatherResponse.data.weather;
        await fetchClothingAdvice(); // 天気取得後にアドバイス取得
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

// TPOまたは日付タブが切り替わったら再取得
watch([selectedTpo, selectedTab, weather], () => {
    fetchClothingAdvice();
});

const handleCitySaved = (event) => {
    selectedCity.value = formatCity(event);
    fetchWeather();
    showModal.value = false;
};

const currentWeather = computed(() => {
    if (!weather.value) return null;
    return weather.value[selectedTab.value];
});

onMounted(async () => {
    await fetchSavedCity();
    await fetchWeather();
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
            @city-saved="handleCitySaved"
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
            <div class="flex border-b mb-4 justify-between">
                <button
                    v-for="tab in ['today', 'tomorrow']"
                    :key="tab"
                    class="px-4 py-2 text-base font-medium mx-auto"
                    :class="
                        selectedTab === tab
                            ? 'border-b-2 border-blue-500 text-blue-500'
                            : 'text-gray-500'
                    "
                    @click="selectedTab = tab"
                >
                    {{
                        tab === 'today'
                            ? `今日 ${formattedWeather.today.displayDate}`
                            : `明日 ${formattedWeather.tomorrow.displayDate}`
                    }}
                </button>
            </div>

            <!-- 天気データ -->
            <div v-if="currentWeather" class="border p-4 rounded-lg">
                <div class="flex justify-center items-center">
                    <div class="text-6xl mr-4">
                        {{ currentWeather.weather_icon }}
                    </div>
                    <div>
                        <p class="font-bold">
                            {{ currentWeather.description }}
                        </p>
                        <p>
                            <span class="text-red-500 font-bold"
                                >{{ currentWeather.max_temp }}°C</span
                            >
                            /
                            <span class="text-blue-500 font-bold"
                                >{{ currentWeather.min_temp }}°C</span
                            >
                        </p>
                        <p class="text-sm font-semibold">
                            降水確率:
                            {{ currentWeather.precipitation_probability }}%
                        </p>
                    </div>
                </div>
            </div>

            <!-- AI服装アドバイス -->
            <div v-if="advice" class="mt-6 border rounded-lg p-4">
                <h3 class="text-lg font-bold mb-2">服装アドバイス:</h3>
                <!-- TPOタブ -->
                <div class="flex justify-center mb-4 space-x-2">
                    <div class="flex items-center flex-shrink-0 space-x-1">
                        <PaletteOutline class="text-gray-700" />
                        <span class="font-semibold text-gray-700">
                            シーン｜
                        </span>
                    </div>

                    <div class="flex overflow-x-auto space-x-2 scrollbar-hide">
                        <button
                            v-for="option in [
                                { key: 'casual', label: 'カジュアル' },
                                { key: 'date', label: 'デート' },
                                { key: 'office', label: 'オフィス' },
                                { key: 'outdoor', label: 'アウトドア' },
                            ]"
                            :key="option.key"
                            class="px-3 py-1 rounded-full text-sm font-semibold border whitespace-nowrap transition"
                            :class="
                                selectedTpo === option.key
                                    ? 'bg-blue-500 text-white border-blue-500'
                                    : 'text-gray-500 hover:border-gray-400'
                            "
                            @click="selectedTpo = option.key"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="isAdviceLoading"
                    class="flex justify-center items-center py-10"
                >
                    <svg
                        class="animate-spin h-6 w-6 text-blue-400 mr-2"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
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
                    <span class="text-gray-500">服装アドバイスを生成中...</span>
                </div>

                <p
                    v-else-if="advice?.error"
                    class="text-red-500 text-center py-10"
                >
                    {{ advice.message }}
                </p>

                <template v-else-if="advice">
                    <p class="text-sm text-gray-700 mb-4">
                        {{ advice.advice }}
                    </p>

                    <div
                        v-if="advice.outfit_suggestion"
                        class="grid grid-cols-2 sm:grid-cols-4 gap-4"
                    >
                        <div
                            v-for="(
                                part, mainCategory
                            ) in advice.outfit_suggestion"
                            :key="mainCategory"
                            class="flex flex-col items-center bg-white rounded-lg shadow p-2"
                        >
                            <span class="font-semibold">
                                {{
                                    enumStore.getMainCategoryName(mainCategory)
                                }}
                            </span>
                            <img
                                v-if="part?.item"
                                :src="part.item.file"
                                class="w-24 h-24 object-cover rounded mb-2"
                            />
                            <span v-if="part?.keyword" class="text-sm">{{
                                part.keyword
                            }}</span>
                            <div v-else class="text-red-500 text-sm">
                                未登録
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* 横スクロールバー非表示 */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
    scroll-behavior: smooth;
}
</style>
