import { defineStore } from 'pinia';

export const useEnumStore = defineStore('enum', {
    state: () => ({
        genders: [],
        mainCategories: [],
        subCategories: [],
        colors: [],
        seasons: [],
        loaded: false,
    }),

    actions: {
        async fetchEnums() {
            if (this.loaded) return;

            const { data } = await axios.get('/api/enums');

            this.genders = data.genders ?? [];
            this.mainCategories = data.mainCategories;
            this.subCategories = data.subCategories;
            this.colors = data.colors;
            this.seasons = data.seasons;

            this.loaded = true;
        },

        // --- 名前変換ヘルパー ---
        getColor(id) {
            const item = this.colors.find((c) => c.id === Number(id));
            return item ? item.name : '未選択';
        },

        getSeason(id) {
            const item = this.seasons.find((s) => s.id === Number(id));
            return item ? item.name : '未選択';
        },

        getGender(id) {
            const item = this.genders.find((g) => g.value === Number(id));
            return item ? item.label : '未選択';
        },
    },
});
