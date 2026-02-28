import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const noticeOpen = ref(false);

export function useLayoutState() {
    const authStore = useAuthStore();
    const router = useRouter();
    const isDropdownOpen = ref(false);
    const account = ref(null);
    const notifications = ref(null);

    const isMobile = ref(window.innerWidth <= 640);

    const handleResize = () => {
        const wasMobile = isMobile.value;
        isMobile.value = window.innerWidth <= 640;

        // モバイルになった瞬間に通知パネルを閉じる
        if (!wasMobile && isMobile.value) {
            noticeOpen.value = false;
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
        if (account.value && !account.value.contains(event.target)) {
            isDropdownOpen.value = false;
        }
    };

    const logout = async () => {
        await authStore.logout();
        router.push({ name: 'Login' });
        if (authStore.user?.id) {
            Echo.stopListening(`user-notifications.${authStore.user.id}`);
        }
    };

    onMounted(() => {
        window.addEventListener('resize', handleResize);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
    });

    return {
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
