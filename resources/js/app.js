import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import router from './Router';
import { createPinia } from 'pinia';

import App from '../js/App.vue';

const pinia = createPinia();

createApp(App).use(router).use(pinia).mount('#app');
