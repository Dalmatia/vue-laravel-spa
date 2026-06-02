import { ref } from 'vue';

export function useItemOverlay() {
    const currentItem = ref(null);
    const openOverlay = ref(false);

    // アイテム詳細ページオーバーレイ表示
    const openItemOverlay = (item) => {
        currentItem.value = item;
        openOverlay.value = true;
    };

    const closeItemOverlay = () => {
        openOverlay.value = false;
    };

    return {
        currentItem,
        openOverlay,
        openItemOverlay,
        closeItemOverlay,
    };
}
