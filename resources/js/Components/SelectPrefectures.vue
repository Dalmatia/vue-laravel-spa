<script setup>
import { ref, computed } from 'vue';

const emit = defineEmits(['close', 'city-saved']);

// 地域データ
const regions = ref([
    {
        name: '北海道/東北',
        areas: [
            {
                name: '北海道',
                cities: ['旭川市', '釧路市', '札幌市', '函館市'],
            },
            {
                name: '青森県',
                cities: ['青森市', '八戸市'],
            },
            {
                name: '岩手県',
                cities: ['宮古市', '盛岡市'],
            },
            {
                name: '宮城県',
                cities: ['白石市', '仙台市青葉区'],
            },
            {
                name: '秋田県',
                cities: ['秋田市', '横手市'],
            },
            {
                name: '山形県',
                cities: ['酒田市', '山形市'],
            },
            {
                name: '福島県',
                cities: ['いわき市', '福島市'],
            },
        ],
    },
    {
        name: '関東',
        areas: [
            {
                name: '茨城県',
                cities: ['土浦市', '水戸市'],
            },
            {
                name: '栃木県',
                cities: ['宇都宮市', '大田原市'],
            },
            {
                name: '群馬県',
                cities: ['前橋市', 'みなかみ町'],
            },
            {
                name: '埼玉県',
                cities: ['熊谷市', 'さいたま市', '秩父市'],
            },
            {
                name: '千葉県',
                cities: ['　館山市', '千葉市'],
            },
            {
                name: '東京都',
                cities: ['大島区', '千代田区', '渋谷区', '八王子市'],
            },
            {
                name: '神奈川県',
                cities: ['横浜市', '川崎市'],
            },
        ],
    },
    {
        name: '中部',
        areas: [
            {
                name: '新潟県',
                cities: ['新潟市', '長岡市'],
            },
            {
                name: '富山県',
                cities: ['富山市', '高岡市'],
            },
            {
                name: '石川県',
                cities: ['金沢市', '輪島市'],
            },
            {
                name: '福井県',
                cities: ['福井市', '敦賀市'],
            },
            {
                name: '山梨県',
                cities: ['甲府市', '山梨市'],
            },
            {
                name: '長野県',
                cities: ['松本市', '長野市'],
            },
            {
                name: '岐阜県',
                cities: ['岐阜市', '大垣市'],
            },
            {
                name: '静岡県',
                cities: ['静岡市', '浜松市'],
            },
            {
                name: '愛知県',
                cities: ['名古屋市', '豊橋市'],
            },
        ],
    },
    {
        name: '近畿',
        areas: [
            {
                name: '三重県',
                cities: ['津市', '四日市市'],
            },
            {
                name: '滋賀県',
                cities: ['大津市', '彦根市'],
            },
            {
                name: '京都府',
                cities: ['京都市', '舞鶴市'],
            },
            {
                name: '大阪府',
                cities: ['大阪市', '堺市'],
            },
            {
                name: '兵庫県',
                cities: ['神戸市', '姫路市'],
            },
            {
                name: '奈良県',
                cities: ['奈良市', '大和高田市'],
            },
            {
                name: '和歌山県',
                cities: ['串本市', '和歌山市'],
            },
        ],
    },
    {
        name: '中国',
        areas: [
            {
                name: '鳥取県',
                cities: ['鳥取市', '米子市'],
            },
            {
                name: '島根県',
                cities: ['松江市', '浜田市'],
            },
            {
                name: '岡山県',
                cities: ['岡山市', '倉敷市'],
            },
            {
                name: '広島県',
                cities: ['広島市', '福山市'],
            },
            {
                name: '山口県',
                cities: ['下関市', '山口市'],
            },
        ],
    },
    {
        name: '四国',
        areas: [
            {
                name: '徳島県',
                cities: ['徳島市', '鳴門市'],
            },
            {
                name: '香川県',
                cities: ['高松市', '丸亀市'],
            },
            {
                name: '愛媛県',
                cities: ['松山市', '新居浜市'],
            },
            {
                name: '高知県',
                cities: ['高知市', '室戸市'],
            },
        ],
    },
    {
        name: '九州/沖縄',
        areas: [
            {
                name: '福岡県',
                cities: ['福岡市', '北九州市'],
            },
            {
                name: '佐賀県',
                cities: ['佐賀市', '唐津市'],
            },
            {
                name: '長崎県',
                cities: ['長崎市', '佐世保市'],
            },
            {
                name: '熊本県',
                cities: ['熊本市', '八代市'],
            },
            {
                name: '大分県',
                cities: ['大分市', '別府市'],
            },
            {
                name: '宮崎県',
                cities: ['宮崎市', '延岡市'],
            },
            {
                name: '鹿児島県',
                cities: ['鹿児島市', '奄美市'],
            },
            {
                name: '沖縄県',
                cities: ['那覇市', '石垣市'],
            },
        ],
    },
]);

// 選択された値
const selectedRegion = ref('');
const selectedArea = ref('');
const selectedCity = ref('');

// フィルタリングされたエリアと市
const filteredAreas = computed(() => {
    const region = regions.value.find((r) => r.name === selectedRegion.value);
    return region ? region.areas : [];
});

const filteredCities = computed(() => {
    const area = filteredAreas.value.find((a) => a.name === selectedArea.value);
    return area ? area.cities : [];
});

const updateAreas = () => {
    selectedArea.value = '';
    selectedCity.value = '';
};

const updateCities = () => {
    selectedCity.value = '';
};

const saveCity = () => {
    emit('city-saved', {
        city: selectedCity.value,
    });
};
</script>

<template>
    <div
        class="fixed inset-0 bg-gray-700 bg-opacity-50 flex justify-center items-center z-50"
    >
        <div
            class="bg-white rounded-lg shadow-lg p-4 w-3/4 max-w-lg max-h-screen"
        >
            <h2 class="font-bold text-lg mb-4 text-center">地域を選択</h2>
            <!-- エリア選択 -->
            <select
                v-model="selectedRegion"
                @change="updateAreas"
                class="w-full border p-2 font-medium"
                aria-label="地域を選択"
            >
                <option disabled value="">地域を選択</option>
                <option
                    v-for="region in regions"
                    :key="region.name"
                    :value="region.name"
                >
                    {{ region.name }}
                </option>
            </select>

            <!-- 都道府県選択 -->
            <div v-if="selectedRegion" class="mt-4">
                <select
                    v-model="selectedArea"
                    @change="updateCities"
                    class="w-full border p-2 font-medium"
                    aria-label="都道府県を選択"
                >
                    <option disabled value="">都道府県を選択</option>
                    <option
                        v-for="area in filteredAreas"
                        :key="area.name"
                        :value="area.name"
                    >
                        {{ area.name }}
                    </option>
                </select>
            </div>

            <!-- 市区町村選択 -->
            <div v-if="selectedArea" class="mt-4">
                <select
                    v-model="selectedCity"
                    class="w-full border p-2 font-medium"
                    aria-label="市区町村を選択"
                >
                    <option disabled value="">市区町村を選択</option>
                    <option
                        v-for="city in filteredCities"
                        :key="city"
                        :value="city"
                    >
                        {{ city }}
                    </option>
                </select>
            </div>

            <div class="flex justify-between mt-4">
                <button
                    v-if="selectedCity"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md"
                    @click="saveCity()"
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
