import { ref, onMounted, onUnmounted } from 'vue';

export function useResponsive() {
    const isMobile = ref(window.innerWidth < 768);
    let timeout = null;

    const handleResize = () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            isMobile.value = window.innerWidth < 768;
        }, 100);
    };

    onMounted(() => {
        window.addEventListener('resize', handleResize);
        handleResize();
    });

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
        if (timeout) clearTimeout(timeout);
    });

    return { isMobile };
}
