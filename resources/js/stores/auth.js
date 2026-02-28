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
            try {
                await axios.get('/sanctum/csrf-cookie');
            } catch (error) {
                console.error('CSRFトークンの取得に失敗:', error);
                throw error;
            }
        },
        async fetchUserData() {
            try {
                const response = await axios.get('/api/user');
                this.authUser = response.data;
            } catch (error) {
                console.error('ユーザー情報の取得に失敗:', error);
                this.authUser = null;
                throw error;
            }
        },
        async logout() {
            try {
                await axios.post('/api/logout');
                this.authUser = null;
            } catch (error) {
                console.error('ログアウトに失敗:', error);
                throw error;
            }
        },
    },
});
