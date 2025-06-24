import { computed, ref, watch } from 'vue';

export function useLocationSelector(emit) {
    // データ状態
    const regions = ref([]);
    const prefectures = ref([]);
    const cities = ref([]);

    // 選択された値(初期値を設定)
    const selectedRegion = ref({
        id: null,
        name: '地域を選択',
    });
    const selectedPrefecture = ref({
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
    const isLoadingPrefectures = ref(false);
    const isLoadingCities = ref(false);
    const isLoadingSavedData = ref(false);

    const errorMessage = ref('');
    const isFormValid = computed(
        () =>
            !!selectedRegion.value.id &&
            !!selectedPrefecture.value.id &&
            !!selectedCity.value.id
    );

    let fetchPrefecturesToken = 0;
    let fetchCitiesToken = 0;

    // 地域データを取得
    async function fetchRegions() {
        isLoadingRegions.value = true;
        try {
            const response = await axios.get('/api/regions');
            regions.value = (response.data.regions || []).map((region) => ({
                id: region.id,
                name: region.name,
            }));
        } catch (error) {
            errorMessage.value = '地域データの取得に失敗しました。';
            console.error(error);
        } finally {
            isLoadingRegions.value = false;
        }
    }

    // 都道府県データを取得
    async function fetchPrefectures() {
        if (!selectedRegion.value.id) return;

        // トークンを使用して重複リクエストを防ぐ
        const currentToken = ++fetchPrefecturesToken;
        isLoadingPrefectures.value = true;
        try {
            const response = await axios.get(
                `/api/region/${selectedRegion.value.id}/prefectures`,
                {
                    params: { region: selectedRegion.value.id },
                }
            );
            if (currentToken !== fetchPrefecturesToken) return;
            prefectures.value = (response.data.prefectures || []).map(
                (pref) => ({
                    id: pref.id,
                    name: pref.name,
                })
            );
        } catch (error) {
            errorMessage.value = '都道府県データの取得に失敗しました。';
            console.error(error);
        } finally {
            isLoadingPrefectures.value = false;
        }
    }

    // 市区町村データを取得
    async function fetchCities() {
        if (!selectedPrefecture.value.id) return;
        // トークンを使用して重複リクエストを防ぐ
        const currentToken = ++fetchCitiesToken;
        isLoadingCities.value = true;
        try {
            const response = await axios.get(
                `/api/prefecture/${selectedPrefecture.value.id}/cities`,
                {
                    params: {
                        region: selectedRegion.value.id,
                        prefecture: selectedPrefecture.value.id,
                    },
                }
            );
            if (currentToken !== fetchCitiesToken) return;
            cities.value = response.data.cities.map((city) => ({
                id: city.id,
                name: city.name,
                latitude: city.lat,
                longitude: city.lon,
            }));
        } catch (error) {
            errorMessage.value = '市区町村データの取得に失敗しました。';
            console.error(error);
        } finally {
            isLoadingCities.value = false;
        }
    }

    //選択した市区町村を保存
    async function saveSelectedLocation() {
        if (!isFormValid.value) return;
        try {
            await axios.post('/api/save_selected_location', {
                region_id: selectedRegion.value.id,
                prefecture_id: selectedPrefecture.value.id,
                city_id: selectedCity.value.id,
            });

            emit('city-saved', {
                region: selectedRegion.value.id,
                prefecture: selectedPrefecture.value.id,
                city: selectedCity.value,
            });
        } catch (error) {
            errorMessage.value = '地域情報の保存に失敗しました。';
            console.error(error);
        }
    }

    async function fetchSavedLocation() {
        isLoadingSavedData.value = true;
        try {
            const response = await axios.get('/api/get_saved_location');
            if (response.data) await applySavedLocation(response.data);
        } catch (error) {
            errorMessage.value = '保存された地域データの取得に失敗しました。';
            console.error(error);
        } finally {
            isLoadingSavedData.value = false;
        }
    }

    async function applySavedLocation(data) {
        selectedRegion.value.id = data.region_id;
        await fetchPrefectures();
        selectedPrefecture.value.id = data.prefecture_id;
        await fetchCities();
        selectedCity.value.id = data.city_id;

        // 名前を補完（UI表示用）
        const region = regions.value.find((r) => r.id === data.region_id);
        const prefecture = prefectures.value.find(
            (p) => p.id === data.prefecture_id
        );
        const city = cities.value.find((c) => c.id === data.city_id);

        selectedRegion.value.name = region?.name || '';
        selectedPrefecture.value.name = prefecture?.name || '';
        selectedCity.value.name = city?.name || '';
    }

    // 依存関係の監視
    watch(
        () => selectedRegion.value.id,
        async (newRegion, oldRegion) => {
            if (newRegion === oldRegion) return;
            selectedPrefecture.value = { id: null, name: '都道府県を選択' };
            selectedCity.value = { id: null, name: '市区町村を選択' };
            prefectures.value = [];
            cities.value = [];
            await fetchPrefectures();
        }
    );

    watch(
        () => selectedPrefecture.value.id,
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

    return {
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
    };
}
