import { ref } from 'vue';
import axios from 'axios';

export const overlayState = ref({
    open: false,
    currentOutfit: null,
    commentOverlay: false,
});
export const errorMessage = ref('');

export function useOutfitOverlay() {
    // 投稿の詳細・コメントページを表示
    const toggleOutfitOverlay = (outfit = null, showComments = false) => {
        overlayState.value = {
            open: !!outfit,
            currentOutfit: outfit,
            commentOverlay: showComments,
        };
        window.dispatchEvent(
            new Event(outfit ? 'modal-opened' : 'modal-closed')
        );
    };

    const openOutfitById = async (id, showComments = false) => {
        try {
            const res = await axios.get(`/api/outfit/${id}`);
            toggleOutfitOverlay(res.data.outfit, showComments);
        } catch {
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
            overlayState.value.open = false;
            window.dispatchEvent(
                new CustomEvent('outfit-deleted', { detail: { id: object.id } })
            );
            if (onSuccess) onSuccess();
        } catch (e) {
            errorMessage.value = 'コーディネート削除に失敗しました';
            if (onError) onError(e);
        }
    };

    return {
        overlayState,
        toggleOutfitOverlay,
        openOutfitById,
        deleteOutfit,
    };
}
