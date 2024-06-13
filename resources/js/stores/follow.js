import { defineStore } from 'pinia';
import axios from 'axios';

export const useFollowStore = defineStore('follow', {
    state: () => ({
        status: {},
        followUsers: [],
        followerUsers: [],
    }),
    getters: {
        followStatus: (state) => (userId) => state.status[userId],
    },
    actions: {
        // 各ユーザーのフォロー状態を確認する関数
        async followStatusCheck(userId) {
            try {
                const response = await axios.get(
                    `/api/follow/status/${userId}`
                );
                this.status[userId] = response.data.is_followed;
            } catch (error) {
                console.log(error);
            }
        },
        // フォロー状態の取得
        async fetchFollowStatus(follows) {
            try {
                const responses = await Promise.all(
                    follows.map((userId) =>
                        axios.get(`/api/follow/status/${userId}`)
                    )
                );
                responses.forEach((response, index) => {
                    this.status[follows[index]] = response.data.is_followed;
                });
            } catch (error) {
                console.error(error);
            }
        },
        // フォロー
        async pushFollow(userId) {
            try {
                const response = await axios.post(`/api/follow/${userId}`);
                if (response.data.success) {
                    this.status[userId] = true;
                }
            } catch (error) {
                console.log(error);
            }
        },
        // フォロー解除
        async deleteFollow(userId) {
            try {
                const response = await axios.delete(`/api/follow/${userId}`);
                if (response.data.success) {
                    this.status[userId] = false;
                }
            } catch (error) {
                console.log(error);
            }
        },
        async followList(userId) {
            try {
                const response = await axios.get(
                    `/api/users/${userId}/follow_list`
                );
                this.followUsers = response.data.follow_list;
                const followIds = this.followUsers.map((user) => user.id);
                await this.fetchFollowStatus(followIds); // フォローリスト取得後にフォロー状態を取得
            } catch (error) {
                console.log(error);
            }
        },
        async followerList(userId) {
            try {
                const response = await axios.get(
                    `/api/users/${userId}/follower_list`
                );
                this.followerUsers = response.data.follower_list;
            } catch (error) {
                console.log(error);
            }
        },
    },
});
