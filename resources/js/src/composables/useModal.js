import { ref } from 'vue';

export function useModal() {
    const isOpen = ref(false);
    const showItemSelectionModal = ref(false);
    const selectedType = ref(null);

    const openModal = (itemType) => {
        // コーディネートに使用したアイテムのカテゴリーを設定
        selectedType.value = itemType;
        // アイテム選択モーダルを表示
        showItemSelectionModal.value = true;
    };

    const toggleAccordion = () => {
        isOpen.value = !isOpen.value;
    };

    return {
        isOpen,
        showItemSelectionModal,
        selectedType,
        openModal,
        toggleAccordion,
    };
}
