<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useFollowStore } from '../stores/follow';

const authStore = useAuthStore();
const followStore = useFollowStore();
const suggestionUsers = ref([]);

const fetchSuggestionUsers = async (limit = 5) => {
    try {
        const response = await axios.get('/api/suggestion_users', {
            params: { limit },
        });
        suggestionUsers.value = response.data.users;
    } catch (error) {
        console.error('おすすめユーザー取得時にエラーが発生しました。', error);
    }
};

// フォロー中かどうかを確認
const isFollowing = (userId) => followStore.followStatus(userId);
const isNotFollowing = (userId) => !followStore.followStatus(userId);

// フォローする
const follow = async (userId) => {
    try {
        await followStore.pushFollow(userId);
        followStore.status[userId] = true;
    } catch (error) {
        console.error(error);
    }
};

// フォロー解除
const deleteFollow = async (userId) => {
    try {
        await followStore.deleteFollow(userId);
        followStore.status[userId] = false;
    } catch (error) {
        console.error(error);
    }
};

watch(
    () => authStore.user,
    (newUser) => {
        if (newUser) {
            fetchSuggestionUsers();
        } else {
            suggestionUsers.value = [];
        }
    },
    { immediate: true }
);
</script>

<template>
    <div
        id="SuggestionsSection"
        class="lg:w-4/12 lg:block hidden text-black mt-10"
    >
        <div
            class="flex items-center justify-between max-w-[300px]"
            v-if="authStore.user"
        >
            <div class="flex items-center">
                <router-link
                    :to="{ name: 'User', params: { id: authStore.user.id } }"
                >
                    <img
                        class="rounded-full z-10 w-[58px] h-[58px]"
                        :src="authStore.user.file"
                    />
                </router-link>
                <div class="pl-4">
                    <router-link
                        class="text-black font-extrabold"
                        :to="{
                            name: 'User',
                            params: { id: authStore.user.id },
                        }"
                    >
                        {{ authStore.user.name }}
                    </router-link>
                </div>
            </div>
            <button
                class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
            >
                切り替え
            </button>
        </div>

        <div class="max-w-[300px] flex items-center justify-between py-3">
            <div class="text-gray-500 font-extrabold">おすすめのユーザー</div>
            <router-link
                :to="{ name: 'SuggestionsUsers' }"
                class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
            >
                全て見る
            </router-link>
        </div>

        <div v-if="suggestionUsers.length">
            <div
                v-for="user in suggestionUsers"
                :key="user.id"
                class="flex items-center justify-between max-w-[300px] pb-2"
            >
                <router-link
                    :to="{ name: 'User', params: { id: user.id } }"
                    class="flex items-center"
                >
                    <img
                        class="rounded-full z-0 w-[37px] h-[37px]"
                        :src="user.file"
                    />
                    <div class="pl-4">
                        <div class="text-black font-extrabold">
                            {{ user.name }}
                        </div>
                    </div>
                </router-link>
                <button
                    class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    v-if="isNotFollowing(user.id)"
                    @click="follow(user.id)"
                >
                    フォロー
                </button>
                <button
                    class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    v-if="isFollowing(user.id)"
                    @click="deleteFollow(user.id)"
                >
                    フォロー中
                </button>
            </div>
        </div>
    </div>
</template>
