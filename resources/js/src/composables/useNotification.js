import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useErrorMessage } from './useErrorMessage';

export const unreadCount = ref(0);
const notifications = ref([]);
const isLoading = ref(false);
const hasLoaded = ref(false);
const currentPage = ref(1);
const hasMore = ref(true);
let channel = null;
const initialized = ref(false);

export function useNotification() {
    const authStore = useAuthStore();
    const { errorMessage, showError } = useErrorMessage();

    // 未読数を取得
    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get(
                `/api/notifications/${authStore.user.id}/unread_count`,
            );
            unreadCount.value = response.data.unread_count;
        } catch (error) {
            console.error('未読通知の取得に失敗しました:', error);
            showError('未読通知の取得に失敗しました');
            unreadCount.value = 0;
        }
    };

    // 通知取得
    const fetchNotifications = async () => {
        if (!authStore.user?.id || isLoading.value || !hasMore.value) return;
        isLoading.value = true;
        try {
            const response = await axios.get(
                `/api/notifications/${authStore.user.id}?page=${currentPage.value}`,
            );
            const newNotifications = response.data.data || [];

            // 投稿画像をセット
            await Promise.all(
                newNotifications.map(async (n) => {
                    if (n.outfit_id) {
                        try {
                            const res = await axios.get(
                                `/api/outfit/${n.outfit_id}`,
                            );
                            n.outfit_image = res.data.outfit.file;
                        } catch (e) {
                            console.error('投稿情報取得失敗:', e);
                        }
                    }
                }),
            );
            notifications.value.push(...newNotifications);
            hasMore.value = response.data.has_more;
            currentPage.value++;
        } catch (e) {
            console.error('通知取得エラー:', e);
            showError('通知の取得に失敗しました');
        } finally {
            isLoading.value = false;
            hasLoaded.value = true;
        }
    };

    // 既読処理
    const markAsRead = async (notification) => {
        try {
            await axios.post(`/api/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(unreadCount.value - 1, 0);
        } catch (e) {
            console.error('通知既読エラー:', e);
            showError('通知の既読処理に失敗しました');
        }
    };

    const handleOutfitDeleted = async (e) => {
        const deletedId = e.detail?.id;
        if (!deletedId) return;

        const beforeCount = notifications.value.length;
        notifications.value = notifications.value.filter(
            (n) => n.outfit_id !== deletedId,
        );
        const afterCount = notifications.value.length;

        // 未読数を調整
        const removed = beforeCount - afterCount;
        if (removed > 0) {
            unreadCount.value = Math.max(unreadCount.value - removed, 0);
        }
    };

    // WebSocket受信
    const listenNotifications = () => {
        if (!authStore.user?.id) return;
        channel = Echo.private(`user-notifications.${authStore.user.id}`);
        channel.listen(
            '.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
            async (notification) => {
                // 投稿画像をセット
                if (notification.outfit_id) {
                    try {
                        const res = await axios.get(
                            `/api/outfit/${notification.outfit_id}`,
                        );
                        notification.outfit_image = res.data.outfit.file;
                    } catch (e) {
                        console.error('投稿情報取得失敗:', e);
                    }
                }

                notifications.value.unshift(notification);

                // 未読数
                unreadCount.value =
                    notification.unread_count ?? unreadCount.value + 1;
            },
        );
        return channel;
    };

    const stopListening = () => {
        if (!channel) return;
        Echo.leave(`user-notifications.${authStore.user.id}`);
        channel = null;
        initialized.value = false;
    };

    const resetNotificationState = () => {
        unreadCount.value = 0;
        notifications.value = [];
        currentPage.value = 1;
        hasMore.value = true;
        hasLoaded.value = false;
        initialized.value = false;
    };

    watch(
        () => authStore.user?.id,
        async (userId) => {
            if (!userId || initialized.value) return;

            await fetchUnreadCount();
            listenNotifications();

            initialized.value = true;
        },
        { immediate: true },
    );

    onMounted(async () => {
        window.addEventListener('outfit-deleted', handleOutfitDeleted);
    });

    onUnmounted(() => {
        window.removeEventListener('outfit-deleted', handleOutfitDeleted);
    });

    return {
        unreadCount,
        notifications,
        isLoading,
        hasLoaded,
        currentPage,
        hasMore,
        errorMessage,
        channel,
        fetchNotifications,
        markAsRead,
        listenNotifications,
        stopListening,
        resetNotificationState,
    };
}
