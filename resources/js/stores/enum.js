import { defineStore } from 'pinia';
import axios from 'axios';

export const useEnumStore = defineStore('enum', {
    state: () => ({
        colors: [],
        seasons: [],
        genders: [],
        loaded: false,
    }),

    actions: {
        async fetchEnums() {
            if (this.loaded) return;

            // まとめて取得するなら /api/enums が便利
            const { data } = await axios.get('/api/enums');

            this.colors = data.colors;
            this.seasons = data.seasons;

            // genders は endpoint が別
            const genderRes = await axios.get('/api/get_genders');
            this.genders = genderRes.data;

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
