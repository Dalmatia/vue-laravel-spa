import { ref, computed } from 'vue';
import axios from 'axios';

export function useWeatherForecast() {
    const selectedCity = ref(null);
    const weather = ref(null);
    const isLoading = ref(true);
    const error = ref(null);

    const formatCity = (data) => ({
        region_id: data.region_id,
        prefecture_id: data.prefecture_id,
        city_id: data.city_id,
        name: data.city?.name ?? data.city_name,
        latitude: data.city?.latitude ?? data.latitude,
        longitude: data.city?.longitude ?? data.longitude,
    });

    const fetchSavedCity = async () => {
        try {
            const { data } = await axios.get('/api/get_saved_location');
            if (data) selectedCity.value = formatCity(data);
        } catch (e) {
            console.error('保存された市区町村の取得に失敗しました:', e);
        }
    };

    const fetchWeather = async () => {
        if (!selectedCity.value) return;
        isLoading.value = true;
        try {
            const { data } = await axios.get('/api/weather', {
                params: {
                    latitude: selectedCity.value.latitude,
                    longitude: selectedCity.value.longitude,
                },
            });
            weather.value = data.weather;
            error.value = null;
        } catch (e) {
            error.value =
                e.response?.data?.message || '天気情報を取得できませんでした。';
            console.error('天気取得失敗:', e);
        } finally {
            isLoading.value = false;
        }
    };

    // 日付表示用のフォーマット
    const formattedWeather = computed(() => {
        if (!weather.value) return {};
        const result = {};
        for (const key of ['today', 'tomorrow']) {
            const w = weather.value[key];
            if (!w) continue;
            result[key] = {
                ...w,
                displayDate: `${new Date(w.date).getMonth() + 1}/${new Date(
                    w.date
                ).getDate()}`,
            };
        }
        return result;
    });

    return {
        selectedCity,
        weather,
        isLoading,
        error,
        formattedWeather,
        fetchSavedCity,
        fetchWeather,
    };
}
