import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const Home = () => import('../Pages/Home.vue');
const Login = () => import('../Pages/Auth/Login.vue');
const Register = () => import('../Pages/Auth/RegisterPage/RegisterForm.vue');
const User = () => import('../Pages/User.vue');
const EditProfile = () => import('../Pages/ProfilePage/EditProfile.vue');
const ContentOverlay = () => import('@/Components/ContentOverlay.vue');
const Items = () => import('../Pages/Items.vue');
const CategoryItems = () => import('../Pages/CategoryItems.vue');
const Calendar = () => import('../Pages/CalendarPage/Calendar.vue');
const Likes = () => import('../Pages/LikesPage.vue');
const FollowList = () => import('../Pages/FollowList.vue');
const FollowerList = () => import('../Pages/FollowerList.vue');
const Search = () => import('../Pages/OutfitSearch/Search.vue');
const Notifications = () =>
    import('../Pages/Notification/NotificationPage.vue');
const SuggestionsUsers = () => import('../Pages/SuggestionsUsers.vue');
const Settings = () => import('../Pages/Settings.vue');
const PasswordChangeForm = () => import('../Pages/PasswordChangeForm.vue');
const DeleteAccountConfirm = () => import('../Pages/DeleteAccountConfirm.vue');
const DeleteAccountSuccess = () => import('../Pages/DeleteAccountSuccess.vue');

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home,
            meta: { requiresAuth: true },
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
            path: '/settings',
            name: 'Settings',
            component: Settings,
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/change_password',
            name: 'PasswordChangeForm',
            component: PasswordChangeForm,
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/delete_account',
            name: 'DeleteAccountConfirm',
            component: DeleteAccountConfirm,
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/delete_account/success',
            name: 'DeleteAccountSuccess',
            component: DeleteAccountSuccess,
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
            props: (route) => ({ user: { id: route.params.id } }),
        },
        {
            path: '/suggestion_users',
            name: 'SuggestionsUsers',
            component: SuggestionsUsers,
            meta: { requiresAuth: true },
        },
    ],
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    const authenticated = !!authStore.user;
    const userId = authStore.user?.id;
    const routeUserId = Number(to.params.id);

    if (to.meta.requiresGuest && authenticated) {
        return next({ name: 'Home' });
    }
    if (to.meta.requiresAuth && !authenticated) {
        return next({ name: 'Login' });
    }
    if (
        to.meta.requiresAuth &&
        ['Items', 'EditProfile', 'Notifications'].includes(to.name) &&
        routeUserId !== userId
    ) {
        return next({ name: 'Home' });
    }

    next();
});

export default router;
