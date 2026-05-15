<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth.js';
import { useFollowStore } from '../stores/follow.js';
import { useRoute } from 'vue-router';
import { useUserProfile } from '../src/composables/user/useUserProfile.js';
import { useUserFollow } from '../src/composables/user/useUserFollow.js';
import { useUserOutfits } from '../src/composables/user/useUserOutfits.js';

import TopNavUser from '../Layouts/TopNavUser.vue';
import UserProfileHeader from '../Components/User/UserProfileHeader.vue';
import UserStats from '../Components/User/UserStats.vue';
import UserTabs from '../Components/User/UserTabs.vue';
import CreateItemOverlay from '@/Components/Items/Register/CreateItemOverlay.vue';

let showCreateItem = ref(false);

const authStore = useAuthStore();
const followStore = useFollowStore();
const route = useRoute();
const userId = computed(() => route.params.id);
const authUser = computed(() => {
    if (!authStore.user || !user.value) return false;
    return authStore.user?.id === user.value?.id;
});

const { user, username, outfitCount } = useUserProfile(userId);

const { toggleFollow } = useUserFollow(userId, followStore);

const { outfits, loadMoreTrigger } = useUserOutfits(userId);

const userBackRoute = computed(() => {
    const backRoute = history.state?.backRoute;

    if (backRoute) {
        return backRoute;
    }
    return null;
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
        <UserProfileHeader
            :user="user"
            :authUser="authUser"
            :userId="userId"
            :outfitCount="outfitCount"
            :followStore="followStore"
            :toggleFollow="toggleFollow"
        />
    </div>

    <div class="md:hidden">
        <UserStats
            :outfitCount="outfitCount"
            :followerCount="followStore.followerCount"
            :followingCount="followStore.followingCount"
            mobile
        />
    </div>

    <div id="ContentSection" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <UserTabs
            :user="user"
            :authUser="authUser"
            @openCreateItem="showCreateItem = true"
        />

        <div>
            <router-view v-slot="{ Component }">
                <component
                    :is="Component"
                    :outfits="outfits"
                    :outfit-count="outfitCount"
                />
            </router-view>
        </div>
        <div ref="loadMoreTrigger" class="h-10"></div>

        <div class="pb-20"></div>
    </div>

    <CreateItemOverlay v-if="showCreateItem" @close="showCreateItem = false" />
</template>
