import { ref } from 'vue';

export function useOutfitComments() {
    const isLoading = ref(true);
    const comments = ref([]);

    // コメントを取得
    const fetchComments = async (outfitId) => {
        try {
            const commentsResponse = await axios.get('/api/comments', {
                params: { outfit_id: outfitId },
            });
            const rawComments = commentsResponse.data.comments;

            // 重複しないuser_idの抽出
            const userIds = [
                ...new Set(rawComments.map((comment) => comment.user_id)),
            ];

            // 各ユーザー情報の取得
            const userResponses = await Promise.all(
                userIds.map((id) => axios.get(`/api/users/${id}`))
            );
            const users = userResponses.map((res) => res.data.user);

            // コメントにユーザー情報を結合
            rawComments.forEach((comment) => {
                comment.user = users.find(
                    (user) => user.id === comment.user_id
                );
            });

            comments.value = rawComments;
        } catch (error) {
            console.error('コメントの取得に失敗しました:', error);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        comments,
        isLoading,
        fetchComments,
    };
}
