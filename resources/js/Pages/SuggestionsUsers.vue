<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useFollowStore } from '@/stores/follow';

const followStore = useFollowStore();
const users = ref([]);
const loading = ref({});

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/suggestion_users', {
            params: { limit: 30 },
        });
        users.value = response.data.users;
    } catch (error) {
        console.error('ユーザーの取得に失敗しました:', error);
    }
};

onMounted(fetchUsers);

const follow = async (userId) => {
    loading.value[userId] = true;

    try {
        followStore.status[userId] = true;
        await followStore.pushFollow(userId);
    } catch (error) {
        followStore.status[userId] = false;
    } finally {
        loading.value[userId] = false;
    }
};

const deleteFollow = async (userId) => {
    loading.value[userId] = true;
    try {
        followStore.status[userId] = false;
        await followStore.deleteFollow(userId);
    } catch (error) {
        followStore.status[userId] = true;
    } finally {
        loading.value[userId] = false;
    }
};
</script>

<template>
    <div
        class="max-w-xl mx-auto mt-10 lg:mx-auto md:ml-20 w-[100vw] md:w-[84.5vw] xl:w-[70vw]"
    >
        <h1 class="text-2xl font-bold mb-6">おすすめユーザー</h1>
        <div
            v-for="user in users"
            :key="user.id"
            class="flex items-center justify-between mb-4"
        >
            <router-link
                :to="{ name: 'User', params: { id: user.id } }"
                class="flex items-center"
            >
                <img class="rounded-full w-[40px] h-[40px]" :src="user.file" />
                <span class="ml-3 font-bold">{{ user.name }}</span>
            </router-link>
            <button
                v-if="!followStore.followStatus(user.id)"
                @click="follow(user.id)"
                :disabled="loading[user.id]"
                class="bg-blue-500 text-white font-bold text-sm px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                フォロー
            </button>
            <button
                v-else
                @click="deleteFollow(user.id)"
                :disabled="loading[user.id]"
                class="bg-blue-500 text-white font-bold text-sm px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                フォロー中
            </button>
        </div>
    </div>
</template>
