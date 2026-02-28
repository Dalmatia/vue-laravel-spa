import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useLayoutState } from './useLayoutState';
import { useNotification } from './useNotification';
import { useOutfitOverlay } from './useOutfitOverlay';

export function useNotificationActions(notifications) {
    const router = useRouter();
    const { noticeOpen } = useLayoutState();
    const { markAsRead } = useNotification();
    const {
        openOutfitById,
        overlayState,
        toggleOutfitOverlay,
        deleteOutfit,
        errorMessage,
        showError,
    } = useOutfitOverlay();
    const selectedNotification = ref(null);
    const openModal = ref(false);

    const notificationType = {
        'App\\Notifications\\FollowedUser': (n) => {
            router.push({ name: 'User', params: { id: n.follower_id } });
            noticeOpen.value = false;
        },
        'App\\Notifications\\OutfitLiked': async (n) => {
            await openOutfitDetails(n.outfit_id, false);
        },
        'App\\Notifications\\OutfitCommented': async (n) => {
            await openOutfitDetails(n.outfit_id, true);
        },
    };

    const handleNotificationAction = async (notification) => {
        if (notificationType[notification.type]) {
            await notificationType[notification.type](notification);
        }
        await markAsRead(notification);
    };

    // 通知削除モーダルを表示
    const showDeleteModal = (id) => {
        selectedNotification.value = id;
        openModal.value = true;
    };

    // 通知の削除
    const deleteNotification = async (id) => {
        try {
            await axios.delete(`/api/notifications/${id}`);
            notifications.value = notifications.value.filter(
                (n) => n.id !== id
            );
        } catch {
            showError('通知削除に失敗しました');
        }
    };

    const confirmDelete = async () => {
        if (!selectedNotification.value) return;
        await deleteNotification(selectedNotification.value);
        selectedNotification.value = null;
        openModal.value = false;
    };

    // 通知からオーバーレイを開く
    const openOutfitDetails = async (outfitId, showComments = false) => {
        try {
            await openOutfitById(outfitId, showComments);
        } catch (e) {
            showError('投稿の取得に失敗しました。');
        }
    };

    return {
        errorMessage,
        selectedNotification,
        openModal,
        handleNotificationAction,
        showDeleteModal,
        deleteNotification,
        confirmDelete,
        openOutfitDetails,
        overlayState,
        toggleOutfitOverlay,
        deleteOutfit,
        showError,
    };
}
