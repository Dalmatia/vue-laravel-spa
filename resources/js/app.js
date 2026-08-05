import './bootstrap';
import '../css/app.css';
import '../js/src/specialColors.js';

import { createApp } from 'vue';
import router from './Router';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';

import App from '../js/App.vue';
import { useThemeStore } from './stores/theme';
import { useEnumStore } from './stores/enum';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

const authStore = useAuthStore();
const enumStore = useEnumStore();
const themeStore = useThemeStore();

(async () => {
    await Promise.all([authStore.fetchUserData(), enumStore.fetchEnums()]);

    app.use(router).mount('#app');
    themeStore.initTheme();
})();
