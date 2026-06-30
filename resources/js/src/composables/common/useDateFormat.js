import dayjs from 'dayjs';

export function useDateFormat() {
    const formatDate = (date) => {
        if (!date) return '';

        return dayjs(date).format('YYYY.M.D');
    };

    return {
        formatDate,
    };
}
