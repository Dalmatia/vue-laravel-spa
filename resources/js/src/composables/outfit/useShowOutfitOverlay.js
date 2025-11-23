import { ref, onMounted, onUnmounted } from 'vue';
import { useOutfitDetail } from './useOutfitDetail';
import { useOutfitItems } from './useOutfitItems';
import { useOutfitComments } from './useOutfitComments';
import { useFollowStore } from '../../../stores/follow';

export function useShowOutfitOverlay(initialOutfit, initialCommentOverlay) {
    const outfit = ref(initialOutfit);
    const commentOverlayState = ref(initialCommentOverlay ?? false);

    // 各種データ
    const postsByUser = ref([]);
    const outfitItems = ref([]);
    const comments = ref([]);
    const season = ref(null);

    // 状態管理
    const isLoading = ref(false);
    const deleteType = ref(null);
    const selectedId = ref(null);

    // composables 呼び出し
    const { fetchOutfitDetail } = useOutfitDetail();
    const { fetchOutfitItems } = useOutfitItems();
    const { fetchOutfitComments } = useOutfitComments();
    const { followStatusCheck, fetchFollowStatus, pushFollow, deleteFollow } =
        useFollowStore();

    // Event Bus
    const bus = useEventBus('outfit-events');

    /**
     * Overlay のデータをまとめて再取得
     */
    const reloadAll = async () => {
        if (!outfit.value?.id) return;

        isLoading.value = true;

        try {
            const [detail, items, cmt] = await Promise.all([
                fetchOutfitDetail(outfit.value.id),
                fetchOutfitItems(outfit.value.id),
                fetchOutfitComments(outfit.value.id),
            ]);

            outfit.value = detail.outfit;
            postsByUser.value = detail.postsByUser;
            season.value = detail.season;

            outfitItems.value = items;
            comments.value = cmt;

            await fetchFollowStatus(outfit.value.user_id);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * コメントオーバーレイを開く
     */
    const openCommentOverlay = () => {
        commentOverlayState.value = true;
    };

    // イベントを購読
    const handleOutfitUpdated = () => reloadAll();
    const handleCommentPosted = () => reloadAll();

    onMounted(() => {
        reloadAll();
        window.addEventListener('outfit-updated', handleOutfitUpdated);
        window.addEventListener('comment-posted', handleCommentPosted);
    });

    onUnmounted(() => {
        unsubscribe();
        window.removeEventListener('outfit-updated', handleOutfitUpdated);
        window.removeEventListener('comment-posted', handleCommentPosted);
    });

    return {
        outfit,
        postsByUser,
        outfitItems,
        season,
        comments,
        isLoading,

        commentOverlayState,

        // 削除モーダル用
        deleteType,
        selectedId,

        // メソッド
        followStatusCheck,
        pushFollow,
        deleteFollow,
        openCommentOverlay,
    };
}
