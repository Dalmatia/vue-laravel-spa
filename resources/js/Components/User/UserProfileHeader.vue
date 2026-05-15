<script setup>
import UserStats from './UserStats.vue';
import Cog from 'vue-material-design-icons/Cog.vue';

defineProps({
    user: Object,
    authUser: Boolean,
    userId: [String, Number],
    outfitCount: Number,
    followStore: Object,
    toggleFollow: Function,
});
</script>

<template>
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
                <UserStats
                    :outfitCount="outfitCount"
                    :followerCount="followStore.followerCount"
                    :followingCount="followStore.followingCount"
                />
            </div>
        </div>
    </div>
</template>
