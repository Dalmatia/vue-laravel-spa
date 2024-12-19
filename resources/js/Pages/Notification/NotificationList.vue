<script setup>
import { computed, defineProps, defineEmits } from 'vue';
import Clock from 'vue-material-design-icons/Clock.vue';

const props = defineProps({
    notifications: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['read', 'delete']);

const hasNotifications = computed(() => props.notifications.length > 0);
</script>

<template>
    <div class="bg-white h-full overflow-y-auto">
        <!-- 通知がある場合 -->
        <div v-if="hasNotifications">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="p-2 space-y-4"
            >
                <div
                    class="p-4 rounded-lg shadow-md cursor-pointer"
                    :class="{
                        'bg-gray-300': !notification.read_at,
                        'bg-white': notification.read_at,
                    }"
                >
                    <h4
                        class="font-semibold"
                        @click="$emit('read', notification)"
                    >
                        {{ notification.follower_name }}
                    </h4>
                    <p
                        class="text-sm text-gray-600"
                        @click="$emit('read', notification)"
                    >
                        {{ notification.message }}
                    </p>
                    <div
                        class="flex items-center space-x-1 text-sm text-gray-600"
                    >
                        <Clock :size="15" class="text-gray-400" />
                        <span>{{ notification.created_at }}</span>
                    </div>
                    <button
                        @click="$emit('delete', notification.id)"
                        class="text-gray-400 hover:underline"
                    >
                        削除
                    </button>
                </div>
            </div>
        </div>

        <!-- 通知が無い場合 -->
        <div
            v-else
            class="flex items-center justify-center h-full text-gray-500"
        >
            <h3 class="text-base">お知らせはありません</h3>
        </div>
    </div>
</template>
