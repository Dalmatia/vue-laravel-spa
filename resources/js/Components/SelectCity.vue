<script setup>
import { onMounted } from 'vue';
import { useLocationSelector } from '../src/composables/locationSelector';

const emit = defineEmits(['close', 'city-saved']);
const {
    regions,
    prefectures,
    cities,
    selectedRegion,
    selectedPrefecture,
    selectedCity,
    isLoadingRegions,
    isLoadingPrefectures,
    isLoadingCities,
    isLoadingSavedData,
    errorMessage,
    isFormValid,
    fetchRegions,
    fetchSavedLocation,
    saveSelectedLocation,
} = useLocationSelector(emit);

onMounted(async () => {
    await fetchRegions();
    await fetchSavedLocation();
});
</script>

<template>
    <div
        class="fixed inset-0 bg-gray-700 bg-opacity-50 flex justify-center items-center z-50"
        @click.self="$emit('close')"
    >
        <div
            class="bg-white rounded-lg shadow-lg p-4 w-3/4 max-w-lg max-h-screen"
        >
            <h2 class="font-bold text-lg mb-4 text-center">地域設定</h2>
            <p v-if="errorMessage" class="text-red-600 text-sm mb-2">
                {{ errorMessage }}
            </p>
            <!-- 地域選択 -->
            <div>
                <label for="region" class="block font-medium mb-1">
                    地域を選択
                </label>
                <select
                    id="region"
                    autocomplete="region"
                    v-model="selectedRegion.id"
                    class="w-full border p-2 font-medium"
                    :disabled="isLoadingRegions"
                >
                    <option :value="null">地域を選択</option>
                    <option
                        v-for="region in regions"
                        :key="region.id"
                        :value="region.id"
                    >
                        {{ region.name }}
                    </option>
                </select>
                <div
                    v-if="isLoadingRegions"
                    class="absolute top-0 right-0 p-2 text-sm text-gray-500"
                >
                    地域データを読み込んでいます...
                </div>
            </div>

            <!-- 都道府県選択 -->
            <div v-if="selectedRegion" class="mt-4">
                <label for="prefecture" class="block font-medium mb-1">
                    都道府県を選択
                </label>
                <select
                    id="prefecture"
                    v-model="selectedPrefecture.id"
                    class="w-full border p-2 font-medium"
                    :disabled="isLoadingPrefectures || !prefectures.length"
                >
                    <option :value="null">都道府県を選択</option>
                    <option
                        v-for="prefecture in prefectures"
                        :key="prefecture.id"
                        :value="prefecture.id"
                    >
                        {{ prefecture.name }}
                    </option>
                </select>
                <div
                    v-if="isLoadingPrefectures"
                    class="absolute top-0 right-0 p-2 text-sm text-gray-500"
                >
                    都道府県データを読み込んでいます...
                </div>
            </div>

            <!-- 市区町村選択 -->
            <div v-if="selectedPrefecture" class="mt-4">
                <label for="city" class="block font-medium mb-1">
                    市区町村を選択
                </label>
                <select
                    id="city"
                    v-model="selectedCity.id"
                    class="w-full border p-2 font-medium"
                    :disabled="isLoadingCities || !cities.length"
                >
                    <option :value="null">市区町村を選択</option>
                    <option
                        v-for="city in cities"
                        :key="city.id"
                        :value="city.id"
                    >
                        {{ city.name }}
                    </option>
                </select>
                <div
                    v-if="isLoadingCities"
                    class="absolute top-0 right-0 p-2 text-sm text-gray-500"
                >
                    市区町村データを読み込んでいます...
                </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between mt-4">
                <button
                    :class="[
                        'bg-blue-500 text-white px-4 py-2 rounded-md',
                        isFormValid ? '' : 'opacity-50 cursor-not-allowed',
                    ]"
                    :disabled="!isFormValid"
                    @click="saveSelectedLocation"
                >
                    保存
                </button>
                <button
                    class="bg-gray-500 text-white px-4 py-2 rounded-md"
                    @click="emit('close')"
                >
                    閉じる
                </button>
            </div>
        </div>
    </div>
</template>
