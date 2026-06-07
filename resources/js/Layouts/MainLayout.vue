<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useLayoutState } from '../src/composables/useLayoutState';
import { useNotification } from '../src/composables/useNotification';
import { useNotificationActions } from '../src/composables/useNotificationActions';
import { useIntersectionObserver } from '../src/composables/common/useIntersectionObserver';

import TopNavHome from './TopNavHome.vue';
import SideNav from './SideNav.vue';
import SuggestionsSection from './SuggestionsSection.vue';
import BottomNav from './BottomNav.vue';
import OutfitFormOverlay from '../Components/Outfit/Form/OutfitFormOverlay.vue';
import ShowOutfitOverlay from '../Components/Outfit/ShowOutfitOverlay.vue';
import NotificationPanel from '../Components/NotificationPanel.vue';
import NotificationOptions from '../Pages/Notification/NotificationOptions.vue';

let showCreatePost = ref(false);
const route = useRoute();
const {
    notifications,
    hasMore,
    fetchNotifications,
    markAsRead,
    stopListening,
} = useNotification();

const {
    openModal,
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
const notificationPanel = ref(null);

useIntersectionObserver({
    target: computed(
        () => notificationPanel.value?.notificationList?.loadMoreTrigger,
    ),

    root: computed(() => notificationPanel.value?.scrollContainer),

    onIntersect: fetchNotifications,

    enabled: hasMore,
});

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

        <SideNav
            @open-create-post="showCreatePost = true"
            @toggle-menu="toggleMenu"
            @logout="logout"
        />

        <div
            id="scroll-container"
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

    <OutfitFormOverlay v-if="showCreatePost" @close="showCreatePost = false" />

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
            ref="notificationPanel"
            v-if="!isMobile && noticeOpen"
            :notifications="notifications"
            :onRead="handleNotificationAction"
            :onDelete="showDeleteModal"
            :onClose="() => (noticeOpen = false)"
            @click.stop
            class="fixed top-0 left-[80px] xl:left-64 z-20 h-full"
        />
    </Transition>

    <NotificationOptions
        v-if="openModal"
        @delete-selected="confirmDelete()"
        @close="openModal = false"
    />
</template>
