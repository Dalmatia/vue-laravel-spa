<script setup>
import { computed, defineProps, defineEmits, ref } from 'vue';
import NotificationItem from '@/Components/Notification/NotificationItem.vue';

const props = defineProps({
    notifications: Array,
    isLoading: Boolean,
    hasLoaded: Boolean,
    hasMore: Boolean,
});

const emit = defineEmits(['read', 'delete']);

const hasNotifications = computed(() => props.notifications.length > 0);

const loadMoreTrigger = ref(null);

defineExpose({
    loadMoreTrigger,
});
</script>

<template>
    <div class="bg-white h-full">
        <div v-if="!hasLoaded" class="text-center py-8">読み込み中...</div>
        <!-- 通知がある場合 -->
        <div v-else-if="hasNotifications">
            <NotificationItem
                v-for="notification in notifications"
                :key="notification.id"
                :notification="notification"
                @read="$emit('read', $event)"
                @delete="$emit('delete', $event)"
            />
            <div ref="loadMoreTrigger" class="h-4"></div>

            <div
                v-if="isLoading && hasMore"
                class="text-center py-4 text-gray-500"
            >
                読み込み中...
            </div>

            <div
                v-if="!hasMore && notifications.length > 0"
                class="text-center py-4 text-gray-400"
            >
                すべての通知を表示しました
            </div>
        </div>

        <!-- 通知が無い場合 -->
        <div
            v-else
            class="flex items-center justify-center h-full text-gray-500"
        >
            <h3 class="text-base">お知らせはありません</h3>
        </div>
        <div class="pb-20"></div>
    </div>
</template>
