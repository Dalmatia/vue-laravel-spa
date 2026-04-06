import { onMounted, onUnmounted } from 'vue';

export function useOutfitEvents(applyFilters) {
    const handleDeleted = () => {
        applyFilters();
    };

    onMounted(() => {
        window.addEventListener('outfit-created', applyFilters);
        window.addEventListener('outfit-updated', applyFilters);
        window.addEventListener('outfit-deleted', handleDeleted);
    });

    onUnmounted(() => {
        window.removeEventListener('outfit-created', applyFilters);
        window.removeEventListener('outfit-updated', applyFilters);
        window.removeEventListener('outfit-deleted', handleDeleted);
    });
}
