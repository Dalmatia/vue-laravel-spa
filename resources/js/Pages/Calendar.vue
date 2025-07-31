<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import dayjs from 'dayjs';
import ja from 'dayjs/locale/ja';

import { useAuthStore } from '../stores/auth';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import YearMonthPicker from './YearMonthPicker.vue';

import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';

let currentDate = ref(dayjs().locale(ja));
let currentOutfit = ref(null);
let openOverlay = ref(false);
const authStore = useAuthStore();
const outfits = ref([]);
const outfitImgMap = ref(new Map());
const showMonthPicker = ref(false);
const currentYear = new Date().getFullYear();

// ユーザーが投稿したコーディネートの取得
const fetchOutfits = async () => {
    try {
        await authStore.fetchUserData();
        const response = await axios.get(`/api/users/${authStore.user.id}`);
        outfits.value = response.data.outfits;
        outfitImgMap.value = new Map(
            outfits.value.map((o) => [o.outfit_date, o.file])
        );
    } catch (error) {
        console.error(error);
    }
};

// コーディネートの詳細ページのオーバーレイを開く関数
const openOutfitOverlay = (date) => {
    const outfit = outfits.value.find((outfit) => outfit.outfit_date === date);
    currentOutfit.value = outfit;
    openOverlay.value = true;
};

// コーディネートの削除
const deleteOutfit = (object) => {
    let url = '';
    if (object.deleteType === 'Outfit') {
        url = `/api/outfit/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                openOverlay.value = false;
                fetchOutfits();
                window.dispatchEvent(new Event('outfit-deleted'));
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

// 月の開始日と終了日を取得する関数
const getCalendarBounds = (date) => {
    const start = dayjs(date).startOf('month').startOf('week');
    const end = dayjs(date).endOf('month').endOf('week');
    return { start, end };
};

// カレンダーの生成
const getCalendar = () => {
    const { start, end } = getCalendarBounds(currentDate.value);
    const totalDays = end.diff(start, 'days') + 1;
    const weekNumber = Math.ceil(totalDays / 7);

    let calendars = [];
    let calendarDate = start;
    for (let week = 0; week < weekNumber; week++) {
        let weekRow = [];
        for (let day = 0; day < 7; day++) {
            const fullDate = calendarDate.format('YYYY-MM-DD');
            weekRow.push({
                day: calendarDate.get('date'),
                month: calendarDate.format('YYYY-MM'),
                fullDate,
                outfit: outfitImgMap.value.get(fullDate) || '',
            });
            calendarDate = calendarDate.add(1, 'days');
        }
        calendars.push(weekRow);
    }
    return calendars;
};

// 次の月に移動する関数
const nextMonth = () => {
    currentDate.value = dayjs(currentDate.value).add(1, 'month');
};

// 前の月に移動する関数
const prevMonth = () => {
    currentDate.value = dayjs(currentDate.value).subtract(1, 'month');
};

// カレンダーを計算する計算プロパティ
const calendars = computed(() => {
    return getCalendar();
});

// 表示する日付を計算する計算プロパティ
const displayDate = computed(() => {
    return currentDate.value.format('YYYY[年]M[月]');
});

// 曜日を取得する関数
const dayOfWeek = (dayIndex) => {
    const week = ['日', '月', '火', '水', '木', '金', '土'];
    return week[dayIndex];
};

// 現在の月を計算する計算プロパティ
const currentMonth = computed(() => {
    return currentDate.value.format('YYYY-MM');
});

onMounted(() => {
    fetchOutfits();
    window.addEventListener('outfit-created', fetchOutfits);
    window.addEventListener('outfit-updated', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', fetchOutfits);
    window.removeEventListener('outfit-updated', fetchOutfits);
});
</script>

<template>
    <div
        id="content"
        class="flex justify-center items-start min-h-screen pb-20"
    >
        <div
            class="max-w-6xl lg:mx-auto md:ml-20 w-[100vw] md:w-[84.5vw] xl:w-[70vw]"
        >
            <div class="flex flex-wrap justify-between p-3">
                <h2
                    class="text-xl cursor-pointer flex items-center"
                    @click="showMonthPicker = !showMonthPicker"
                >
                    {{ displayDate }}
                    <ChevronDown />
                </h2>
                <div class="flex justify-end">
                    <button
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-4 rounded-l"
                        @click="prevMonth"
                    >
                        前の月
                    </button>
                    <button
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-4 rounded-r"
                        @click="nextMonth"
                    >
                        次の月
                    </button>
                </div>
            </div>

            <div
                class="flex border-t-[1px] border-l-[1px] border-solid border-gray-300"
            >
                <div
                    class="flex-1 border-r-[1px] border-solid border-gray-300 text-center"
                    v-for="n in 7"
                    :key="n"
                >
                    {{ dayOfWeek(n - 1) }}
                </div>
            </div>

            <div
                class="flex border-l-[1px] border-solid border-gray-300 flex-grow"
                v-for="(week, index) in calendars"
                :key="index"
            >
                <div
                    class="flex-1 min-h-[125px] border-r-[1px] border-b-[1px] border-solid border-gray-300 text-center"
                    :class="{ 'bg-gray-200': currentMonth != day.month }"
                    v-for="(day, index) in week"
                    :key="index"
                >
                    {{ day.day }}
                    <a
                        v-if="day.outfit"
                        @click="openOutfitOverlay(day.fullDate)"
                        role="button"
                        tabindex="0"
                    >
                        <img
                            class="w-20 h-24 my-auto mx-auto md:w-20 md:h-24 lg:w-20 lg:h-24"
                            :src="day.outfit"
                            :alt="`${day.month}の${day.day}日のコーディネート`"
                        />
                    </a>
                </div>
            </div>
        </div>
        <div class="pb-20 md:pb-5"></div>
    </div>
    <!-- コーディネートの詳細ページ -->
    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
    <!-- 年月ピッカー -->
    <YearMonthPicker
        v-if="showMonthPicker"
        :currentSelect="currentDate.toDate()"
        :maxYear="currentYear"
        @update:currentSelect="(val) => (currentDate = dayjs(val))"
        @close="showMonthPicker = false"
    />
</template>
