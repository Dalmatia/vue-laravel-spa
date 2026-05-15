<script setup>
defineProps({
    outfitCount: Number,
    followerCount: Number,
    followingCount: Number,
    mobile: Boolean,
});
</script>

<template>
    <!-- モバイル用レイアウト -->
    <div
        v-if="mobile"
        class="w-full flex items-center justify-around border-t border-t-gray-300 mt-8"
    >
        <div class="text-center p-3">
            <div class="font-extrabold">
                {{ outfitCount }}
            </div>
            <div class="text-gray-400 font-semibold -mt-1.5">投稿</div>
        </div>

        <router-link class="text-center p-3" :to="{ name: 'FollowerList' }">
            <div class="font-extrabold">
                {{ followerCount }}
            </div>
            <div class="text-gray-400 font-semibold -mt-1.5">フォロワー</div>
        </router-link>

        <router-link class="text-center p-3" :to="{ name: 'FollowList' }">
            <div class="font-extrabold">
                {{ followingCount }}
            </div>
            <div class="text-gray-400 font-semibold -mt-1.5">フォロー</div>
        </router-link>
    </div>

    <!-- PC/タブレット用レイアウト -->
    <div v-else class="flex items-center text-[18px]">
        <div class="mr-6">
            <span class="font-extrabold">
                {{ outfitCount }}
            </span>
            投稿
        </div>

        <router-link class="mr-6" :to="{ name: 'FollowerList' }">
            <span class="font-extrabold">
                {{ followerCount }}
            </span>
            フォロワー
        </router-link>

        <router-link class="mr-6" :to="{ name: 'FollowList' }">
            <span class="font-extrabold">
                {{ followingCount }}
            </span>
            フォロー
        </router-link>
    </div>
</template>
