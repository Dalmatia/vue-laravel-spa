<script setup>
import { onMounted, onUnmounted, reactive, watch } from 'vue';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';

import NotificationList from './NotificationList.vue';
import NotificationOptions from './NotificationOptions.vue';
import ShowOutfitOverlay from '@/Components/Outfits/ShowOutfitOverlay.vue';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close-notice']);
const props = defineProps(['user']);
const state = reactive({
    notifications: [],
    selectedNotification: null,
    isMobile: window.innerWidth <= 640,
    openModal: false,
    errorMessage: '',
    currentPage: 1,
    hasMore: true,
    isLoading: false,
    openOverlay: false,
    currentOutfit: null,
    commentOverlay: false,
});
const router = useRouter();
const channel = Echo.private('user-notifications.' + props.user.id);
const notificationType = {
    'App\\Notifications\\FollowedUser': (notification) => {
        router.push({ name: 'User', params: { id: notification.follower_id } });
        emit('close-notice');
    },
    'App\\Notifications\\OutfitLiked': async (notification) => {
        const response = await axios.get(
            `/api/outfit/${notification.outfit_id}`
        );
        toggleOutfitOverlay(response.data.outfit);
    },
    'App\\Notifications\\OutfitCommented': async (notification) => {
        const response = await axios.get(
            `/api/outfit/${notification.outfit_id}`
        );
        toggleOutfitOverlay(response.data.outfit, true);
    },
};

// スマートフォン画面ではない時、ホーム画面に戻る
const handleResize = debounce(() => {
    const mobile = window.innerWidth <= 640;
    if (
        !mobile &&
        state.isMobile &&
        router.currentRoute.value.name === 'Notifications'
    ) {
        // モバイルからデスクトップに切り替わったとき
        router.push({ name: 'Home' });
    }
    state.isMobile = mobile; // 現在の状態を更新
}, 200);

const closeNotification = () => {
    state.isMobile ? router.back() : emit('close-notice');
};

// リアルタイムで通知を受信
let reconnectAttempts = 0;
const setupWebSocket = () => {
    const connect = () => {
        channel
            .listen(
                '.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
                (notification) => {
                    state.notifications.unshift(notification);
                }
            )
            .error(() => {
                reconnectAttempts++;
                if (reconnectAttempts <= 5) {
                    setTimeout(connect, reconnectAttempts * 1000);
                } else {
                    showError(
                        'リアルタイム更新に失敗しました。再試行を終了します。'
                    );
                }
            });
    };
    connect();
};

const showError = (message) => {
    state.errorMessage = message;
    setTimeout(() => {
        state.errorMessage = '';
    }, 3000);
};

const fetchNotifications = async () => {
    if (!state.hasMore || state.isLoading) return;
    state.isLoading = true;
    try {
        const response = await axios.get(
            `/api/notifications/${props.user.id}?page=${state.currentPage}`
        );
        if (response.data && response.data.notifications) {
            state.notifications = [
                ...state.notifications,
                ...response.data.notifications,
            ];
            state.hasMore = response.data.hasMore;
            state.currentPage++;
        } else {
            showError('通知の読み込みに失敗しました。');
        }
    } catch {
        showError('通知の読み込みに失敗しました。');
    } finally {
        state.isLoading = false;
    }
};

const markAsRead = async (notification) => {
    try {
        // 通知を既読にする
        await axios.post(`/api/notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();

        // 通知の種類を判定
        if (notificationType[notification.type]) {
            await notificationType[notification.type](notification);
        }
    } catch (error) {
        // エラー表示
        showError('問題が発生しました。');
        console.error(error); // デバッグ用
    }
};

const deleteNotification = () => {
    if (state.selectedNotification === null) return;
    axios
        .delete(`/api/notifications/${state.selectedNotification}`)
        .then(() => {
            state.notifications = state.notifications.filter(
                (n) => n.id !== state.selectedNotification
            );
            state.selectedNotification = null;
            state.openModal = false;
        })
        .catch(() => showError('通知の削除に失敗しました。'));
};

// コーディネートの削除
const deleteOutfit = (object) => {
    let url = '';
    if (object.deleteType === 'Outfit') {
        url = `/api/outfit/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                state.openOverlay = false;
                window.dispatchEvent(new Event('outfit-deleted'));
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

const showDeleteModal = (id) => {
    state.selectedNotification = id;
    state.openModal = true;
};

const toggleOutfitOverlay = (outfit = null, showComments = false) => {
    state.currentOutfit = outfit;
    state.openOverlay = !!outfit;
    state.commentOverlay = showComments;
    const event = outfit ? 'modal-opened' : 'modal-closed';
    window.dispatchEvent(new Event(event));
};

watch(
    () => state.isMobile,
    (newValue, oldValue) => {
        if (!newValue && oldValue) {
            // モバイルからデスクトップに切り替わった場合
            fetchNotifications();
        }
    }
);

onMounted(() => {
    if (props.user && props.user.id) {
        setupWebSocket();
        fetchNotifications();
    }
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    channel.stopListening(
        '.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated'
    );
});
</script>

<template>
    <div>
        <div
            v-if="state.errorMessage"
            class="fixed top-0 left-0 w-full z-50 bg-red-500 text-white p-2 text-center text-sm sm:text-base break-words"
        >
            {{ state.errorMessage }}
        </div>
        <div v-if="state.isMobile">
            <!-- モバイルレイアウト -->
            <div class="fixed inset-0 z-20 bg-white overflow-y-auto">
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

                <NotificationList
                    :notifications="state.notifications"
                    @read="markAsRead"
                    @delete="showDeleteModal"
                />
            </div>
            <ShowOutfitOverlay
                v-if="state.openOverlay"
                :outfit="state.currentOutfit"
                :commentOverlay="state.commentOverlay"
                @delete-selected="deleteOutfit($event)"
                @close-overlay="toggleOutfitOverlay()"
            />
        </div>

        <div
            v-if="!state.isMobile"
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
            <NotificationList
                :notifications="state.notifications"
                @read="markAsRead"
                @delete="showDeleteModal"
            />
            <ShowOutfitOverlay
                v-if="state.openOverlay"
                :outfit="state.currentOutfit"
                :commentOverlay="state.commentOverlay"
                @delete-selected="deleteOutfit($event)"
                @close-overlay="toggleOutfitOverlay()"
            />
        </div>
    </div>
    <NotificationOptions
        v-if="state.openModal"
        @delete-selected="deleteNotification()"
        @close="state.openModal = false"
    />
</template>
