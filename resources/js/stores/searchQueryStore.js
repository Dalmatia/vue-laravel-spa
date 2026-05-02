import { defineStore } from 'pinia';

export const useSearchQueryStore = defineStore('searchQuery', {
    state: () => ({
        query: {},
    }),

    actions: {
        setQuery(query) {
            this.query = { ...query };
        },
    },
});
