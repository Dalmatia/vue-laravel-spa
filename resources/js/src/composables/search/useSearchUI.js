import { ref } from 'vue';

export function useSearchUI(applyFilters, clearFilters) {
    const openFilter = ref(false);
    const openModal = ref(false);
    const isGenderModalOpen = ref(false);

    const openColorModal = () => {
        openModal.value = true;
    };

    const handleFilterByCategory = async () => {
        openFilter.value = false;
        await applyFilters();
    };

    const handleClearFilters = async () => {
        clearFilters();
        openFilter.value = false;
        await applyFilters();
    };

    return {
        openFilter,
        openModal,
        isGenderModalOpen,
        openColorModal,
        handleFilterByCategory,
        handleClearFilters,
    };
}
