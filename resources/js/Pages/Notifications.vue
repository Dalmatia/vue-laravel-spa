<script setup>
import { ref } from 'vue';

import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close-notice']);
const hasNotifications = ref(false);

const closeNotification = () => {
    emit('close-notice');
};
</script>

<template>
    <transition name="slide">
        <div
            @click.stop
            class="absolute top-0 left-[73px] xl:left-60 z-0 w-full md:w-[397px] h-full bg-slate-100 shadow-md rounded-r-2xl border-r transition-transform duration-300"
        >
            <!-- 通知ページヘッダー -->
            <div
                class="flex items-center justify-between px-6 py-4 border-b bg-white"
            >
                <span class="text-lg font-bold">お知らせ</span>
                <button
                    @click.stop="closeNotification()"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <Close :size="33" class="cursor-pointer" />
                </button>
            </div>

            <!-- 通知内容 -->
            <div class="bg-white h-[calc(100vh-4rem)] overflow-y-auto">
                <!-- 通知がある場合 -->
                <div v-if="hasNotifications" class="p-4 space-y-4">
                    <!-- 通知アイテムの例 -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h4 class="font-semibold">通知タイトル</h4>
                        <p class="text-sm text-gray-600">
                            通知の内容がここに表示されます。
                        </p>
                    </div>
                    <!-- 通知アイテムの繰り返し -->
                </div>

                <!-- 通知が無い場合 -->
                <div
                    v-else
                    class="flex items-center justify-center h-full text-gray-500"
                >
                    <h3 class="text-base">お知らせはありません</h3>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(-100%);
}
.slide-enter-to {
    transform: translateX(0);
}
.slide-leave-from {
    transform: translateX(0);
}
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
