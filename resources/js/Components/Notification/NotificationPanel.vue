<script setup>
import { ref } from 'vue';
import NotificationList from '@/Components/Notification/NotificationList.vue';
import Close from 'vue-material-design-icons/Close.vue';

const props = defineProps({
    notifications: Array,
    onRead: Function,
    onDelete: Function,
    onClose: Function,
});

const scrollContainer = ref(null);
const notificationList = ref(null);

defineExpose({
    scrollContainer,
    notificationList,
});
</script>

<template>
    <div
        v-if="notifications"
        ref="scrollContainer"
        class="fixed top-0 left-[80px] xl:left-64 z-20 w-full md:w-[397px] h-full bg-slate-100 shadow-md rounded-r-2xl border-r transition-transform duration-300 overflow-auto hidden-scrollbar"
    >
        <div
            class="flex items-center justify-between px-6 py-4 border-b bg-white"
        >
            <span class="text-lg font-bold">お知らせ</span>
            <button
                @click.stop="onClose"
                class="text-gray-600 hover:text-gray-900"
            >
                <Close :size="33" class="cursor-pointer" />
            </button>
        </div>

        <NotificationList
            ref="notificationList"
            :notifications="notifications"
            @read="onRead"
            @delete="onDelete"
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
