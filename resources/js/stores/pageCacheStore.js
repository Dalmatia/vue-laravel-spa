import { defineStore } from 'pinia';

export const usePageCacheStore = defineStore('pageCache', {
    state: () => ({
        cache: {},
    }),

    actions: {
        get(key) {
            return this.cache[key];
        },

        set(key, data) {
            this.cache[key] = data;
        },

        updateScroll(key, scrollY) {
            if (!this.cache[key]) return;

            this.cache[key].scrollY = scrollY;
        },

        has(key) {
            return !!this.cache[key];
        },

        clear() {
            this.cache = {};
        },
    },
});
