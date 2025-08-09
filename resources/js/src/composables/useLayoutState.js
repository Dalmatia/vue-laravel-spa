import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

export function useLayoutState() {
    const authStore = useAuthStore();
    const router = useRouter();
    const sideNavZIndex = ref(10);
    const isDropdownOpen = ref(false);
    const noticeOpen = ref(false);
    const account = ref(null);
    const notifications = ref(null);

    const isMobile = ref(window.innerWidth <= 640);

    const handleResize = () => {
        isMobile.value = window.innerWidth <= 640;
    };

    const handleModalEvents = (event) => {
        if (event.type === 'modal-opened') {
            sideNavZIndex.value = 0;
        } else if (event.type === 'modal-closed') {
            sideNavZIndex.value = 10;
        }
    };

    const toggleMenu = (dropdownType, event) => {
        if (event) event.stopPropagation();
        if (dropdownType === 'account') {
            isDropdownOpen.value = !isDropdownOpen.value;
        }
        if (dropdownType === 'notifications') {
            noticeOpen.value = !noticeOpen.value;
        }
    };

    const closeMenu = (event) => {
        if (
            (account.value && !account.value.contains(event.target)) ||
            (notifications.value && !notifications.value.contains(event.target))
        ) {
            isDropdownOpen.value = false;
            noticeOpen.value = false;
        }
    };

    const logout = async () => {
        await authStore.logout();
        router.push({ name: 'login' });
        if (authStore.user?.id) {
            Echo.stopListening(`user-notifications.${authStore.user.id}`);
        }
    };

    onMounted(() => {
        window.addEventListener('resize', handleResize);
        window.addEventListener('modal-opened', handleModalEvents);
        window.addEventListener('modal-closed', handleModalEvents);
        window.addEventListener('outfit-deleted', handleModalEvents);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
        window.removeEventListener('modal-opened', handleModalEvents);
        window.removeEventListener('modal-closed', handleModalEvents);
        window.removeEventListener('outfit-deleted', handleModalEvents);
    });

    return {
        sideNavZIndex,
        isDropdownOpen,
        noticeOpen,
        account,
        notifications,
        isMobile,
        toggleMenu,
        closeMenu,
        logout,
    };
}
