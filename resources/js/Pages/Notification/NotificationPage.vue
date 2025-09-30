<script setup>
import { reactive, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';

import { useNotification } from '../../src/composables/useNotification';
import { useNotificationActions } from '../../src/composables/useNotificationActions';
import { useOutfitOverlay } from '../../src/composables/useOutfitOverlay';

import NotificationList from './NotificationList.vue';
import NotificationOptions from './NotificationOptions.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close-notice']);
const props = defineProps(['user']);
const router = useRouter();

// --- Composables ---
const { notifications, fetchNotifications, markAsRead, stopListening } =
    useNotification();
const {
    errorMessage,
    selectedNotification,
    openModal,
    showDeleteModal,
    confirmDelete,
    openOutfitDetails,
} = useNotificationActions(notifications);
const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();

// --- Local State ---
const state = reactive({
    isMobile: window.innerWidth <= 640,
});

// --- Notification Action Mapping ---
const notificationType = {
    'App\\Notifications\\FollowedUser': (n) => {
        router.push({ name: 'User', params: { id: n.follower_id } });
        emit('close-notice');
    },
    'App\\Notifications\\OutfitLiked': async (n) => {
        await openOutfitDetails(n.outfit_id, false);
    },
    'App\\Notifications\\OutfitCommented': async (n) => {
        await openOutfitDetails(n.outfit_id, true);
    },
};

// --- Handlers ---
const handleNotificationAction = async (notification) => {
    if (notificationType[notification.type]) {
        await notificationType[notification.type](notification);
    }
    await markAsRead(notification);
};

const closeNotification = () => {
    state.isMobile ? router.back() : emit('close-notice');
};

const handleResize = debounce(() => {
    const mobile = window.innerWidth <= 640;
    if (
        !mobile &&
        state.isMobile &&
        router.currentRoute.value.name === 'Notifications'
    ) {
        router.push({ name: 'Home' });
    }
    state.isMobile = mobile;
}, 200);

// --- Watchers & Lifecycle ---
watch(
    () => state.isMobile,
    (newValue, oldValue) => {
        if (!newValue && oldValue) fetchNotifications();
    }
);

onMounted(() => {
    window.addEventListener('resize', handleResize);
    fetchNotifications();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    stopListening();
});
</script>

<template>
    <div>
        <!-- エラーメッセージ -->
        <div
            v-if="errorMessage"
            class="fixed top-0 left-0 w-full z-50 bg-red-500 text-white p-2 text-center text-sm sm:text-base break-words"
        >
            {{ errorMessage }}
        </div>

        <!-- モバイルレイアウト -->
        <div
            v-if="state.isMobile"
            class="fixed inset-0 z-20 bg-white overflow-y-auto"
        >
            <div
                class="flex items-center sticky top-0 justify-between px-4 py-4 border-b bg-white"
            >
                <button
                    @click="closeNotification"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <ArrowLeft :size="24" class="cursor-pointer" />
                </button>
                <span class="text-lg font-bold">お知らせ</span>
                <div class="w-6"></div>
            </div>

            <NotificationList
                :notifications="notifications"
                @read="handleNotificationAction"
                @delete="showDeleteModal($event)"
            />
        </div>

        <!-- デスクトップレイアウト -->
        <div
            v-else
            class="absolute top-0 left-[80px] xl:left-64 z-0 w-full md:w-[397px] h-full bg-slate-100 shadow-md rounded-r-2xl border-r transition-transform duration-300 overflow-auto hidden-scrollbar"
        >
            <div
                class="flex items-center justify-between px-6 py-4 border-b bg-white"
            >
                <span class="text-lg font-bold">お知らせ</span>
                <button
                    @click.stop="closeNotification"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <Close :size="33" class="cursor-pointer" />
                </button>
            </div>

            <NotificationList
                :notifications="notifications"
                @read="handleNotificationAction"
                @delete="showDeleteModal($event)"
            />
        </div>

        <!-- コーディネート詳細ページオーバーレイ -->
        <ShowOutfitOverlay
            v-if="overlayState.open"
            :outfit="overlayState.currentOutfit"
            :commentOverlay="overlayState.commentOverlay"
            @delete-selected="deleteOutfit($event)"
            @close-overlay="toggleOutfitOverlay()"
        />

        <!-- 通知削除モーダル -->
        <NotificationOptions
            v-if="openModal"
            @delete-selected="confirmDelete()"
            @close="openModal = false"
        />
    </div>
</template>

<style scoped>
.hidden-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.hidden-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
