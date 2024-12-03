<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close-notice']);
const props = defineProps(['user']);
let notifications = ref([]);
const router = useRouter();
const BREAKPOINT_MOBILE = 640;
const isMobile = ref(window.innerWidth <= BREAKPOINT_MOBILE);

// スマートフォン画面ではない時、ホーム画面に戻る
const handleResize = () => {
    const mobile = window.innerWidth <= BREAKPOINT_MOBILE;
    if (
        !mobile &&
        isMobile.value &&
        router.currentRoute.value.name === 'Notifications'
    ) {
        // モバイルからデスクトップに切り替わったとき
        router.push({ name: 'Home' });
    }
    isMobile.value = mobile; // 現在の状態を更新
};

const closeNotification = () => {
    if (isMobile.value) {
        router.back();
    } else {
        emit('close-notice');
    }
};

const setupWebSocket = () => {
    Echo.private('user-notifications.' + props.user.id).notification(
        (notification) => {
            notifications.value.unshift({
                id: new Date().getTime(),
                message: notification.message,
                follower_name: notification.follower_name,
            });
        }
    );
};

watch(
    () => props.user,
    (newUser) => {
        if (newUser && newUser.id) {
            setupWebSocket();
        }
    },
    { immediate: true }
);

onMounted(() => {
    handleResize();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <!-- モバイルレイアウト -->
    <div v-if="isMobile" class="fixed inset-0 z-20 bg-white overflow-y-auto">
        <!-- ヘッダー -->
        <div
            class="flex items-center sticky top-0 justify-between px-4 py-4 border-b bg-white"
        >
            <button
                @click="closeNotification()"
                class="text-gray-600 hover:text-gray-900"
            >
                <ArrowLeft :size="24" class="cursor-pointer" />
            </button>
            <span class="text-lg font-bold">お知らせ</span>
            <div class="w-6"></div>
            <!-- Closeボタンのサイズ調整 -->
        </div>

        <!-- 通知内容 -->
        <div class="h-full">
            <div v-if="notifications.length > 0">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="p-2 space-y-4"
                >
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h4 class="font-semibold">
                            {{ notification.follower_name }}
                        </h4>
                        <p class="text-sm text-gray-600">
                            {{ notification.message }}
                        </p>
                    </div>
                    <!-- 繰り返し表示 -->
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
    </div>

    <transition name="slide" v-else>
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
                <div v-if="notifications.length > 0">
                    <!-- 通知がある場合 -->
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="p-2 space-y-4"
                    >
                        <!-- 通知アイテムの例 -->
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h4 class="font-semibold">
                                {{ notification.follower_name }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                {{ notification.message }}
                            </p>
                        </div>
                        <!-- 通知アイテムの繰り返し -->
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
