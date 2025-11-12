<script setup>
import { reactive, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useNotification } from '../../src/composables/useNotification';
import { useNotificationActions } from '../../src/composables/useNotificationActions';

import NotificationList from './NotificationList.vue';
import NotificationOptions from './NotificationOptions.vue';
import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

const router = useRouter();

// --- Composables ---
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

const closeNotification = () => {
    router.back();
};

onMounted(() => {
    fetchNotifications();
});

onUnmounted(() => {
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
