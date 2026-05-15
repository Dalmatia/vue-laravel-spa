import { ref, watch } from 'vue';

export function useUserProfile(userId) {
    const user = ref(null);
    const username = ref('');
    const outfitCount = ref(0);

    let prevUserId = null;

    // ===== ユーザープロフィール取得 =====
    const fetchUserProfile = async () => {
        try {
            const response = await axios.get(
                `/api/users/${userId.value}?page=0`,
            );

            user.value = response.data.user;
            username.value = response.data.user.name;
            outfitCount.value = response.data.outfit_count;
        } catch (error) {
            console.error('ユーザー情報の取得に失敗しました:', error);
        }
    };

    watch(
        () => userId.value,
        async (newId) => {
            if (newId && newId !== prevUserId) {
                prevUserId = newId;

                await fetchUserProfile();
            }
        },
        {
            immediate: true,
        },
    );

    return {
        user,
        username,
        outfitCount,

        fetchUserProfile,
    };
}
