<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';

import { useNotification } from '../../src/composables/useNotification';
import { useNotificationActions } from '../../src/composables/useNotificationActions';

import NotificationList from './NotificationList.vue';
import NotificationOptions from './NotificationOptions.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';

const router = useRouter();
const isMobile = ref(window.innerWidth <= 640);
const { notifications, fetchNotifications, markAsRead, stopListening } =
    useNotification();
const {
    errorMessage,
    selectedNotification,
    openModal,
    handleNotificationAction,
    showDeleteModal,
    confirmDelete,
    openOutfitDetails,
    overlayState,
    toggleOutfitOverlay,
    deleteOutfit,
} = useNotificationActions(notifications);

const handleResize = debounce(() => {
    const mobile = window.innerWidth <= 640;
    if (
        !mobile &&
        isMobile.value &&
        router.currentRoute.value.name === 'Notifications'
    ) {
        router.push({ name: 'Home' });
    }
    isMobile.value = mobile;
}, 200);

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
        <div class="fixed inset-0 z-20 bg-white overflow-y-auto">
            <NotificationList
                class="pt-[61px]"
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
