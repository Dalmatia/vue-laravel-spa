<script setup>
import { onMounted, ref, watch } from 'vue';

const emit = defineEmits(['close', 'city-saved']);

// データ状態
const regions = ref([]);
const prefectures = ref([]);
const cities = ref([]);

// 選択された値(初期値を設定)
const selectedRegion = ref({
    id: null,
    name: '地域を選択',
});
const selectedPref = ref({
    id: null,
    name: '都道府県を選択',
});
const selectedCity = ref({
    id: null,
    name: '市区町村を選択',
    latitude: null,
    longitude: null,
});

// ロード状態
const isLoadingRegions = ref(false);
const isLoadingPrefs = ref(false);
const isLoadingCities = ref(false);
const isLoadingSavedData = ref(false);

// 地域データを取得
const fetchRegions = async () => {
    isLoadingRegions.value = true;
    try {
        const response = await axios.get('/api/regions');
        regions.value = (response.data.regions || []).map((region) => ({
            id: region.id,
            name: region.name,
        }));
    } catch (error) {
        console.error('地域データの取得に失敗しました:', error);
    } finally {
        isLoadingRegions.value = false;
    }
};

// 都道府県データを取得
const fetchPrefs = async () => {
    if (!selectedRegion.value.id) return;

    isLoadingPrefs.value = true;
    try {
        const response = await axios.get(
            `/api/region/${selectedRegion.value.id}/prefectures`,
            {
                params: { region: selectedRegion.value.id },
            }
        );
        prefectures.value = (response.data.prefectures || []).map((pref) => ({
            id: pref.id,
            name: pref.name,
        }));
    } catch (error) {
        console.error('都道府県データの取得に失敗しました:', error);
    } finally {
        isLoadingPrefs.value = false;
    }
};

// 市区町村データを取得
const fetchCities = async () => {
    if (!selectedPref.value.id) return;

    isLoadingCities.value = true;
    try {
        const response = await axios.get(
            `/api/prefecture/${selectedPref.value.id}/cities`,
            {
                params: {
                    region: selectedRegion.value.id,
                    prefecture: selectedPref.value.id,
                },
            }
        );
        cities.value = response.data.cities.map((city) => ({
            id: city.id,
            name: city.name,
            latitude: city.lat,
            longitude: city.lon,
        }));
    } catch (error) {
        console.error('市区町村データの取得に失敗しました:', error);
    } finally {
        isLoadingCities.value = false;
    }
};

// 依存関係の監視
watch(
    () => selectedRegion.value.id,
    async (newRegion, oldRegion) => {
        if (newRegion === oldRegion) return;
        selectedPref.value = { id: null, name: '都道府県を選択' };
        selectedCity.value = { id: null, name: '市区町村を選択' };
        prefectures.value = [];
        cities.value = [];
        await fetchPrefs();
    }
);

watch(
    () => selectedPref.value.id,
    async (newPref, oldPref) => {
        if (newPref === oldPref) return;
        selectedCity.value = { id: null, name: '市区町村を選択' };
        cities.value = [];
        await fetchCities();
    }
);

watch(
    () => selectedCity.value.id,
    async (newCity, oldCity) => {
        if (newCity === oldCity) return;
        const city = cities.value.find((c) => c.id === newCity);
        if (city) {
            selectedCity.value.name = city.name;
            selectedCity.value.latitude = city.latitude;
            selectedCity.value.longitude = city.longitude;
        }
    }
);

//選択した市区町村を保存
const saveCity = async () => {
    if (!selectedCity.value.id) return;
    try {
        await axios.post('/api/saveCity', {
            region_id: selectedRegion.value.id,
            prefecture_id: selectedPref.value.id,
            city_id: selectedCity.value.id,
        });

        emit('city-saved', {
            region: selectedRegion.value.id,
            prefecture: selectedPref.value.id,
            city: selectedCity.value,
        });
    } catch (error) {
        console.error('地域情報の保存に失敗しました:', error);
    }
};

const fetchSavedCity = async () => {
    isLoadingSavedData.value = true;
    try {
        const response = await axios.get('/api/getSavedCity'); // 保存データを取得
        if (response.data) {
            selectedRegion.value.id = response.data.region_id;
            await fetchPrefs();
            selectedPref.value = { id: response.data.prefecture_id, name: '' };
            await fetchCities();
            selectedCity.value = { id: response.data.city_id, name: '' };

            const region = regions.value.find(
                (r) => r.id === response.data.region_id
            );
            const prefecture = prefectures.value.find(
                (p) => p.id === response.data.prefecture_id
            );
            const city = cities.value.find(
                (c) => c.id === response.data.city_id
            );

            selectedRegion.value.name = region ? region.name : '';
            selectedPref.value.name = prefecture ? prefecture.name : '';
            selectedCity.value.name = city ? city.name : '';
        }
    } catch (error) {
        console.error('保存済みデータの取得に失敗しました:', error);
    } finally {
        isLoadingSavedData.value = false;
    }
};

onMounted(async () => {
    await fetchRegions();
    await fetchSavedCity();
});
</script>

<template>
    <div
        class="fixed inset-0 bg-gray-700 bg-opacity-50 flex justify-center items-center z-50"
    >
        <div
            class="bg-white rounded-lg shadow-lg p-4 w-3/4 max-w-lg max-h-screen"
        >
            <h2 class="font-bold text-lg mb-4 text-center">地域設定</h2>

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
                <div v-if="isLoadingRegions" class="text-gray-500 text-sm mt-1">
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
                    v-model="selectedPref.id"
                    class="w-full border p-2 font-medium"
                    :disabled="isLoadingPrefs || !prefectures.length"
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
                <div v-if="isLoadingPrefs" class="text-gray-500 text-sm mt-1">
                    都道府県データを読み込んでいます...
                </div>
            </div>

            <!-- 市区町村選択 -->
            <div v-if="selectedPref" class="mt-4">
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
                <div v-if="isLoadingCities" class="text-gray-500 text-sm mt-1">
                    市区町村データを読み込んでいます...
                </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between mt-4">
                <button
                    v-if="selectedCity.id"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md"
                    @click="saveCity"
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
