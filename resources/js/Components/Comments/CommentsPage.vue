<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';

import Close from 'vue-material-design-icons/Close.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

import CommentOptionsOverlay from '../Comments/CommentOptionsOverlay.vue';

const user = useAuthStore().user;
const props = defineProps(['outfit']);
const outfit = ref(props.outfit);
const username = ref(null);
const emit = defineEmits(['closeOverlay']);
const comments = ref([]);
// 日付毎にコメントをまとめるためのデータ構造
const commentedDate = reactive({});
let comment = ref('');
let selectComment = ref(null);
let id = ref(null);

// 投稿ユーザーの情報とコーディネートに対するコメントの取得を並列で行う
const fetchUserDataAndComments = async () => {
    const [userDataResponse, commentsResponse] = await Promise.all([
        // ユーザー情報とコメントを並列で取得
        axios.get(`/api/users/${outfit.value.user_id}`),
        axios.get('/api/comments', { params: { outfit_id: outfit.value.id } }),
    ]);

    username.value = userDataResponse.data.user.name;
    comments.value = commentsResponse.data.comments;

    // コメントしたユーザーの情報も取得する
    const users = await Promise.all(
        comments.value.map(async (comment) => {
            const userResponse = await axios.get(
                `/api/users/${comment.user_id}`
            );
            return userResponse.data.user;
        })
    );

    // コメントオブジェクトにユーザー情報を追加
    comments.value.forEach((comment, index) => {
        comment.user = users[index];

        // コメントが投稿された日付を取得
        const commentDate = new Date(comment.created_at);
        // コメントが投稿された日付を取得してフォーマットする
        const formattedDate = `${commentDate.getFullYear()}/${(
            commentDate.getMonth() + 1
        )
            .toString()
            .padStart(2, '0')}/${commentDate
            .getDate()
            .toString()
            .padStart(2, '0')}`;
        // コメントオブジェクトに日付を追加
        comment.created_date = formattedDate;

        if (!commentedDate[comment.created_date]) {
            commentedDate[comment.created_date] = [];
        }
        // 日付毎にコメントをまとめる
        commentedDate[comment.created_date].push(comment);
    });
};

// コメントがコーディネート投稿者であるかどうかの判別
const commentedByUser = (comment) => {
    return comment.user_id === outfit.value.user_id;
};

// textareaのサイズ調整
const textareaInput = (e) => {
    const textarea = e.target;
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
};

// コメント投稿機能
const addComment = () => {
    if (comment.value.trim() === '') {
        return; // コメントが空の場合は何もしない
    }

    const id = outfit.value.id;
    axios
        .post(`/api/outfit/${id}/comment`, {
            outfit_id: id,
            user_id: user.id,
            text: comment.value,
        })
        .then((response) => {
            comment.value = '';
            console.log(response);
            fetchUserDataAndComments();
        })
        .catch(function (error) {
            console.error('コメントの送信に失敗しました。:', error);
        });
};

// コメントの削除
const deleteComment = async (comment) => {
    if (selectComment.value === 'Comment') {
        const url = `/api/comment/${comment.id}`;
        await axios
            .delete(url)
            .then((response) => {
                console.log(response);
                // 削除に成功した際にコメントの一覧を取得
                fetchUserDataAndComments();
            })
            .catch((error) => {
                console.error('コメントの送信に失敗しました。:', error);
            });
    }
};

onMounted(() => {
    fetchUserDataAndComments();
    window.addEventListener('comment-updated', fetchUserDataAndComments);
});

onUnmounted(() => {
    window.removeEventListener('comment-updated', fetchUserDataAndComments);
});
</script>

<template>
    <div
        id="comment_dialog"
        class="fixed inset-0 flex items-center justify-center"
    >
        <div class="fixed inset-0 z-10 bg-white/[0.97]"></div>
        <div class="z-20 h-full w-full overflow-y-auto">
            <div
                id="outfitDetail_user_comment"
                class="min-h-screen transition duration-200 ease-linear opacity-1"
            >
                <!-- 投稿コメント一覧 -->
                <div
                    class="w-full transition-all duration-300 ease-linear opacity-1 translate-y-0"
                >
                    <div class="px-[10px] pb-10 pt-5">
                        <div v-if="comments.length > 0">
                            <p
                                class="text-[14px] font-bold leading-[1.3] text-black-400"
                            >
                                {{ username }}さんへのコメント ({{
                                    comments.length
                                }})
                            </p>
                            <div class="pb-[46px]">
                                <div
                                    class="flex flex-col gap-4 pt-[17px] xl:pt-2"
                                >
                                    <div
                                        v-for="(comment, index) in comments"
                                        :key="comment"
                                    >
                                        <div
                                            v-if="
                                                index === 0 ||
                                                comments[index - 1]
                                                    .created_date !==
                                                    comment.created_date
                                            "
                                        >
                                            <div
                                                class="flex flex-col items-center pt-[2px] xl:mt-[14px] xl:border-t xl:border-t-gray-200 xl:pt-0"
                                            >
                                                <p
                                                    class="mb-[10px] inline-block rounded-[10px] bg-black px-[10px] py-1 align-middle text-[12px] leading-[1.2] text-white opacity-50 xl:mb-0 xl:mt-[-14px] xl:bg-white xl:bg-none xl:px-[22px] xl:pb-[14px] xl:text-[14px] xl:tracking-[0.07em] xl:text-gray-600 xl:opacity-100"
                                                >
                                                    {{ comment.created_date }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            :class="[
                                                'flex w-full gap-4 lg:gap-[15px]',
                                                commentedByUser(comment)
                                                    ? 'flex-row-reverse'
                                                    : '', // コメントが投稿主のものであれば何も指定せず、そうでなければ右側に表示
                                            ]"
                                        >
                                            <a
                                                class="flex h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-300 hover:opacity-70"
                                            >
                                                <img
                                                    class="rounded-full w-[38px] h-[38px]"
                                                    src="https://picsum.photos/id/54/800/820"
                                                />
                                            </a>

                                            <div class="pt-[3px] lg:pt-[6px]">
                                                <div
                                                    class="flex flex-row-reverse"
                                                >
                                                    <p
                                                        v-if="
                                                            comment.user &&
                                                            comment.user.name
                                                        "
                                                        :class="[
                                                            'flex w-full gap-4 lg:gap-[15px]',
                                                            commentedByUser(
                                                                comment
                                                            )
                                                                ? 'flex-row-reverse'
                                                                : '',
                                                        ]"
                                                    >
                                                        {{ comment.user.name }}
                                                        <button
                                                            v-if="
                                                                user.id ===
                                                                comment.user_id
                                                            "
                                                            @click="
                                                                selectComment =
                                                                    'Comment';
                                                                id = comment.id;
                                                            "
                                                        >
                                                            <DotsHorizontal
                                                                class="cursor-pointer"
                                                            />
                                                        </button>
                                                    </p>
                                                </div>

                                                <div
                                                    class="flex items-end gap-[10px] lg:mt-[5px] flex-row-reverse"
                                                >
                                                    <div
                                                        class="relative max-w-[262px] flex-1 rounded-b-[6px] bg-gray-400 px-[15px] py-2 lg:max-w-[362px] lg:border lg:border-gray-300 lg:bg-gray-50 rounded-tl-[6px]"
                                                    >
                                                        {{ comment.text }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- コメントが存在しない場合の表示 -->
                        <template v-else>
                            <p class="text-[14px] leading-[1.3] text-black-400">
                                コメントはまだありません。
                            </p>
                        </template>
                    </div>
                </div>
                <!-- ここまで -->

                <!-- コメント送信機能 -->
                <div class="z-100 fixed bottom-0 flex w-full bg-white p-2">
                    <p class="flex w-full max-h-[200px]">
                        <img
                            src="https://picsum.photos/id/32/32/32"
                            alt=""
                            class="h-8 w-8 rounded-full border border-gray-300 mr-2"
                        />
                        <textarea
                            ref="textarea"
                            :onInput="textareaInput"
                            v-model="comment"
                            placeholder="コメントする"
                            rows="1"
                            class="flex-1 rounded-[18px] border-[1px] border-gray-300 bg-gray-100 py-2 pl-3 text-[14px] leading-[1.3] text-gray-500"
                        ></textarea>
                    </p>
                    <button
                        v-if="comment.trim() !== ''"
                        @click="addComment()"
                        class="text-blue-600 font-extrabold pr-4 flex-shrink-0"
                    >
                        送信
                    </button>
                </div>

                <button
                    class="fixed right-[15px] top-[15px] flex h-[30px] w-[30px] items-center justify-center rounded-[50%] bg-black text-[14px] leading-[1] text-white opacity-60 transition-all duration-300 ease-linear translate-y-0"
                    @click="emit('closeOverlay')"
                >
                    <Close fillColor="#FFFFFF" />
                </button>
            </div>
        </div>
    </div>
    <!-- 自身が投稿したコメントに対するオプション -->
    <CommentOptionsOverlay
        v-if="selectComment"
        :selectComment="selectComment"
        :id="id"
        @delete-selected="deleteComment($event)"
        @close="
            selectComment = null;
            id = null;
        "
    />
</template>
