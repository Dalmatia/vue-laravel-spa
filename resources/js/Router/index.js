import { createRouter, createWebHistory } from 'vue-router';

import Home from '../Pages/Home.vue';
import User from '../Pages/User.vue';
import Login from '../Pages/Auth/Login.vue';
import Register from '../Pages/Auth/Register.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home,
    },
    {
        path: '/user',
        name: 'User',
        component: User,
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { requiresGuest: true },
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { requiresGuest: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
