<script setup>
import { ref, onMounted, computed } from 'vue';
import dayjs from 'dayjs';
import ja from 'dayjs/locale/ja';

let currentDate = ref(dayjs().locale(ja));

const getStartDate = () => {
    let date = dayjs(currentDate.value);
    date = date.startOf('month');
    const youbiNum = date.day();
    return date.subtract(youbiNum, 'days');
};

const getEndDate = () => {
    let date = dayjs(currentDate.value);
    date = date.endOf('month');
    const youbiNum = date.day();
    return date.add(6 - youbiNum, 'days');
};

const getCalendar = () => {
    let startDate = getStartDate();
    const endDate = getEndDate();
    const weekNumber = Math.ceil(endDate.diff(startDate, 'days') / 7);

    let calendars = [];
    let calendarDate = getStartDate();
    for (let week = 0; week < weekNumber; week++) {
        let weekRow = [];
        for (let day = 0; day < 7; day++) {
            weekRow.push({
                day: calendarDate.get('date'),
                month: calendarDate.format('YYYY-MM'),
            });
            calendarDate = calendarDate.add(1, 'days');
        }
        calendars.push(weekRow);
    }
    return calendars;
};

const nextMonth = () => {
    currentDate.value = dayjs(currentDate.value).add(1, 'month');
};
const prevMonth = () => {
    currentDate.value = dayjs(currentDate.value).subtract(1, 'month');
};

// onMounted(() => {
//     console.log(getCalendar());
// });

const calendars = computed(() => {
    return getCalendar();
});

const displayDate = computed(() => {
    return currentDate.value.format('YYYY[年]M[月]');
});

const dayOfWeek = (dayIndex) => {
    const week = ['日', '月', '火', '水', '木', '金', '土'];
    return week[dayIndex];
};

const currentMonth = computed(() => {
    return currentDate.value.format('YYYY-MM');
});

// const getImagePath = (day) => {
//     // 日付に基づいて画像のパスを生成するロジックをここに追加
//     // 例: return `/images/${day}.jpg`;
//     return '';
// };
</script>

<template>
    <div id="content">
        <div
            class="max-w-[900px] lg:ml-0 lg:pl-0 md:ml-[30px] md:pl-[74px] px-4"
        >
            <div class="flex flex-wrap justify-between p-3">
                <h2 class="text-xl">
                    {{ displayDate }}
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
                class="flex border-l-[1px] border-solid border-gray-300"
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
                    <a href="/">
                        <img
                            class="block h-[85%] w-auto md:h-auto md:w-full"
                            src="https://picsum.photos/id/600/480/?random"
                        />
                    </a>
                    <!-- <img :src="getImagePath(day.day)" alt="日付の画像" /> -->
                </div>
            </div>
        </div>
        <div class="pb-20 md:pb-5"></div>
    </div>
</template>

<style>
.outside {
    background-color: #f7f7f7;
}
</style>
