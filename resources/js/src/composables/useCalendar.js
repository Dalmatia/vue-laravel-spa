import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import ja from 'dayjs/locale/ja';

export function useCalendar(outfitImgMap) {
    const currentDate = ref(dayjs().locale(ja));
    const viewMode = ref('month');
    const selectedDay = ref(null);

    // 月の開始日と終了日を取得
    const getCalendarBounds = (date) => {
        const start = dayjs(date).startOf('month').startOf('week');
        const end = dayjs(date).endOf('month').endOf('week');
        return { start, end };
    };

    // 月表示用カレンダー
    const calendars = computed(() => {
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
    });

    // 週表示
    const currentWeek = computed(() => {
        const start = dayjs(currentDate.value).startOf('week');
        const end = dayjs(currentDate.value).endOf('week');
        let days = [];
        let day = start;

        while (day.isBefore(end) || day.isSame(end)) {
            const fullDate = day.format('YYYY-MM-DD');
            days.push({
                day: day.get('date'),
                month: day.format('YYYY-MM'),
                fullDate,
                outfit: outfitImgMap.value.get(fullDate) || '',
            });
            day = day.add(1, 'day');
        }
        return days;
    });

    // 選択している週のインデックス
    const currentWeekIndex = computed(() => {
        return currentWeek.value.findIndex(
            (d) => d.fullDate === selectedDay.value
        );
    });

    // 前後移動
    const prevDay = () => {
        if (currentWeekIndex.value > 0) {
            selectedDay.value =
                currentWeek.value[currentWeekIndex.value - 1].fullDate;
        }
    };
    const nextDay = () => {
        if (currentWeekIndex.value < currentWeek.value.length - 1) {
            selectedDay.value =
                currentWeek.value[currentWeekIndex.value + 1].fullDate;
        }
    };

    const prevMonth = () => {
        currentDate.value = currentDate.value.subtract(1, 'month');
    };
    const nextMonth = () => {
        currentDate.value = currentDate.value.add(1, 'month');
    };

    const prevWeek = () => {
        currentDate.value = currentDate.value.subtract(1, 'week');
    };
    const nextWeek = () => {
        currentDate.value = currentDate.value.add(1, 'week');
    };

    // 表示系
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

    const selectDay = (date) => {
        currentDate.value = dayjs(date);
        viewMode.value = 'week';
        selectedDay.value = date;
    };

    return {
        currentDate,
        viewMode,
        selectedDay,
        calendars,
        currentWeek,
        currentWeekIndex,
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
    };
}
