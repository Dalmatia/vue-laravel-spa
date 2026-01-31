import { ref, watch } from 'vue';
import axios from 'axios';

export function useClothingAdvice(selectedCity, weather) {
    const selectedTpo = ref('casual');
    const selectedTab = ref('today');
    const advice = ref(null);
    const isAdviceLoading = ref(false);
    const cache = ref({});

    // TPOを含む服装アドバイスの取得
    const fetchClothingAdvice = async () => {
        if (!weather.value || !selectedCity.value) return;

        const w = weather.value[selectedTab.value];
        if (!w) return;

        const cacheKey = `${selectedCity.value.city_id}_${selectedTpo.value}_${w.date}`;
        if (cache.value[cacheKey]) {
            advice.value = cache.value[cacheKey];
            return;
        }

        isAdviceLoading.value = true;
        advice.value = null;

        try {
            const { data } = await axios.post('/api/clothing_advice', {
                weather: weather.value,
                selectedTab: selectedTab.value,
                tpo: selectedTpo.value,
                targetDate: w.date,
                cityId: selectedCity.value.city_id,
            });

            advice.value = data;
            cache.value[cacheKey] = data;
        } catch (error) {
            advice.value = {
                error: true,
                message:
                    error.response?.data?.message ||
                    '服装アドバイスを取得できませんでした。',
            };
            console.error(error);
        } finally {
            isAdviceLoading.value = false;
        }
    };

    // TPOまたは日付タブが切り替わったら再取得
    watch(
        [selectedCity, selectedTpo, selectedTab, weather],
        fetchClothingAdvice,
        { deep: true },
    );

    return {
        selectedTpo,
        selectedTab,
        advice,
        isAdviceLoading,
        fetchClothingAdvice,
    };
}
