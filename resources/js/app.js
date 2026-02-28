import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import router from './Router';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';

import App from '../js/App.vue';
import { useThemeStore } from './stores/theme';

const app = createApp(App);
app.use(createPinia());

const authStore = useAuthStore();

authStore.fetchUserData().finally(() => {
    app.use(router).mount('#app');
    useThemeStore().initTheme();
});
