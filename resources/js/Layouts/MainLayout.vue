<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

import Magnify from 'vue-material-design-icons/Magnify.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import HomeOutline from 'vue-material-design-icons/HomeOutline.vue';
import Calendar from 'vue-material-design-icons/Calendar.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import AccountPlusOutline from 'vue-material-design-icons/AccountPlusOutline.vue';
import BellOutline from 'vue-material-design-icons/BellOutline.vue';
import Setting from 'vue-material-design-icons/Cog.vue';
import Logout from 'vue-material-design-icons/Logout.vue';
import AccountArrowRightOutline from 'vue-material-design-icons/AccountArrowRightOutline.vue';

import MenuItem from '@/Components/MenuItem.vue';
import CreateOutfitOverlay from '@/Components/Outfits/CreateOutfitOverlay.vue';
import Notifications from '../Pages/Notifications.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

let showCreatePost = ref(false);
let isLoading = ref(true);
let isDropdownOpen = ref(false);
let noticeOpen = ref(false);
const account = ref(null);
const notifications = ref(null);
const BREAKPOINT_MOBILE = 640;
const isMobile = ref(window.innerWidth <= BREAKPOINT_MOBILE);

// ユーザー情報の取得
const fetchUserData = async () => {
    try {
        await authStore.fetchUserData();
    } catch (error) {
        if (error.response && error.response.status === 401) {
            logout();
        }
    } finally {
        isLoading.value = false;
    }
};

const isAuthenticatedAndNotInSpecificRoutes = computed(
    () =>
        authStore.user &&
        ![
            '/',
            `/user/${authStore.user.id}/editProfile`,
            `/user/${authStore.user.id}/follow_list`,
            `/user/${authStore.user.id}/follower_list`,
            '/search',
            `/user/${authStore.user.id}/notifications`,
        ].includes(route.path)
);

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'Login' });
};

const toggleMenu = (dropdownType, event) => {
    if (event) event.stopPropagation();
    if (dropdownType === 'account') {
        isDropdownOpen.value = !isDropdownOpen.value;
    }
    if (dropdownType === 'notifications') {
        noticeOpen.value = !noticeOpen.value;
    }
};

const closeMenu = (event) => {
    if (
        (account.value && !account.value.contains(event.target)) ||
        (notifications.value && !notifications.value.contains(event.target))
    ) {
        isDropdownOpen.value = false;
        noticeOpen.value = false;
    }
};

// 画面サイズと向きに応じて通知メニューの挙動を調整
const handleResize = () => {
    const orientationType = screen.orientation?.type || '';
    const isPortrait = orientationType.includes('portrait');
    const mobile = window.innerWidth > BREAKPOINT_MOBILE;
    if (isPortrait && !mobile && isMobile.value) {
        noticeOpen.value = false;
    }
    isMobile.value = mobile;
};

onMounted(() => {
    fetchUserData();
    window.addEventListener('click', closeMenu);
    window.addEventListener('resize', handleResize);
    if (screen.orientation?.addEventListener) {
        screen.orientation.addEventListener('change', handleResize);
    }
});

onUnmounted(() => {
    window.removeEventListener('click', closeMenu);
    window.removeEventListener('resize', handleResize);
    if (screen.orientation?.removeEventListener) {
        screen.orientation.removeEventListener('change', handleResize);
    }
});
</script>

<template>
    <div id="MainLayout" class="w-full h-screen">
        <div
            v-if="route.path == '/'"
            id="TopNavHome"
            class="fixed z-30 md:hidden block w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <div class="flex items-center justify-between h-full">
                <router-link :to="{ name: 'Home' }">
                    <h1 class="font-aurore w-[105px] ml-6 cursor-pointer">
                        daily outfit
                    </h1>
                </router-link>

                <div class="flex items-center justify-end w-full">
                    <router-link :to="{ name: 'Likes' }" class="pl-4 pr-3">
                        <HeartOutline fillColor="#000000" :size="27" />
                    </router-link>

                    <!-- 通知アイコン -->
                    <div class="relative pl-4 pr-4">
                        <router-link
                            v-if="authStore.user"
                            :to="{
                                name: 'Notifications',
                                params: { id: authStore.user.id },
                            }"
                            class="relative"
                        >
                            <BellOutline fillColor="#000000" :size="27" />
                            <span
                                class="absolute top-0 right-0 translate-x-1/2 translate-y-1/2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-red-100 bg-red-600 rounded-full"
                            >
                                10
                            </span>
                        </router-link>
                    </div>

                    <!-- アカウントアイコン -->
                    <div class="relative" ref="account" v-if="authStore.user">
                        <div
                            @click="toggleMenu('account', $event)"
                            class="flex items-center cursor-pointer p-2 rounded-full hover:bg-gray-100 transition-all duration-300"
                        >
                            <div
                                class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-300"
                            >
                                <img
                                    :src="authStore.user.file"
                                    alt="Profile"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>

                        <!-- ドロップダウンメニューの内容 -->
                        <div
                            v-if="isDropdownOpen"
                            class="absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
                        >
                            <ul class="py-1">
                                <li
                                    class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                                >
                                    <router-link
                                        :to="{
                                            name: 'EditProfile',
                                            params: { id: authStore.user.id },
                                        }"
                                        v-if="authStore.user"
                                        class="flex items-center space-x-2 text-gray-700"
                                    >
                                        <AccountOutline class="w-5 h-5" />
                                        <span>プロフィール編集</span>
                                    </router-link>
                                </li>
                                <li
                                    class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                                >
                                    <div
                                        class="flex items-center space-x-2 text-gray-700"
                                    >
                                        <Setting class="w-5 h-5" />
                                        <span>設定</span>
                                    </div>
                                </li>
                                <li
                                    class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                                >
                                    <div
                                        @click.prevent="logout()"
                                        class="flex items-center space-x-2 text-red-600 cursor-pointer"
                                    >
                                        <Logout class="w-5 h-5" />
                                        <span>ログアウト</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isAuthenticatedAndNotInSpecificRoutes"
            id="TopNavUser"
            class="md:hidden fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <router-link :to="{ name: 'Home' }" class="px-4">
                <ChevronLeft :size="30" class="cursor-pointer"></ChevronLeft>
            </router-link>
            <div class="font-extrabold text-lg" v-if="authStore.user">
                {{ authStore.user.name }}
            </div>
            <AccountPlusOutline :size="30" class="px-4"></AccountPlusOutline>
        </div>

        <div
            id="SideNav"
            class="fixed h-full bg-white xl:w-[255px] w-[80px] md:block hidden border-r border-r-gray-300 z-10"
        >
            <router-link :to="{ name: 'Home' }">
                <img
                    class="xl:hidden block w-[25px] mt-10 ml-[25px] mb-10 cursor-pointer"
                    src="/icons/hanger.svg"
                />
                <h1
                    class="font-aurore xl:block hidden w-[120px] mt-10 ml-6 mb-10 cursor-pointer"
                >
                    daily outfit
                </h1>
            </router-link>

            <div class="px-3">
                <router-link :to="{ name: 'Home' }">
                    <MenuItem iconString="Home" class="mb-4 lg:mb-2" />
                </router-link>
                <router-link :to="{ name: 'Search' }">
                    <MenuItem iconString="Search" class="mb-4 lg:mb-2" />
                </router-link>
                <router-link :to="{ name: 'Calendar' }" v-if="authStore.user">
                    <MenuItem iconString="Calendar" class="mb-4 lg:mb-2" />
                </router-link>
                <div
                    ref="notifications"
                    v-if="authStore.user"
                    @click="toggleMenu('notifications', $event)"
                >
                    <MenuItem iconString="Notifications" class="mb-4 lg:mb-2" />
                </div>
                <router-link :to="{ name: 'Likes' }" v-if="authStore.user">
                    <MenuItem iconString="Likes" class="mb-4 lg:mb-2" />
                </router-link>
                <MenuItem
                    v-if="authStore.user"
                    @click="showCreatePost = true"
                    iconString="Create"
                    class="mb-4 lg:mb-2"
                />
                <router-link :to="{ name: 'Login' }" v-if="!authStore.user">
                    <MenuItem iconString="Login" class="mb-4 lg:mb-2" />
                </router-link>
                <router-link
                    :to="{ name: 'User', params: { id: authStore.user.id } }"
                    v-if="authStore.user"
                >
                    <MenuItem iconString="Profile" class="mb-4 lg:mb-2" />
                </router-link>
            </div>

            <button
                type="button"
                class="absolute bottom-0 px-3 w-full"
                @click="logout"
                v-if="authStore.user"
            >
                <MenuItem iconString="Logout" class="mb-4" />
            </button>
        </div>

        <div
            class="flex lg:justify-between bg-white h-full w-[100%-280px] xl:pl-[280px] lg:pl-[100px] overflow-auto"
        >
            <div
                class="mx-auto md:pt-6 pt-20"
                :class="
                    route.path === '/' ? 'lg:w-8/12 w-full' : 'max-w-[1200px]'
                "
            >
                <main class="container">
                    <slot />
                </main>
            </div>

            <div
                v-if="route.path == '/'"
                id="SuggestionsSection"
                class="lg:w-4/12 lg:block hidden text-black mt-10"
            >
                <div
                    class="flex items-center justify-between max-w-[300px]"
                    v-if="authStore.user"
                >
                    <div class="flex items-center">
                        <router-link
                            :to="{
                                name: 'User',
                                params: { id: authStore.user.id },
                            }"
                        >
                            <img
                                class="rounded-full z-10 w-[58px] h-[58px]"
                                :src="authStore.user.file"
                            />
                        </router-link>
                        <div class="pl-4">
                            <router-link
                                class="text-black font-extrabold"
                                :to="{
                                    name: 'User',
                                    params: { id: authStore.user.id },
                                }"
                            >
                                {{ authStore.user.name }}
                            </router-link>
                            <div class="text-gray-500 text-extrabold text-sm">
                                NAME HERE
                            </div>
                        </div>
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        切り替え
                    </button>
                </div>

                <div
                    class="max-w-[300px] flex items-center justify-between py-3"
                >
                    <div class="text-gray-500 font-extrabold">
                        おすすめのユーザー
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        全て見る
                    </button>
                </div>

                <a
                    href="/"
                    class="flex items-center justify-between max-w-[300px] pb-2"
                >
                    <div class="flex items-center">
                        <img
                            class="rounded-full z-10 w-[37px] h-[37px]"
                            src="https://picsum.photos/id/200/300/320"
                        />
                        <div class="pl-4">
                            <div class="text-black font-extrabold">
                                NAME HERE
                            </div>
                            <div class="text-gray-500 text-extrabold text-sm">
                                NAME HERE
                            </div>
                        </div>
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        フォロー
                    </button>
                </a>
            </div>
        </div>

        <div
            id="BottomNav"
            class="fixed z-30 bottom-0 w-full md:hidden flex items-center justify-around bg-white border-t py-2 border-t-gray-300"
        >
            <router-link :to="{ name: 'Home' }">
                <HomeOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
            <router-link :to="{ name: 'Search' }">
                <Magnify
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
            <Plus
                @click="showCreatePost = true"
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
            <router-link :to="{ name: 'Calendar' }">
                <Calendar
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
            <router-link :to="{ name: 'Login' }" v-if="!authStore.user">
                <AccountArrowRightOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
            <router-link
                :to="{ name: 'User', params: { id: authStore.user.id } }"
                v-if="authStore.user"
            >
                <AccountOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
        </div>
    </div>

    <CreateOutfitOverlay
        v-if="showCreatePost"
        @close="showCreatePost = false"
    />
    <div v-if="authStore.user">
        <Notifications
            v-show="noticeOpen"
            @close-notice="noticeOpen = false"
            :user="authStore.user"
        />
    </div>
</template>
