export function useOutfitApi() {
    const createOutfit = async (formData) => {
        return axios.post('/api/outfit', formData);
    };

    const updateOutfit = async (id, formData) => {
        return axios.post(`/api/outfit/${id}`, formData);
    };

    const buildSearchParams = (filters, sort, page) => ({
        ...filters,
        gender: filters.gender === 0 ? null : filters.gender,
        color: filters.color?.id || null,
        sort,
        page,
    });

    const getOutfits = async ({ filters, sort, page }) => {
        const params = buildSearchParams(filters, sort, page);
        const { data } = await axios.get('/api/outfits', { params });
        return {
            outfits: data.outfits,
            hasMore: data.meta.has_more,
        };
    };

    const getHomeOutfits = async () => {
        const { data } = await axios.get('/api/home');
        return data;
    };

    const getOutfit = async (id) => {
        const { data } = await axios.get(`/api/outfit/${id}`);
        return data;
    };

    return {
        createOutfit,
        updateOutfit,
        getOutfits,
        getHomeOutfits,
        getOutfit,
    };
}
