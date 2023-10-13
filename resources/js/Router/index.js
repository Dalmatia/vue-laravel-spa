import { createRouter, createWebHistory } from 'vue-router';

const Home = () => import('../Pages/Home.vue');
const Login = () => import('../Pages/Auth/Login.vue');
const Register = () => () => import('../Pages/Auth/Register.vue');
const User = () => import('../Pages/User.vue');
const ContentOverlay = () => import('@/Components/ContentOverlay.vue');
const Items = () => import('../Pages/Items.vue');
const Calendar = () => import('../Pages/Calendar.vue');

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home,
        },
        {
            path: '/user/:id',
            component: User,
            meta: { requiresAuth: true },
            children: [
                {
                    path: '',
                    name: 'User',
                    component: ContentOverlay,
                    meta: { requiresAuth: true },
                },
                {
                    path: 'items',
                    name: 'Items',
                    component: Items,
                    meta: { requiresAuth: true },
                },
            ],
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
        {
            path: '/calendar',
            name: 'Calendar',
            component: Calendar,
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
