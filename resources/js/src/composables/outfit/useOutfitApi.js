export function useOutfitApi() {
    const createOutfit = async (formData) => {
        return axios.post('/api/outfit', formData);
    };

    const updateOutfit = async (id, formData) => {
        return axios.post(`/api/outfit/${id}`, formData);
    };

    return {
        createOutfit,
        updateOutfit,
    };
}
