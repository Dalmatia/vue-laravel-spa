<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useAuthStore } from '../stores/auth.js';
import { useFollowStore } from '../stores/follow.js';
import { useRoute } from 'vue-router';

import Cog from 'vue-material-design-icons/Cog.vue';
import Grid from 'vue-material-design-icons/Grid.vue';
import Hanger from 'vue-material-design-icons/Hanger.vue';
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue';

import TopNavUser from '../Layouts/TopNavUser.vue';
import CreateItemOverlay from '@/Components/Items/Register/CreateItemOverlay.vue';

let showCreateItem = ref(false);
let prevUserId = ref(null);

const authStore = useAuthStore();
const followStore = useFollowStore();
const user = ref(null);
const username = ref('');
const outfits = ref([]);
const outfit_count = ref(0);
const route = useRoute();
const userId = computed(() => route.params.id);
const authUser = computed(() => authStore.user?.id === user.value?.id);

// ユーザー情報の取得
const fetchUser = async () => {
    if (!userId.value) return;
    try {
        const response = await axios.get(`/api/users/${userId.value}`);
        user.value = response.data.user;
        username.value = user.value.name;
        outfits.value = response.data.outfits;
        outfit_count.value = response.data.outfit_count;
    } catch (error) {
        console.error('ユーザー情報の取得に失敗しました:', error);
    }
};

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

const fetchUserData = async () => {
    if (!userId.value) return;
    await Promise.all([fetchUser(), fetchFollowData()]);
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

const userBackRoute = computed(() => {
    const backRoute = history.state?.backRoute;

    if (backRoute) {
        return backRoute;
    }
    return null;
});

watch(
    () => route.params.id,
    (newId, oldId) => {
        if (newId && newId !== prevUserId.value) {
            prevUserId.value = newId;
            fetchUserData();
        }
    },
    { immediate: true }
);

onMounted(() => {
    window.addEventListener('outfit-created', fetchUserData);
    window.addEventListener('outfit-updated', fetchUserData);
    window.addEventListener('outfit-deleted', fetchUserData);
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', fetchUserData);
    window.removeEventListener('outfit-updated', fetchUserData);
    window.removeEventListener('outfit-deleted', fetchUserData);
});
</script>

<template>
    <TopNavUser
        class="md:hidden"
        :title="username"
        :showBackButton="!authUser"
        :backRoute="userBackRoute"
    />
    <div class="mt-2 md:pt-6"></div>
    <div
        class="max-w-[880px] lg:ml-0 md:ml-[80px] md:pl-20 px-4 w-[100vw] md:w-[84.5vw]"
    >
        <div class="flex items-center md:justify-between">
            <div>
                <img
                    class="rounded-full object-fit md:w-[200px] w-[100px] cursor-pointer"
                    :src="user?.file"
                />
            </div>

            <div class="ml-6 w-full" v-if="user">
                <div class="flex items-center md:mb-8 mb-5">
                    <div class="md:mr-6 mr-3 rounded-lg text-[22px]">
                        {{ user.name }}
                    </div>
                    <div v-if="!authUser" class="mt-4">
                        <button
                            v-if="followStore.followStatus(userId)"
                            @click="toggleFollow"
                            class="px-4 py-2 bg-blue-500 rounded-md text-white hover:bg-blue-600 font-bold"
                        >
                            フォロー中
                        </button>
                        <button
                            v-else
                            @click="toggleFollow"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 font-bold"
                        >
                            フォロー
                        </button>
                    </div>
                    <router-link
                        v-if="authUser"
                        :to="{
                            name: 'EditProfile',
                            params: { id: user.id },
                        }"
                        class="md:block hidden md:mr-6 p-1 px-4 rounded-lg text-[16px] font-extrabold bg-gray-100 hover:bg-gray-200"
                    >
                        プロフィール編集
                    </router-link>
                    <router-link v-if="authUser" :to="{ name: 'Settings' }">
                        <Cog :size="28" class="cursor-pointer" />
                    </router-link>
                </div>
                <router-link
                    class="md:hidden mr-6 p-1 px-4 max-w-[260px] w-full rounded-lg text-[17px] font-extrabold bg-gray-100 hover:bg-gray-200"
                    v-if="authUser"
                    :to="{
                        name: 'EditProfile',
                        params: { id: user.id },
                    }"
                >
                    プロフィール編集
                </router-link>
                <div class="md:block hidden">
                    <div class="flex items-center text-[18px]">
                        <div class="mr-6">
                            <span class="font-extrabold">
                                {{ outfit_count }}
                            </span>
                            投稿
                        </div>
                        <router-link
                            class="mr-6"
                            :to="{ name: 'FollowerList' }"
                        >
                            <span class="font-extrabold">
                                {{ followStore.followerCount }}
                            </span>
                            フォロワー
                        </router-link>
                        <router-link class="mr-6" :to="{ name: 'FollowList' }">
                            <span class="font-extrabold">
                                {{ followStore.followingCount }}
                            </span>
                            フォロー
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:hidden">
        <div
            class="w-full flex items-center justify-around border-t border-t-gray-300 mt-8"
        >
            <div class="text-center p-3">
                <div class="font-extrabold">
                    {{ outfit_count }}
                </div>
                <div class="text-gray-400 font-semibold -mt-1.5">投稿</div>
            </div>
            <router-link class="text-center p-3" :to="{ name: 'FollowerList' }">
                <div class="font-extrabold">
                    {{ followStore.followerCount }}
                </div>
                <div class="text-gray-400 font-semibold -mt-1.5">
                    フォロワー
                </div>
            </router-link>
            <router-link class="text-center p-3" :to="{ name: 'FollowList' }">
                <div class="font-extrabold">
                    {{ followStore.followingCount }}
                </div>
                <div class="text-gray-400 font-semibold -mt-1.5">フォロー</div>
            </router-link>
        </div>

        <div
            class="w-full flex items-center justify-between border-t border-t-gray-300"
            v-if="user"
        >
            <router-link
                class="p-3 w-1/3 flex justify-center border-t"
                :to="{ name: 'User', params: { id: user.id } }"
                :class="{ 'border-t border-t-gray-900': route.name === 'User' }"
            >
                <Grid
                    :size="28"
                    class="cursor-pointer"
                    :class="{
                        'text-[#8E8E8E]': route.name !== 'User',
                        'text-[#0095F6]': route.name === 'User',
                    }"
                />
            </router-link>
            <div class="p-3 w-1/3 flex justify-center border-t" v-if="authUser">
                <PlusCircle
                    @click="showCreateItem = true"
                    :size="28"
                    fillColor="#8E8E8E"
                    class="cursor-pointer"
                />
            </div>
            <router-link
                class="p-3 w-1/3 flex justify-center border-t"
                v-if="authUser"
                :to="{ name: 'Items', params: { id: user.id } }"
                :class="{
                    'border-t border-t-gray-900': route.name === 'Items',
                }"
            >
                <Hanger
                    :size="28"
                    class="cursor-pointer"
                    :class="{
                        'text-[#8E8E8E]': route.name !== 'Items',
                        'text-[#0095F6]': route.name === 'Items',
                    }"
                />
            </router-link>
        </div>
    </div>

    <div id="ContentSection" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <div
            class="md:block mt-10 hidden border-t border-t-gray-300"
            v-if="user"
        >
            <div
                class="flex items-center justify-between max-w-[600px] mx-auto font-extrabold text-gray-400 text-[15px]"
            >
                <router-link
                    class="p-[17px] w-1/3 flex justify-center items-center"
                    :to="{ name: 'User', params: { id: user.id } }"
                    :class="{
                        'text-[#8E8E8E]': route.name !== 'User',
                        'border-t border-t-gray-900 text-gray-900':
                            route.name === 'User',
                    }"
                >
                    <Grid :size="15" />
                    <div class="ml-2 -mb-[1px]">POSTS</div>
                </router-link>
                <div
                    class="p-[17px] w-1/3 flex justify-center items-center"
                    v-if="authUser"
                >
                    <PlusCircle
                        @click="showCreateItem = true"
                        :size="40"
                        fillColor="#8E8E8E"
                        class="cursor-pointer"
                    />
                </div>
                <router-link
                    class="p-[17px] w-1/3 flex justify-center items-center"
                    v-if="authUser"
                    :to="{ name: 'Items', params: { id: user.id } }"
                    :class="{
                        'text-[#8E8E8E]': route.name !== 'Items',
                        'border-t border-t-gray-900 text-gray-900':
                            route.name === 'Items',
                    }"
                >
                    <Hanger :size="15" />
                    <span class="ml-2 -mb-[1px]">ITEMS</span>
                </router-link>
            </div>
        </div>

        <div>
            <router-view v-slot="{ Component }">
                <component
                    :is="Component"
                    :outfits="outfits"
                    :outfit-count="outfit_count"
                />
            </router-view>
        </div>

        <div class="pb-20"></div>
    </div>

    <CreateItemOverlay v-if="showCreateItem" @close="showCreateItem = false" />
</template>
