<script setup>
import { computed, toRefs } from 'vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import CommentOutline from 'vue-material-design-icons/CommentOutline.vue';
import SendOutline from 'vue-material-design-icons/SendOutline.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import { useAuthStore } from '../stores/auth';

const props = defineProps(['outfit']);
const { outfit } = toRefs(props);
const emit = defineEmits(['like']);
const user = useAuthStore().user;

const isHeartActiveComputed = computed(() => {
    if (outfit.value && outfit.value.likes) {
        let isTrue = false;
        for (let i = 0; i < outfit.value.likes.length; i++) {
            const like = outfit.value.likes[i];
            if (
                like.user_id === user.id &&
                like.outfit_id === outfit.value.id
            ) {
                isTrue = true;
            }
        }

        return isTrue;
    } else {
        return false;
    }
});
</script>

<template>
    <div class="flex z-20 items-center justify-between">
        <div class="flex gap-[6px]">
            <button
                class="border border-gray-500 bg-white text-gray-500 xl:border-gray-300 xl:bg-gradient-to-b xl:from-white xl:to-gray-60 xl:hover:opacity-70 xl:text-gray-600 block text-center rounded-[4px] leading-[1] xl:rounded-[2px] w-full"
            >
                <span
                    class="flex min-w-[64px] items-center justify-center gap-[5px] px-2 py-[9px]"
                >
                    <span id="icon-like" class="text-[21px]">
                        <HeartOutline class="cursor-pointer" />
                        <!-- <Heart
                            v-else
                            class="cursor-pointer"
                            fillColor="#FF0000"
                        /> -->
                    </span>
                    <span class="text-[15px] font-bold"> (39) </span>
                </span>
            </button>
            <button
                class="border border-gray-500 bg-white text-gray-500 xl:border-gray-300 xl:bg-gradient-to-b xl:from-white xl:to-gray-60 xl:hover:opacity-70 xl:text-gray-600 block text-center rounded-[4px] leading-[1] xl:rounded-[2px] w-full"
            >
                <span
                    class="flex min-w-[64px] items-center justify-center gap-[5px] px-2 py-[9px]"
                >
                    <span id="icon-comment" class="text-[21px]">
                        <CommentOutline class="cursor-pointer" />
                    </span>
                    <span class="text-[15px] font-bold">(0)</span>
                </span>
            </button>
        </div>
        <div class="flex gap-[6px]">
            <div
                class="border border-gray-500 bg-white text-gray-500 xl:border-gray-300 xl:bg-gradient-to-b xl:from-white xl:to-gray-60 xl:hover:opacity-70 xl:text-gray-600 block text-center rounded-[4px] leading-[1] xl:rounded-[2px] w-full"
            >
                <span
                    class="flex h-[42px] items-center text-[13px] font-bold text-gray-600"
                >
                    <span
                        id="icon-favorite"
                        class="border-l-text-gray-600 flex aspect-square h-full items-center justify-center border-l text-[21px]"
                    >
                        <BookmarkOutline />
                    </span>
                </span>
            </div>
        </div>
    </div>
    <!-- <div
        class="absolute flex border bottom-0 w-full max-h-[200px] bg-white overflow-auto"
    >
        <EmoticonHappyOutline class="pl-3 pt-[10px]" :size="30" />
        <textarea
            ref="textarea"
            :onInput="textareaInput"
            v-model="comment"
            placeholder="コメントする"
            rows="1"
            class="w-full border-0 mt-4 mb-2 text-sm z-50 focus:ring-0 text-gray-600 text-[18px] outline-none"
        ></textarea>
        <button
            v-if="comment"
            class="text-blue-600 font-extrabold pr-4 min-w-[70px]"
        >
            送信
        </button>
    </div> -->
</template>
