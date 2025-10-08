import { reactive, ref } from 'vue';
import axios from 'axios';

export function useOutfitOverlay() {
    const overlayState = reactive({
        open: false,
        currentOutfit: null,
        commentOverlay: false,
    });

    const errorMessage = ref('');

    // 投稿の詳細・コメントページを表示
    const toggleOutfitOverlay = (outfit = null, showComments = false) => {
        overlayState.open = !!outfit;
        overlayState.currentOutfit = outfit;
        overlayState.commentOverlay = showComments;
        window.dispatchEvent(
            new Event(outfit ? 'modal-opened' : 'modal-closed')
        );
    };

    const openOutfitById = async (id, showComments = false) => {
        try {
            const res = await axios.get(`/api/outfit/${id}`);
            toggleOutfitOverlay(res.data.outfit, showComments);
        } catch (e) {
            showError('投稿取得に失敗しました');
        }
    };

    // コーディネートの削除
    const deleteOutfit = async (object, onSuccess = null, onError = null) => {
        if (object.deleteType !== 'Outfit') {
            errorMessage.value = '無効な削除タイプです';
            return;
        }
        try {
            await axios.delete(`/api/outfit/${object.id}`);
            toggleOutfitOverlay(); // 閉じる
            window.dispatchEvent(
                new CustomEvent('outfit-deleted', { detail: { id: object.id } })
            );
            if (onSuccess) onSuccess();
        } catch (e) {
            errorMessage.value = 'コーディネート削除に失敗しました';
            if (onError) onError(e);
        }
    };

    const showError = (msg) => {
        errorMessage.value = msg;
        setTimeout(() => (errorMessage.value = ''), 3000);
    };

    return {
        overlayState,
        toggleOutfitOverlay,
        openOutfitById,
        deleteOutfit,
        errorMessage,
        showError,
    };
}
