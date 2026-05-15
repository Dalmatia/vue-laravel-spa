import { watch } from 'vue';

export function useUserFollow(userId, followStore) {
    const fetchFollowData = async () => {
        if (!userId.value) return;

        try {
            await Promise.all([
                followStore.followList(userId.value),

                followStore.followerList(userId.value),

                followStore.followStatusCheck(userId.value),
            ]);
        } catch (error) {
            console.error('フォロー情報の取得に失敗しました:', error.message);
        }
    };

    const toggleFollow = async () => {
        try {
            if (followStore.followStatus(userId.value)) {
                await followStore.deleteFollow(userId.value);
            } else {
                await followStore.pushFollow(userId.value);
            }

            await fetchFollowData();
        } catch (error) {
            console.error('フォロー操作に失敗しました:', error);
        }
    };

    watch(
        () => userId.value,
        async (newId) => {
            if (!newId) return;

            await fetchFollowData();
        },
        {
            immediate: true,
        },
    );

    return {
        fetchFollowData,
        toggleFollow,
    };
}
