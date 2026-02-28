import { ref, computed, nextTick, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useFileUploader } from './useFileUploader';

export function useEditProfileForm() {
    const authStore = useAuthStore();
    const router = useRouter();

    // 入力状態
    const genders = ref([]);
    const { fileDisplay, isValidFile, getUploadedImage } = useFileUploader();

    // ユーザー情報取得
    const fetchUserData = async () => {
        try {
            await authStore.fetchUserData();
        } catch (error) {
            if (error.response?.status === 401) {
                handleUnauthorized();
            }
        }
    };

    // 認証切れ時
    const handleUnauthorized = () => {
        authStore.logout();
        router.push({ name: 'Login' });
    };

    // 性別一覧の取得
    const fetchGenders = async () => {
        try {
            const response = await axios.get('/api/get_genders');
            genders.value = response.data;
        } catch (error) {
            console.error('性別一覧の取得に失敗しました:', error);
        }
    };

    // ファイルアップロード
    const profileImageChange = (e) => {
        getUploadedImage(e, (file) => {
            authStore.user.file = file;
        });
    };

    // プロフィール更新
    const updateProfile = async () => {
        const formData = new FormData();
        formData.append('name', authStore.user.name);
        formData.append('email', authStore.user.email);

        if (authStore.user.file instanceof File) {
            formData.append('file', authStore.user.file);
        }
        formData.append('gender', authStore.user.gender);
        formData.append('birthdate', authStore.user.birthdate || '');

        try {
            const response = await axios.post(
                `/api/user/${authStore.user.id}/update`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            );

            if (response.status === 200) {
                window.dispatchEvent(new Event('profile-updated'));
                await fetchUserData();
                await nextTick();
                router.push({
                    name: 'User',
                    params: { id: authStore.user.id },
                });
            }
        } catch (error) {
            if (error.response?.status === 422) {
                console.log('Validation Errors:', error.response.data.errors);
            } else {
                console.error('プロフィール更新エラー:', error);
            }
        }
    };

    // 年齢計算
    const age = computed(() => {
        if (!authStore.user?.birthdate) return null;
        const today = new Date();
        const birth = new Date(authStore.user.birthdate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    });

    // 初期化
    onMounted(() => {
        fetchUserData();
        fetchGenders();
    });

    return {
        authStore,
        genders,
        age,
        fileDisplay,
        isValidFile,
        updateProfile,
        fetchUserData,
        fetchGenders,
        profileImageChange,
    };
}
