<script setup>
import { useAuthStore } from '../../stores/auth';
import { useFollowStore } from '../../stores/follow';

import FollowButton from '../FollowButton.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

const props = defineProps({
    outfit: Object,
    postsByUser: Object,
});

const emit = defineEmits(['follow', 'unfollow', 'openOptions']);

const authUser = useAuthStore().user;
const followStore = useFollowStore();
</script>

<template>
    <div class="top-[54px] z-contentHeader lg:block">
        <div
            class="w-full bg-white pb-[14px] pt-[11px] border-b-[1px] border-gray-300 rounded-xl"
        >
            <div class="relative mx-auto flex w-full">
                <div class="flex items-center">
                    <p
                        class="h-12 w-12 overflow-hidden rounded-[50%] border border-gray-300 hover:opacity-70"
                    >
                        <img
                            class="rounded-full h-12 w-12"
                            :src="postsByUser.file"
                        />
                    </p>
                    <div class="pl-[10px]">
                        <div
                            class="items-center pt-[2px] leading-[1.2] tracking-wider"
                        >
                            <p class="font-extrabold text-[15px]">
                                {{ postsByUser.name }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="ml-3 mt-2"
                        v-if="
                            authUser.id &&
                            outfit.user_id &&
                            authUser.id !== outfit.user_id
                        "
                    >
                        <FollowButton
                            :is-following="
                                followStore.followStatus(outfit.user_id)
                            "
                            @follow="emit('follow', outfit.user_id)"
                            @unfollow="emit('unfollow', outfit.user_id)"
                        />
                    </div>
                </div>
                <div class="ml-auto mt-2">
                    <button
                        v-if="authUser.id === outfit.user_id"
                        @click="emit('openOptions', outfit.id)"
                    >
                        <DotsHorizontal class="cursor-pointer" :size="27" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
