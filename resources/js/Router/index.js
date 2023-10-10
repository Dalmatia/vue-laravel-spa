import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Home',
            component: () => import('../Pages/Home.vue'),
        },
        {
            path: '/user',
            name: 'User',
            component: () => import('../Pages/User.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/login',
            name: 'Login',
            component: () => import('../Pages/Auth/Login.vue'),
            meta: { requiresGuest: true },
        },
        {
            path: '/register',
            name: 'Register',
            component: () => import('../Pages/Auth/Register.vue'),
            meta: { requiresGuest: true },
        },
        {
            path: '/calendar',
            name: 'Calendar',
            component: () => import('../Pages/Calendar.vue'),
            meta: { requiresAuth: true },
        },
    ],
});

router.beforeEach((to, from, next) => {
    const authenticated = localStorage.getItem('authenticated');

    if (to.meta.requiresGuest && authenticated) {
        next({ name: 'Home' });
    } else if (to.meta.requiresAuth && !authenticated) {
        next({ name: 'Login' });
    } else {
        next(); // 通常のルート遷移
    }
});

export default router;
