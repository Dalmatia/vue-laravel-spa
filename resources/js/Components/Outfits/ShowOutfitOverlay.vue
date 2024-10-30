<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { getEnumStore } from '../../stores/enum';
import { useFollowStore } from '../../stores/follow';

import LikesSection from '../LikesSection.vue';
import ShowOutfitOptionsOverlay from './ShowOutfitOptionsOverlay.vue';
import FollowButton from '../FollowButton.vue';
import ItemList from './ItemList.vue';
import CommentsPage from '../Comments/CommentsPage.vue';

import Close from 'vue-material-design-icons/Close.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

let deleteType = ref(null);
let id = ref(null);
let commentOverlay = ref(false);

const authUser = useAuthStore().user;
const followStore = useFollowStore();
const props = defineProps(['outfit']);
const outfit = ref(props.outfit);
const postsByUser = outfit.value.user;
const outfitItems = ref([]);
const comments = ref([]);

// 選択したシーズン情報の取得
const season = computed(() => selectData.getSeason(outfit.value.season));
const selectData = getEnumStore();

defineEmits(['closeOverlay', 'addComment', 'updateLike', 'deleteSelected']);

// 投稿時に選択したアイテムIDから情報取得
const fetchItemData = async (itemId) => {
    try {
        const response = await axios.get(`/api/items/${itemId}`);
        const itemData = response.data;

        return {
            data: itemData,
            category: selectData.getSubCategoryName(itemData.sub_category),
            color: selectData.getColor(itemData.color),
        };
    } catch (error) {
        console.error('アイテムデータの取得に失敗しました:', error);
        return null;
    }
};

// コーディネートに使用したアイテム情報取得
const fetchItems = async () => {
    try {
        const itemTypes = [
            { label: 'トップス', id: outfit.value.tops },
            { label: 'アウター', id: outfit.value.outer },
            { label: 'ボトムス', id: outfit.value.bottoms },
            { label: 'シューズ', id: outfit.value.shoes },
        ];

        const fetchPromises = itemTypes
            .filter((itemType) => itemType.id)
            .map(async (itemType) => {
                const itemData = await fetchItemData(itemType.id);
                return { label: itemType.label, ...itemData };
            });

        const items = await Promise.all(fetchPromises);
        outfitItems.value = items;
    } catch (error) {
        console.error('データの取得に失敗しました:', error);
    }
};

// 登録・更新時のコーディネート情報取得
const fetchOutfit = async () => {
    try {
        const response = await axios.get(`/api/outfit/${outfit.value.id}`);

        // EditOutfitOverlay.vueのOutfitUpdateメソッドで変更された場合、データを反映
        outfit.value = response.data;
        await fetchItems();
    } catch (error) {
        console.error(error);
    }
};

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

// コメントを取得
const fetchComments = async () => {
    try {
        const commentsResponse = await axios.get('/api/comments', {
            params: { outfit_id: outfit.value.id },
        });
        comments.value = commentsResponse.data.comments;

        const userIds = [
            ...new Set(comments.value.map((comment) => comment.user_id)),
        ];
        const userResponses = await Promise.all(
            userIds.map((id) => axios.get(`/api/users/${id}`))
        );
        const users = userResponses.map((response) => response.data.user);

        comments.value.forEach((comment) => {
            comment.user = users.find((user) => user.id === comment.user_id);
        });
    } catch (error) {
        console.error(`コメントの取得に失敗しました: ${error}`);
    }
};

// ...もっと見るをクリックした時にコメントモーダルを開く
const openCommentOverlay = () => {
    commentOverlay.value = true;
};

onMounted(async () => {
    await Promise.all([fetchOutfit(), followStatus(), fetchComments()]);
    // EditOutfitOverlay.vueのoutfitUpdateメソッドで定義したイベントの購読
    window.addEventListener('outfit-updated', fetchOutfit);
    window.addEventListener('comment-posted', fetchComments);
});

onUnmounted(() => {
    window.removeEventListener('outfit-updated', fetchOutfit);
    window.removeEventListener('comment-posted', fetchComments);
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <button class="absolute right-3" @click="$emit('closeOverlay')">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl overflow-auto"
        >
            <!-- ヘッダーセクション -->
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
                                    @follow="follow(outfit.user_id)"
                                    @unfollow="deleteFollow(outfit.user_id)"
                                />
                            </div>
                        </div>
                        <div class="ml-auto mt-2">
                            <button
                                v-if="authUser.id === outfit.user_id"
                                @click="
                                    deleteType = 'Outfit';
                                    id = outfit.id;
                                "
                            >
                                <DotsHorizontal
                                    class="cursor-pointer"
                                    :size="27"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                            @like="updateLike($event)"
                        />
                    </div>

                    <!-- コメント欄 -->
                    <div class="hidden lg:block w-full">
                        <div class="border-t border-gray-300 bg-white pt-5">
                            <div class="px-[23px]">
                                <h2
                                    class="text-[16px] font-bold leading-[1] tracking-wide"
                                >
                                    {{ postsByUser.name }} さんへのコメント
                                </h2>
                            </div>

                            <div class="pb-[30px]">
                                <div
                                    class="flex flex-col gap-4 pt-[17px] lg:pt-2"
                                    v-if="comments.length > 0"
                                >
                                    <div
                                        v-for="comment in comments.slice(0, 2)"
                                        :key="comment.id"
                                        :class="{
                                            'flex-row-reverse':
                                                comment.user_id ===
                                                outfit.user_id,
                                        }"
                                    >
                                        <div class="flex flex-col gap-4">
                                            <div
                                                class="flex w-full gap-4 lg:gap-[15px]"
                                                :class="{
                                                    'flex-row-reverse':
                                                        comment.user_id ===
                                                        outfit.user_id,
                                                }"
                                            >
                                                <div
                                                    class="flex h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-300 hover:opacity-70"
                                                >
                                                    <span
                                                        class="box-border inline-block overflow-hidden w-initial h-initial bg-none opacity-100 border-0 m-0 p-0 max-w-full"
                                                    >
                                                        <img
                                                            class="rounded-full w-[38px] h-[38px]"
                                                            v-if="
                                                                comment.user &&
                                                                comment.user
                                                                    .file
                                                            "
                                                            :src="
                                                                comment.user
                                                                    .file
                                                            "
                                                        />
                                                    </span>
                                                </div>

                                                <div
                                                    class="pt-[3px] lg:pt-[6px]"
                                                >
                                                    <div
                                                        class="flex"
                                                        :class="{
                                                            'flex-row-reverse':
                                                                comment.user_id ===
                                                                outfit.user_id,
                                                        }"
                                                    >
                                                        <p
                                                            class="line-clamp-3 max-w-[272px] text-[12px] leading-[1.4] lg:max-w-[420px] lg:tracking-[0.03em] lg:text-[14px]"
                                                            v-if="
                                                                comment.user &&
                                                                comment.user
                                                                    .name
                                                            "
                                                        >
                                                            {{
                                                                comment.user
                                                                    .name
                                                            }}
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="flex items-end gap-[10px] lg:mt-[5px]"
                                                        :class="{
                                                            'flex-row-reverse':
                                                                comment.user_id ===
                                                                outfit.user_id,
                                                        }"
                                                    >
                                                        <div
                                                            class="max-w-[262px] flex-1 rounded-b-[6px] bg-gray-500 px-[15px] py-2 lg:max-w-[362px] lg:border lg:border-gray-300 lg:bg-gray-50 rounded-tl-[6px]"
                                                        >
                                                            <span
                                                                class="absolute top-0 speech-bubble-right right-[-4px] lg:pc-speech-bubble-right"
                                                            ></span>
                                                            <p
                                                                class="whitespace-pre-wrap text-[15px] leading-[1.4] text-black lg:text-[13px] lg:leading-[1.6] lg:tracking-[0.03em] lg:text-black-400"
                                                            >
                                                                {{
                                                                    comment.text
                                                                }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="block text-[14px] leading-[1.8] text-blue-500 xl:pt-1 xl:leading-[1.6]"
                                        @click="openCommentOverlay()"
                                    >
                                        ...もっと見る
                                    </button>
                                </div>
                                <!-- コメントが存在しない場合の表示 -->
                                <template v-else>
                                    <p
                                        class="mt-10 text-[14px] text-gray-600 text-center"
                                    >
                                        コメントはまだありません。
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
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
                                    <ItemList :items="outfitItems" />
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
