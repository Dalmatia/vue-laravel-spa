<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute } from 'vue-router';
import { useLayoutState } from '../src/composables/useLayoutState';
import { useNotification } from '../src/composables/useNotification';
import { useNotificationActions } from '../src/composables/useNotificationActions';

import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import AccountPlusOutline from 'vue-material-design-icons/AccountPlusOutline.vue';

import TopNavHome from './TopNavHome.vue';
import SideNav from './SideNav.vue';
import SuggestionsSection from './SuggestionsSection.vue';
import BottomNav from './BottomNav.vue';
import CreateOutfitOverlay from '@/Components/Outfit/Create/CreateOutfitOverlay.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import NotificationPanel from '../Components/NotificationPanel.vue';

let showCreatePost = ref(false);
const authStore = useAuthStore();
const route = useRoute();
const { notifications, fetchNotifications, markAsRead, stopListening } =
    useNotification();
const {
    handleNotificationAction,
    showDeleteModal,
    confirmDelete,
    overlayState,
    toggleOutfitOverlay,
    deleteOutfit,
} = useNotificationActions(notifications);

const {
    isDropdownOpen,
    noticeOpen,
    isMobile,
    account,
    toggleMenu,
    closeMenu,
    logout,
} = useLayoutState();
const topsNavRef = ref();

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
    fetchNotifications();
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenu);
});
</script>

<template>
    <div id="MainLayout" class="w-full h-screen" @click="noticeOpen = false">
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

            <SuggestionsSection v-show="route.path == '/'" />
        </div>

        <BottomNav @open-create-post="showCreatePost = true" />
    </div>

    <CreateOutfitOverlay
        v-if="showCreatePost"
        @close="showCreatePost = false"
    />

    <ShowOutfitOverlay
        v-if="overlayState.open"
        :outfit="overlayState.currentOutfit"
        :commentOverlay="overlayState.commentOverlay"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="toggleOutfitOverlay()"
    />

    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
    >
        <NotificationPanel
            v-if="!isMobile && noticeOpen"
            :notifications="notifications"
            :onRead="handleNotificationAction"
            :onDelete="showDeleteModal"
            :onClose="() => (noticeOpen = false)"
            @click.stop
            class="fixed top-0 left-[80px] xl:left-64 z-20 h-full"
        />
    </Transition>
</template>
