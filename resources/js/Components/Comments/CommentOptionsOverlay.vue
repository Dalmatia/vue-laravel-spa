<script setup>
import { onMounted, toRefs, ref } from 'vue';

import EditCommentOverlay from '../../Components/Comments/EditCommentOverlay.vue';

const emit = defineEmits(['close', 'deleteSelected']);
const props = defineProps({ selectComment: String, id: Number });
const { selectComment, id } = toRefs(props);
const comment = ref(null);
let editMode = ref(false);

// 投稿したコメントの情報取得
const fetchComment = async () => {
    try {
        const response = await axios.get(`/api/comment/${id.value}`);

        comment.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const openEditCommentOverlay = () => {
    editMode.value = true;
};

onMounted(() => {
    fetchComment();
});
</script>

<template>
    <div
        id="CommentOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center"
        >
            <button
                class="font-extrabold w-full text-blue-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="openEditCommentOverlay(editComment)"
            >
                編集
            </button>
            <button
                class="font-extrabold w-full text-red-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="emit('deleteSelected', { selectComment, id })"
            >
                コメントの削除
            </button>
            <div class="p-3 text-lg cursor-pointer" @click="$emit('close')">
                キャンセル
            </div>
        </div>
    </div>
    <EditCommentOverlay
        v-if="editMode"
        :editComment="comment"
        @close-overlay="editMode = false"
    />
</template>
