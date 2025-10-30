import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        isDark: localStorage.getItem('theme') === 'dark',
    }),
    getters: {
        theme: (state) => state.isDark,
    },
    actions: {
        async applyTheme() {
            const root = document.documentElement;
            if (this.isDark) {
                root.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                root.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        },

        async initTheme() {
            this.applyTheme();
        },
    },
});
