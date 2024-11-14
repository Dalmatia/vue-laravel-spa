import { createRouter, createWebHistory } from 'vue-router';

const Home = () => import('../Pages/Home.vue');
const Login = () => import('../Pages/Auth/Login.vue');
const Register = () => import('../Pages/Auth/Register.vue');
const User = () => import('../Pages/User.vue');
const EditProfile = () => import('../Pages/EditProfile.vue');
const ContentOverlay = () => import('@/Components/ContentOverlay.vue');
const Items = () => import('../Pages/Items.vue');
const CategoryItems = () => import('../Pages/CategoryItems.vue');
const Calendar = () => import('../Pages/Calendar.vue');
const Likes = () => import('../Pages/LikesPage.vue');
const FollowList = () => import('../Pages/FollowList.vue');
const FollowerList = () => import('../Pages/FollowerList.vue');
const Search = () => import('../Pages/Search.vue');
const Notifications = () => import('../Pages/Notifications.vue');

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home,
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
            path: '/user/:id/editProfile',
            name: 'EditProfile',
            component: EditProfile,
            meta: { requiresAuth: true },
        },
        {
            path: '/user/:id/items/:mainCategory',
            name: 'CategoryItems',
            component: CategoryItems,
            meta: { requiresAuth: true },
        },
        {
            path: '/likes',
            name: 'Likes',
            component: Likes,
            meta: { requiresAuth: true },
        },
        {
            path: '/user/:id/follow_list',
            name: 'FollowList',
            component: FollowList,
            meta: { requiresAuth: true },
        },
        {
            path: '/user/:id/follower_list',
            name: 'FollowerList',
            component: FollowerList,
            meta: { requiresAuth: true },
        },
        {
            path: '/search',
            name: 'Search',
            component: Search,
        },
        {
            path: '/user/:id/notifications',
            name: 'Notifications',
            component: Notifications,
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
