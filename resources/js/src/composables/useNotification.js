import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export function useNotification() {
    const authStore = useAuthStore();
    const route = useRoute();
    const unreadCount = ref(0);

    const fetchNotifications = async () => {
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

    const setupWebSocket = () => {
        if (!authStore.user?.id) return;
        Echo.private(`user-notifications.${authStore.user.id}`).notification(
            () => {
                fetchNotifications();
            }
        );
    };

    onMounted(async () => {
        await authStore.fetchUserData();
        if (authStore.user?.id) {
            await fetchNotifications();
            setupWebSocket();
        }
    });

    watch(
        () => route.fullPath,
        () => {
            if (authStore.user?.id) {
                fetchNotifications();
            }
        }
    );

    return {
        unreadCount,
    };
}
