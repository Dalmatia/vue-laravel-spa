import './bootstrap';

import { createApp } from 'vue';
import router from './Router';

import App from '../js/App.vue';

createApp(App).use(router).mount('#app');
