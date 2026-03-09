import { ref, onMounted } from 'vue';

export function useOutfitEnums() {
    const seasons = ref([]);

    const fetchEnums = async () => {
        const response = await axios.get('/api/enums');
        seasons.value = response.data.seasons;
    };

    onMounted(fetchEnums);

    return { seasons };
}
