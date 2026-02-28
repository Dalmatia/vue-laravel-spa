<script setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute } from 'vue-router';
import { useFollowStore } from '../stores/follow';

import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';

const authStore = useAuthStore();
const authUserId = ref(null);
const route = useRoute();
const followStore = useFollowStore();
const userId = ref(null);

const unFollow = (userId) =>
    !followStore.followStatus(userId) && userId !== authUserId.value;

const isFollowing = (userId) =>
    followStore.followStatus(userId) && userId !== authUserId.value;

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

// フォローデータを取得する
const fetchFollowData = async () => {
    if (!route.params.id || userId.value === route.params.id) return;
    userId.value = route.params.id;
    try {
        await followStore.followList(userId.value || '');
        await followStore.fetchFollowStatus(
            followStore.followUsers.map((user) => user.id)
        );
    } catch (error) {
        console.error('フォローデータの取得に失敗しました。', error);
    }
};

watch(
    () => authStore.user,
    (newUser) => {
        if (newUser && newUser.id) {
            authUserId.value = newUser.id;
        } else {
            authUserId.value = null;
        }
    },
    { immediate: true } // 初回にもチェックを実行
);

watch(
    () => route.params.id,
    async (newId) => {
        if (!newId) return;
        await fetchFollowData();
    },
    { immediate: true }
);
</script>

<template>
    <div class="max-w-6xl mx-auto w-[100vw] md:w-[84.5vw] xl:w-[70vw]">
        <section class="min-h-[100dvh] flex-col flex">
            <main class="bg-white flex-col flex relative grow order-4">
                <div class="py-2 px-4 md:pt-14 md:pl-20 lg:px-4">
                    <div
                        class="overflow-x-hidden overflow-y-hidden h-8 rounded-r-lg rounded-l-lg relative"
                    >
                        <div class="flex items-center w-full">
                            <div
                                class="flex items-center w-full bg-gray-100 rounded-lg"
                            >
                                <Magnify
                                    class="pl-4"
                                    fillColor="#8E8E8E"
                                    :size="27"
                                />
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="検索する"
                                    class="bg-transparent w-full placeholder-[#8E8E8E] border-0 ring-0 focus:ring-0"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl block grow shrink min-h-[200px] overflow-y-scroll relative"
                >
                    <div class="h-auto overflow-auto block">
                        <div
                            class="flex flex-col pb-0 pt-0 relative"
                            v-if="followStore.followUsers.length"
                        >
                            <div
                                class="w-full max-w-full block"
                                v-for="user in followStore.followUsers"
                                :key="user.id"
                            >
                                <div
                                    class="py-2 overflow-y-visible overflow-x-visible rounded-l-none bg-transparent flex-col box-border flex rounded-r-none static px-4 items-stretch justify-start"
                                >
                                    <div
                                        class="min-w-0 justify-center flex-col box-border flex items-stretch relative z-0 grow"
                                    >
                                        <div
                                            class="flex-nowrap box-border flex items-center shrink-0 justify-between flex-row relative z-0"
                                        >
                                            <router-link
                                                class="min-w-0 max-w-full flex-col self-center box-border flex shrink-0 relative z-0"
                                                replace
                                                :to="{
                                                    name: 'User',
                                                    params: {
                                                        id: user.id,
                                                    },
                                                    state: {
                                                        backRoute: {
                                                            name: 'FollowList',
                                                            params: {
                                                                id: route.params
                                                                    .id,
                                                            },
                                                        },
                                                    },
                                                }"
                                            >
                                                <div
                                                    class="overflow-y-visible overflow-x-visible mr-3 rounded-bl-none bg-transparent flex-col box-border flex rounded-br-none shrink-0 static items-stretch self-auto justify-start grow-0 rounded-tl-none rounded-tr-none"
                                                >
                                                    <div
                                                        class="self-center block flex-none relative md:pl-20 lg:pl-0"
                                                    >
                                                        <div
                                                            class="h-[44px] w-[44px]"
                                                        >
                                                            <img
                                                                class="w-full pt-0 mt-0 mb-0 h-full border-y-0 pb-0 border-x-0 object-cover pl-0 text-[100%] pr-0 ml-0 align-baseline mr-0 border-none cursor-pointer rounded-full"
                                                                :src="user.file"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </router-link>
                                            <router-link
                                                class="min-w-0 shrink basis-auto box-border flex items-center justify-between flex-row relative z-0 grow flex-wrap"
                                                replace
                                                :to="{
                                                    name: 'User',
                                                    params: {
                                                        id: user.id,
                                                    },
                                                    state: {
                                                        backRoute: {
                                                            name: 'FollowList',
                                                            params: {
                                                                id: route.params
                                                                    .id,
                                                            },
                                                        },
                                                    },
                                                }"
                                            >
                                                <div
                                                    class="min-w-0 max-w-full flex-col box-border flex shrink-0 relative z-0 grow"
                                                >
                                                    <div
                                                        class="overflow-x-visible overflow-y-visible min-w-0 min-h-0 rounded-l-none bg-transparent flex-col box-border flex rounded-r-none static self-auto justify-start grow items-start"
                                                    >
                                                        {{ user.name }}
                                                    </div>
                                                </div>
                                            </router-link>
                                            <div
                                                class="min-w-0 max-w-full flex-col self-center box-border flex shrink-0 relative z-0"
                                            >
                                                <div
                                                    class="overflow-y-visible overflow-x-visible shrink rounded-l-none bg-transparent box-border flex rounded-r-none static items-stretch flex-row self-auto justify-start grow-0 ml-3"
                                                >
                                                    <button
                                                        class="border-none text-white bg-blue-500 rounded-lg relative text-center box-border cursor-pointer block text-sm font-semibold !py-[7px] !px-4 pointer-events-auto overflow-ellipsis w-auto leading-[18px] m-0"
                                                        type="button"
                                                        @click="follow(user.id)"
                                                        v-if="unFollow(user.id)"
                                                    >
                                                        <div
                                                            class="h-full overflow-y-visible overflow-x-visible rounded-l-none justify-center bg-transparent box-border flex items-center rounded-r-none flex-row relative px-1"
                                                        >
                                                            <div
                                                                class="block font-semibold m-0 text-[14px] leading-[18px]"
                                                            >
                                                                フォロー
                                                            </div>
                                                        </div>
                                                    </button>
                                                    <button
                                                        class="border-none text-white bg-blue-500 rounded-lg relative text-center box-border cursor-pointer block text-sm font-semibold !py-[7px] !px-4 pointer-events-auto overflow-ellipsis w-auto leading-[18px] m-0"
                                                        type="button"
                                                        @click="
                                                            deleteFollow(
                                                                user.id
                                                            )
                                                        "
                                                        v-if="
                                                            isFollowing(user.id)
                                                        "
                                                    >
                                                        <div
                                                            class="h-full overflow-y-visible overflow-x-visible rounded-l-none justify-center bg-transparent box-border flex items-center rounded-r-none flex-row relative px-1"
                                                        >
                                                            <div
                                                                class="block font-semibold m-0 text-[14px] leading-[18px]"
                                                            >
                                                                フォロー中
                                                            </div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="flex items-center justify-center h-full text-center text-gray-500"
                        >
                            フォローしているユーザーはいません
                        </p>
                    </div>
                </div>
            </main>
            <div class="order-[0]">
                <nav
                    class="flex-col box-border flex items-stretch relative align-baseline"
                >
                    <div
                        class="h-auto px-0 py-0 mx-0 my-0 text-[100%] flex-col box-border flex shrink-0 items-stretch z-[11] relative ml-0 align-baseline"
                    >
                        <header
                            class="bg-white flex flex-col flex-wrap text-[16px] font-semibold left-0 md:left-20 xl:left-64 fixed right-0 top-0 z-[1] border-b border-solid"
                        >
                            <div
                                class="items-center box-border flex flex-row h-[45px] justify-between px-[16px] py-0 w-full"
                            >
                                <div class="items-center flex basis-8 flex-row">
                                    <button
                                        class="p-0 items-center bg-transparent border-none cursor-pointer flex justify-center"
                                        type="button"
                                    >
                                        <div
                                            class="items-center flex justify-center"
                                        >
                                            <span class="inline-block">
                                                <router-link
                                                    :to="{ name: 'User' }"
                                                    class="relative block"
                                                >
                                                    <ChevronLeft :size="27" />
                                                </router-link>
                                            </span>
                                        </div>
                                    </button>
                                </div>
                                <h1
                                    class="border-0 block basis-0 grow shrink text-[100%] m-0 min-w-0 overflow-visible p-0 text-center text-ellipsis align-baseline whitespace-nowrap"
                                >
                                    フォロー中
                                </h1>
                                <div
                                    class="justify-end items-center basis-8 flex-row"
                                ></div>
                            </div>
                        </header>
                    </div>
                </nav>
            </div>
        </section>
    </div>
</template>
