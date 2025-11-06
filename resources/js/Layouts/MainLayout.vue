<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute } from 'vue-router';
import { useLayoutState } from '../src/composables/useLayoutState';

import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import AccountPlusOutline from 'vue-material-design-icons/AccountPlusOutline.vue';

import TopNavHome from './TopNavHome.vue';
import SideNav from './SideNav.vue';
import SuggestionsSection from './SuggestionsSection.vue';
import BottomNav from './BottomNav.vue';
import CreateOutfitOverlay from '@/Components/Outfit/Create/CreateOutfitOverlay.vue';
import Notifications from '../Pages/Notification/NotificationPage.vue';

const authStore = useAuthStore();
const route = useRoute();
const topsNavRef = ref();
const { isDropdownOpen, noticeOpen, account, toggleMenu, closeMenu, logout } =
    useLayoutState();

let showCreatePost = ref(false);

const isUserNavVisible = computed(
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

onMounted(() => {
    if (topsNavRef.value?.account) {
        account.value = topsNavRef.value.account;
    }
    document.addEventListener('click', closeMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
});
</script>

<template>
    <div id="MainLayout" class="w-full h-screen">
        <div v-show="route.path == '/'">
            <TopNavHome
                ref="topsNavRef"
                :is-dropdown-open="isDropdownOpen"
                @toggle-menu="toggleMenu"
                @logout="logout"
            />
        </div>

        <div
            v-show="isUserNavVisible"
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

        <SideNav
            @open-create-post="showCreatePost = true"
            @toggle-menu="toggleMenu"
            @logout="logout"
        />

        <div
            class="flex lg:justify-between bg-white h-full overflow-auto pl-0 md:pl-[80px] xl:pl-[255px]"
        >
            <div
                class="mx-auto md:pt-6 pt-20"
                :class="
                    route.path === '/'
                        ? 'lg:w-8/12 w-full'
                        : 'max-w-[800px] w-full'
                "
            >
                <main class="container">
                    <slot />
                </main>
            </div>

            <SuggestionsSection v-show="route.path == '/'" />
        </div>

        <BottomNav @open-create-post="showCreatePost = true" />
    </div>

    <CreateOutfitOverlay
        v-if="showCreatePost"
        @close="showCreatePost = false"
    />
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
        @click.self="noticeOpen = false"
    >
        <div v-if="authStore.user && noticeOpen" class="fixed inset-0">
            <Notifications @close-notice="noticeOpen = false" />
        </div>
    </Transition>
</template>
