import { onMounted, onUnmounted } from 'vue';

export function useItemEvents(callback) {
    onMounted(() => {
        window.addEventListener('item-registered', callback);
        window.addEventListener('item-updated', callback);
    });

    onUnmounted(() => {
        window.removeEventListener('item-registered', callback);
        window.removeEventListener('item-updated', callback);
    });
}
