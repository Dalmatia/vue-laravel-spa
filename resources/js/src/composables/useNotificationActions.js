import { ref } from 'vue';
import axios from 'axios';
import { useOutfitOverlay } from './useOutfitOverlay';

export function useNotificationActions(notifications) {
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
