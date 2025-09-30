import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
export const unreadCount = ref(0);

export function useNotification() {
    const authStore = useAuthStore();
    const route = useRoute();
    const notifications = ref([]);
    const isLoading = ref(false);
    const currentPage = ref(1);
    const hasMore = ref(true);
    let channel = null;

    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get(
                `/api/notifications/${authStore.user.id}/unread_count`
            );
            unreadCount.value = response.data.unread_count;
        } catch (error) {
            console.error('通知の取得に失敗しました:', error);
            unreadCount.value = 0;
        }
    };

    // 通知取得
    const fetchNotifications = async () => {
        if (!authStore.user?.id || isLoading.value || !hasMore.value) return;
        isLoading.value = true;
        try {
            const response = await axios.get(
                `/api/notifications/${authStore.user.id}?page=${currentPage.value}`
            );
            const newNotifications = response.data.notifications || [];

            // 投稿画像をセット
            await Promise.all(
                newNotifications.map(async (n) => {
                    if (n.outfit_id) {
                        try {
                            const res = await axios.get(
                                `/api/outfit/${n.outfit_id}`
                            );
                            n.outfit_image = res.data.outfit.file;
                        } catch (e) {
                            console.error('投稿情報取得失敗:', e);
                        }
                    }
                })
            );
            notifications.value.push(...newNotifications);
            hasMore.value = response.data.hasMore ?? false;
            currentPage.value++;
        } catch (e) {
            console.error('通知取得エラー:', e);
        } finally {
            isLoading.value = false;
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
                            `/api/outfit/${notification.outfit_id}`
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
            }
        );
        return channel;
    };

    const stopListening = () => {
        if (!channel) return;
        Echo.leave(`user-notifications.${authStore.user.id}`);
        channel = null;
    };

    onMounted(async () => {
        await authStore.fetchUserData();
        if (authStore.user?.id) {
            await fetchUnreadCount();
            listenNotifications();
        }
    });

    watch(
        () => route.fullPath,
        () => {
            if (authStore.user?.id) {
                fetchUnreadCount();
            }
        }
    );

    return {
        unreadCount,
        notifications,
        isLoading,
        currentPage,
        hasMore,
        channel,
        fetchNotifications,
        markAsRead,
        listenNotifications,
        stopListening,
    };
}
