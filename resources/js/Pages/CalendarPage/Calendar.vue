<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import dayjs from 'dayjs';

import { useAuthStore } from '../../stores/auth';
import { useCalendar } from '../../src/composables/useCalendar';
import CalendarHeader from './CalendarHeader.vue';
import CalendarDayCell from './CalendarDayCell.vue';
import OutfitPreview from './OutfitPreview.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import YearMonthPicker from './YearMonthPicker.vue';
import CreateOutfitOverlay from '@/Components/Outfit/Create/CreateOutfitOverlay.vue';

let currentOutfit = ref(null);
let openOverlay = ref(false);
const authStore = useAuthStore();
const outfits = ref([]);
const outfitImgMap = ref(new Map());
const showMonthPicker = ref(false);
const currentYear = new Date().getFullYear();
const openCreatePost = ref(false);
const {
    currentDate,
    viewMode,
    selectedDay,
    calendars,
    currentWeek,
    prevDay,
    nextDay,
    prevMonth,
    nextMonth,
    prevWeek,
    nextWeek,
    displayDate,
    dayOfWeek,
    currentMonth,
    selectDay,
} = useCalendar(outfitImgMap);

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
    currentDate.value = dayjs(date);
    viewMode.value = 'week';
    const outfit = outfits.value.find((outfit) => outfit.outfit_date === date);
    currentOutfit.value = outfit;
    openOverlay.value = true;
};

const selectedOutfit = computed(() => {
    if (!selectedDay.value) return null;
    return (
        outfits.value.find((o) => o.outfit_date === selectedDay.value) || null
    );
});

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
            <CalendarHeader
                :display-date="displayDate"
                :view-mode="viewMode"
                @toggle-month-picker="showMonthPicker = !showMonthPicker"
                @change-view="viewMode = $event"
                @prev-month="prevMonth"
                @next-month="nextMonth"
                @prev-week="prevWeek"
                @next-week="nextWeek"
            />

            <!-- 曜日ヘッダー -->
            <div
                class="flex border-t-[1px] border-l-[1px] border-solid border-gray-300"
            >
                <div
                    class="flex-1 border-r-[1px] border-solid border-gray-300 text-center"
                    v-for="n in 7"
                    :key="n"
                    :class="{
                        'text-red-500': n === 1, // 日曜
                        'text-blue-500': n === 7, // 土曜
                    }"
                >
                    {{ dayOfWeek(n - 1) }}
                </div>
            </div>

            <!-- 月表示 -->
            <div v-if="viewMode === 'month'">
                <div
                    class="flex border-l-[1px] border-solid border-gray-300 flex-grow"
                    v-for="(week, index) in calendars"
                    :key="'m-' + index"
                >
                    <CalendarDayCell
                        v-for="(day, index) in week"
                        :key="index"
                        :day="day"
                        :current-month="currentMonth"
                        :is-week-mode="false"
                        @select="selectDay"
                    />
                </div>
            </div>

            <!-- 週表示 -->
            <div v-else>
                <div
                    class="flex border-l-[1px] border-solid border-gray-300 flex-grow"
                >
                    <CalendarDayCell
                        v-for="(day, index) in currentWeek"
                        :key="'w-' + index"
                        :day="day"
                        :is-week-mode="true"
                        @select="selectDay"
                    />
                </div>

                <!-- コーディネートのプレビュー表示 -->
                <OutfitPreview
                    :selected-day="selectedDay"
                    :selected-outfit="selectedOutfit"
                    @prev="prevDay"
                    @next="nextDay"
                    @open-outfit="openOutfitOverlay"
                    @create-outfit="openCreatePost = true"
                />
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
    <!-- コーディネートの投稿オーバーレイ -->
    <CreateOutfitOverlay
        v-if="openCreatePost"
        @close="openCreatePost = false"
    />
    <!-- 年月ピッカー -->
    <YearMonthPicker
        v-if="showMonthPicker"
        :current-select="currentDate.toDate()"
        :max-year="currentYear"
        @update:currentSelect="(val) => (currentDate = dayjs(val))"
        @close="showMonthPicker = false"
    />
</template>
