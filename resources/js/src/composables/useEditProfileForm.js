import { ref, computed, nextTick } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useEnumStore } from '@/stores/enum';
import { useFileUploader } from './useFileUploader';

export function useEditProfileForm() {
    const authStore = useAuthStore();
    const router = useRouter();
    const enumStore = useEnumStore();

    // 入力状態
    const selectedFile = ref(null);
    // 性別一覧の取得
    const { genders } = enumStore;
    const { fileDisplay, isValidFile, getUploadedImage } = useFileUploader();

    // ファイルアップロード
    const profileImageChange = (e) => {
        getUploadedImage(e, (file) => {
            selectedFile.value = file;
        });
    };

    // プロフィール更新
    const updateProfile = async () => {
        const formData = new FormData();
        formData.append('name', authStore.user.name);
        formData.append('email', authStore.user.email);

        if (selectedFile.value) {
            formData.append('file', selectedFile.value);
        }
        formData.append('gender', authStore.user.gender);
        formData.append('birthdate', authStore.user.birthdate || '');

        try {
            const response = await axios.post(
                `/api/user/${authStore.user.id}/update`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );

            if (response.status === 200) {
                await authStore.fetchUserData();
                window.dispatchEvent(new Event('profile-updated'));
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

    return {
        authStore,
        genders,
        age,
        fileDisplay,
        isValidFile,
        updateProfile,
        profileImageChange,
    };
}
