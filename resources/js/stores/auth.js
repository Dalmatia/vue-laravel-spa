import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        authUser: null,
    }),
    getters: {
        user: (state) => state.authUser,
    },
    actions: {
        async getToken() {
            await axios.get('/sanctum/csrf-cookie');
        },
        async fetchUserData() {
            const response = await axios.get('/api/user');
            this.authUser = response.data;
        },
        async logout() {
            await axios.post('/api/logout');
            this.authUser = null;
        },
    },
});
