<script setup>
import { computed, onMounted, ref, toRefs } from 'vue';
import CommentsPage from '../Pages/CommentsPage.vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import CommentOutline from 'vue-material-design-icons/CommentOutline.vue';
import SendOutline from 'vue-material-design-icons/SendOutline.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';

const props = defineProps(['outfit']);
const { outfit } = toRefs(props);
const emit = defineEmits(['like']);
const status = ref(false);
const count = ref(0);
let openOverlay = ref(false);

const first_check = () => {
    const id = outfit.value.id;
    const array = ['/api/outfit/', id, '/firstcheck'];
    const path = array.join('');
    axios
        .get(path)
        .then((res) => {
            status.value = res.data[0] === 1;
            count.value = res.data[1];
        })
        .catch(function (err) {
            console.log(err);
        });
};

const toggleLike = () => {
    if (status.value) {
        // いいねが既にされている場合は解除
        unlike();
    } else {
        // いいねがまだされていない場合はいいね
        like();
    }
};

const like = () => {
    const id = outfit.value.id;
    const array = ['/api/outfit/', id, '/like'];
    const path = array.join('');
    axios
        .post(path)
        .then((res) => {
            status.value = true;
            count.value = res.data.count;
            window.dispatchEvent(new Event('liked-created'));
        })
        .catch(function (err) {
            console.log(err);
        });
};

const unlike = () => {
    const id = outfit.value.id;
    const array = ['/api/outfit/', id, '/unlike'];
    const path = array.join('');
    axios
        .delete(path)
        .then((res) => {
            status.value = false;
            count.value = res.data.count;
            window.dispatchEvent(new Event('liked-deleted'));
        })
        .catch(function (err) {
            console.log(err);
        });
};

const openCommentOverlay = () => {
    openOverlay.value = true;
};

onMounted(() => {
    first_check();
});
</script>

<template>
    <div class="flex z-20 items-center justify-between">
        <div class="flex gap-[6px]">
            <button
                class="border border-gray-500 bg-white text-gray-500 xl:border-gray-300 xl:bg-gradient-to-b xl:from-white xl:to-gray-60 xl:hover:opacity-70 xl:text-gray-600 block text-center rounded-[4px] leading-[1] xl:rounded-[2px] w-full"
                @click.prevent="toggleLike()"
            >
                <span
                    class="flex min-w-[64px] items-center justify-center gap-[5px] px-2 py-[9px]"
                >
                    <span id="icon-like" class="text-[21px]">
                        <HeartOutline class="cursor-pointer" v-if="!status" />
                        <Heart
                            v-else
                            class="cursor-pointer"
                            fillColor="#FF0000"
                        />
                    </span>
                    <span class="text-[15px] font-bold"> ({{ count }}) </span>
                </span>
            </button>
            <button
                class="border border-gray-500 bg-white text-gray-500 xl:border-gray-300 xl:bg-gradient-to-b xl:from-white xl:to-gray-60 xl:hover:opacity-70 xl:text-gray-600 block text-center rounded-[4px] leading-[1] xl:rounded-[2px] w-full"
                @click="openCommentOverlay()"
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
    <CommentsPage
        v-if="openOverlay"
        :outfit_id="outfit.id"
        @close-overlay="openOverlay = false"
    />
</template>
