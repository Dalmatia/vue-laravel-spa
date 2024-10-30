<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth.js';
import { useFollowStore } from '../stores/follow.js';

import Cog from 'vue-material-design-icons/Cog.vue';
import Grid from 'vue-material-design-icons/Grid.vue';
import PlayBoxOutline from 'vue-material-design-icons/PlayBoxOutline.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import Hanger from 'vue-material-design-icons/Hanger.vue';
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue';

import CreateItemOverlay from '@/Components/Items/CreateItemOverlay.vue';

let showCreateItem = ref(false);

const authStore = useAuthStore();
const followStore = useFollowStore();
const outfits = ref([]);

// ユーザー情報の取得
const fetchUserData = async () => {
    try {
        await authStore.fetchUserData();
    } catch (error) {
        if (error.response && error.response.status === 401) {
            handleUnauthorized();
        }
    }
};

// 投稿したコーディネートを取得
const fetchOutfits = async () => {
    try {
        const response = await axios.get(`/api/users/${authStore.user.id}`);
        outfits.value = response.data.outfits;
    } catch (error) {
        console.error(error);
    }
};

let tab = ref('User');
const select = (selectedTab) => {
    tab.value = selectedTab;
};

const fetchData = async (action) => {
    if (authStore.user && authStore.user.id) {
        await action(authStore.user.id);
    }
};

onMounted(async () => {
    await Promise.all([
        fetchUserData(),
        fetchOutfits(),
        fetchData(followStore.followList),
        fetchData(followStore.followerList),
    ]).catch((error) => {
        console.error('情報の取得に失敗しました。', error);
    });
});
</script>

<template>
    <div class="mt-2 md:pt-6"></div>
    <div
        class="max-w-[880px] lg:ml-0 md:ml-[80px] md:pl-20 px-4 w-[100vw] md:w-[84.5vw]"
    >
        <div class="flex items-center md:justify-between">
            <div>
                <img
                    class="rounded-full object-fit md:w-[200px] w-[100px] cursor-pointer"
                    :src="authStore.user.file"
                />
            </div>

            <div class="ml-6 w-full" v-if="authStore.user">
                <div class="flex items-center md:mb-8 mb-5">
                    <div class="md:mr-6 mr-3 rounded-lg text-[22px]">
                        {{ authStore.user.name }}
                    </div>
                    <router-link
                        :to="{
                            name: 'EditProfile',
                            params: { id: authStore.user.id },
                        }"
                        class="md:block hidden md:mr-6 p-1 px-4 rounded-lg text-[16px] font-extrabold bg-gray-100 hover:bg-gray-200"
                    >
                        プロフィール編集
                    </router-link>
                    <Cog :size="28" class="cursor-pointer" />
                </div>
                <router-link
                    class="md:hidden mr-6 p-1 px-4 max-w-[260px] w-full rounded-lg text-[17px] font-extrabold bg-gray-100 hover:bg-gray-200"
                    :to="{
                        name: 'EditProfile',
                        params: { id: authStore.user.id },
                    }"
                >
                    プロフィール編集
                </router-link>
                <div class="md:block hidden">
                    <div class="flex items-center text-[18px]">
                        <div class="mr-6">
                            <span class="font-extrabold">
                                {{ outfits.length }}
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
                    {{ outfits.length }}
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
        >
            <router-link
                class="p-3 w-1/4 flex justify-center border-t border-t-gray-900"
                :to="{ name: 'User' }"
                :class="{ active: tab === 'User' }"
                @click="select('User')"
            >
                <Grid :size="28" fillColor="#0095F6" class="cursor-pointer" />
            </router-link>
            <div class="p-3 w-1/4 flex justify-center border-t">
                <PlayBoxOutline
                    :size="28"
                    fillColor="#8E8E8E"
                    class="cursor-pointer"
                />
            </div>
            <div class="p-3 w-1/4 flex justify-center border-t">
                <PlusCircle
                    @click="showCreateItem = true"
                    :size="28"
                    fillColor="#8E8E8E"
                    class="cursor-pointer"
                />
            </div>
            <div class="p-3 w-1/4 flex justify-center border-t">
                <BookmarkOutline
                    :size="28"
                    fillColor="#8E8E8E"
                    class="cursor-pointer"
                />
            </div>
            <router-link
                class="p-3 w-1/4 flex justify-center border-t"
                :to="{ name: 'Items' }"
                @click="select('Items')"
            >
                <Hanger :size="28" fillColor="#8E8E8E" class="cursor-pointer" />
            </router-link>
        </div>
    </div>

    <div id="ContentSection" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <div class="md:block mt-10 hidden border-t border-t-gray-300">
            <div
                class="flex items-center justify-between max-w-[600px] mx-auto font-extrabold text-gray-400 text-[15px]"
            >
                <router-link
                    class="p-[17px] w-1/4 flex justify-center items-center border-t border-t-gray-900"
                    :to="{ name: 'User' }"
                    :class="{ active: tab === 'User' }"
                    @click="select('User')"
                >
                    <Grid :size="15" fillColor="#000000" />
                    <div class="ml-2 -mb-[1px] text-gray-900">POSTS</div>
                </router-link>
                <div class="p-[17px] w-1/4 flex justify-center items-center">
                    <PlayBoxOutline
                        :size="15"
                        fillColor="#8E8E8E"
                        class="cursor-pointer"
                    />
                    <div class="ml-2 -mb-[1px] text-gray-900">REELS</div>
                </div>
                <div class="p-[17px] w-1/4 flex justify-center items-center">
                    <PlusCircle
                        @click="showCreateItem = true"
                        :size="40"
                        fillColor="#8E8E8E"
                        class="cursor-pointer"
                    />
                </div>
                <div class="p-[17px] w-1/4 flex justify-center items-center">
                    <BookmarkOutline
                        :size="15"
                        fillColor="#8E8E8E"
                        class="cursor-pointer"
                    />
                    <span class="ml-2 -mb-[1px]">SAVED</span>
                </div>
                <router-link
                    class="p-[17px] w-1/4 flex justify-center items-center"
                    :to="{ name: 'Items' }"
                    @click="select('Items')"
                >
                    <Hanger :size="15" fillColor="#8E8E8E" />
                    <span class="ml-2 -mb-[1px]">ITEMS</span>
                </router-link>
            </div>
        </div>

        <div>
            <router-view></router-view>
        </div>

        <div class="pb-20"></div>
    </div>

    <CreateItemOverlay v-if="showCreateItem" @close="showCreateItem = false" />
</template>
