<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useFollowStore } from '../../stores/follow';
import { useOutfitDetail } from '../../src/composables/outfit/useOutfitDetail';
import { useOutfitItems } from '../../src/composables/outfit/useOutfitItems';
import { useOutfitComments } from '../../src/composables/outfit/useOutfitComments';

import OutfitHeader from './OutfitHeader.vue';
import CommentPreview from './CommentPreview.vue';
import LikesSection from '../LikesSection.vue';
import ShowOutfitOptionsOverlay from './ShowOutfitOptionsOverlay.vue';
import ItemList from './ItemList.vue';
import CommentsPage from '../Comments/CommentsPage.vue';

import Close from 'vue-material-design-icons/Close.vue';

let deleteType = ref(null);
let id = ref(null);
let commentOverlay = ref(false);

const followStore = useFollowStore();
const props = defineProps(['outfit', 'commentOverlay']);

const {
    outfit,
    user: postsByUser,
    fetchOutfit,
} = useOutfitDetail(props.outfit);

const { enumStore, outfitItems, fetchItems } = useOutfitItems();

// 選択したシーズン情報の取得
const season = computed(() => enumStore.getSeason(outfit.value.season));

const { comments, isLoading, fetchComments } = useOutfitComments();

defineEmits(['closeOverlay', 'addComment', 'updateLike', 'deleteSelected']);

// フォロー状態のチェック
const followStatus = async () => {
    if (outfit.value && outfit.value.user_id) {
        try {
            await followStore.followStatusCheck(outfit.value.user.id);
        } catch (error) {
            console.error(`フォロー状態の取得に失敗しました。:`, error);
        }
    }
};

// フォローする
const follow = async (userId) => {
    try {
        await followStore.pushFollow(userId);
        followStore.status[userId] = true;
    } catch (error) {
        console.error(`フォローに失敗しました。:`, error);
    }
};

// フォロー解除
const deleteFollow = async (userId) => {
    try {
        await followStore.deleteFollow(userId);
        followStore.status[userId] = false;
    } catch (error) {
        console.error(`フォローの解除に失敗しました。:`, error);
    }
};

// ...もっと見るをクリックした時にコメントモーダルを開く
const openCommentOverlay = () => {
    commentOverlay.value = true;
};

const refreshOutfitData = async () => {
    await fetchOutfit();
    await fetchItems(outfit.value);
};

onMounted(async () => {
    await Promise.all([
        fetchOutfit(),
        followStatus(),
        fetchComments(outfit.value.id),
    ]);
    await fetchItems(outfit.value);

    window.addEventListener('outfit-updated', refreshOutfitData);
    window.addEventListener('comment-posted', fetchComments);

    if (props.commentOverlay) {
        openCommentOverlay();
    }
});

onUnmounted(() => {
    window.removeEventListener('outfit-updated', refreshOutfitData);
    window.removeEventListener('comment-posted', fetchComments);
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
        @click.self="$emit('closeOverlay')"
    >
        <button class="absolute right-3" @click="$emit('closeOverlay')">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl overflow-auto"
        >
            <!-- ヘッダーセクション -->
            <OutfitHeader
                :outfit="outfit"
                :postsByUser="postsByUser"
                @follow="follow"
                @unfollow="deleteFollow"
                @openOptions="
                    (outfitId) => {
                        deleteType = 'Outfit';
                        id = outfitId;
                    }
                "
            />
            <!-- ここまでヘッダーセクション -->

            <!-- メインセクション -->
            <section
                class="lg:mx-auto lg:grid lg:w-[990px] lg:grid-cols-[558px_1fr] lg:gap-x-5 lg:pt-5"
            >
                <!-- コーディネート・お気に入りやコメント等表示部分 -->
                <section
                    class="border-y border-gray-300 lg:h-fit lg:overflow-hidden lg:rounded-t-[3px] lg:border lg:place-items-center"
                >
                    <!-- コーディネート画像表示部分 -->
                    <article class="relative">
                        <div
                            class="hide-scroll relative flex snap-x snap-mandatory overflow-x-auto"
                        >
                            <section
                                class="relative flex w-full shrink-0 snap-center"
                            >
                                <h1 class="flex">
                                    <span
                                        class="relative overflow-hidden max-w-full aspect-w-16 aspect-h-9"
                                    >
                                        <img
                                            class="w-full h-full lg:w-[556px] lg:h-[742px] p-4 mx-auto rounded-xl"
                                            :src="outfit.file"
                                        />
                                    </span>
                                </h1>
                            </section>
                        </div>
                    </article>

                    <!-- いいね!等の表示 -->
                    <div
                        class="justify-between border-t border-gray-300 bg-white px-[23px] py-6 w-full"
                    >
                        <LikesSection
                            v-if="outfit"
                            :outfit="outfit"
                            @like="$emit('updateLike', $event)"
                        />
                    </div>

                    <!-- コメント欄 -->
                    <CommentPreview
                        :outfit="outfit"
                        :comments="comments"
                        :isLoading="isLoading"
                        @open-overlay="openCommentOverlay()"
                    />
                </section>

                <!-- サブセクション -->
                <section
                    class="pt-[18px] lg:overflow-hidden lg:pt-0 lg:grid-in-[sub]"
                >
                    <div
                        class="flex flex-col lg:flex-col-reverse lg:rounded-[3px] lg:border lg:border-gray-300 lg:bg-white lg:pb-[6px] lg:pl-[23px] lg:pt-7"
                    >
                        <!-- 着用アイテム表示欄 -->
                        <section>
                            <div class="hidden pt-6 lg:block">
                                <div class="bg-gray-300 h-[1px]"></div>
                            </div>

                            <div class="lg:pt-6">
                                <div
                                    class="flex flex-col gap-[17px] lg:gap-5 lg:bg-white lg:pb-[23px]"
                                >
                                    <h2
                                        class="pl-4 pt-[6px] text-[14px] font-bold leading-none lg:pl-0 lg:pt-0 lg:text-[16px] lg:tracking-wide"
                                    >
                                        着用アイテム
                                    </h2>
                                    <ItemList
                                        :items="outfitItems"
                                        @close-overlay="$emit('closeOverlay')"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- コーディネートの紹介や着用日等の表示 -->
                        <section class="lg:pr-[23px]">
                            <h1
                                class="hidden text-[16px] font-bold leading-[1.5] lg:block"
                            >
                                {{ postsByUser.name }}さんのコーディネート
                            </h1>
                            <div class="px-4 pt-[38px] lg:px-0 lg:pt-4">
                                <div>
                                    <p
                                        class="overflow-y-hidden whitespace-pre-line text-[14px] leading-[1.8] lg:leading-[1.6] h-[224px]"
                                    >
                                        {{ outfit.description }}
                                    </p>
                                </div>
                                <div class="pt-1 lg:pt-5">
                                    <p
                                        class="flex items-center justify-between leading-[1.8] text-gray-500 lg:text-[12px] lg:leading-none lg:tracking-widest lg:text-gray-600"
                                    >
                                        <span class="pr-[5px] lg:text-[13px]">
                                            着用日:
                                            <span>
                                                {{ outfit.outfit_date }}
                                            </span>
                                        </span>

                                        <span class="pr-[5px] lg:text-[13px]">
                                            シーズン:
                                            <span>{{ season }}</span>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </section>

                        <div class="pb-16 md:hidden"></div>
                    </div>
                </section>
                <!-- ここまでサブセクション -->
            </section>
            <!-- ここまでメインセクション -->
        </div>
        <CommentsPage
            v-if="commentOverlay"
            :outfit="outfit"
            @close-overlay="commentOverlay = false"
        />
    </div>
    <ShowOutfitOptionsOverlay
        v-if="deleteType"
        :deleteType="deleteType"
        :id="id"
        @deleteSelected="
            $emit('deleteSelected', {
                deleteType: $event.deleteType,
                id: $event.id,
                outfit: outfit,
            });
            deleteType = null;
            id = null;
        "
        @close="
            deleteType = null;
            id = null;
        "
    />
</template>
