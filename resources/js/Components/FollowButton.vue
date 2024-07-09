<script setup>
const props = defineProps({
    isFollowing: Boolean,
});

const emit = defineEmits(['follow', 'unfollow']);

const handleFollow = () => {
    emit('follow');
};

const handleUnFollow = () => {
    emit('unfollow');
};
</script>

<template>
    <div class="w-[110px]">
        <button
            class="bg-blue-500 text-white lg:border lg:border-blue-700 lg:bg-gradient-to-b lg:from-blue-500 lg:to-blue-600 lg:hover:opacity-70 block text-center rounded-[4px] leading-[1] lg:rounded-[2px] w-full"
            type="button"
            @click="handleFollow()"
            v-if="!isFollowing"
        >
            <span class="block pb-3 pt-[10px] font-bold tracking-[0.03em]">
                フォローする
            </span>
        </button>
        <button
            class="bg-blue-500 text-white lg:border lg:border-blue-700 lg:bg-gradient-to-b lg:from-blue-500 lg:to-blue-600 lg:hover:opacity-70 block text-center rounded-[4px] leading-[1] lg:rounded-[2px] w-full"
            type="button"
            @click="handleUnFollow()"
            v-if="isFollowing"
        >
            <span class="block pb-3 pt-[10px] font-bold tracking-[0.03em]">
                フォロー中
            </span>
        </button>
    </div>
</template>
