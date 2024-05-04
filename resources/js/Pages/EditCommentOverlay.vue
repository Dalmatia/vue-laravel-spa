<script setup>
import { ref } from 'vue';

import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editComment: Object, required: true });
const editForm = ref(props.editComment);
console.log(editForm.value.text);

// コメントの編集・更新機能
const updateComment = () => {
    let url = `/api/comment/${editForm.value.id}`;
    axios
        .put(url, {
            text: editForm.value.text,
        })
        .then((response) => {
            console.log(response);
            window.dispatchEvent(new Event('comment-updated'));
            emit('closeOverlay');
        })
        .catch((error) => {
            console.log(error);
        });
};
</script>

<template>
    <div
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-opacity-60 p-3"
    >
        <div
            class="absolute top-0 left-0 flex items-center justify-center w-full h-full"
        >
            <!-- Main modal -->
            <div class="relative px-auto w-full max-w-md max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-600"
                    >
                        <h3 class="text-lg font-semibold text-gray-900">
                            コメント編集
                        </h3>
                        <button
                            type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                            @click="emit('closeOverlay')"
                        >
                            <Close />
                            <span class="sr-only">閉じる</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <form class="p-4 md:p-5">
                        <div class="grid gap-4 mb-4 grid-cols-2">
                            <div class="col-span-2">
                                <textarea
                                    id="description"
                                    rows="4"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    v-model="editForm.text"
                                ></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button
                                type="button"
                                class="px-4 bg-transparent p-3 rounded-lg text-blue-500 hover:bg-blue-700 mr-2"
                                @click="updateComment()"
                            >
                                更新
                            </button>
                            <button
                                type="button"
                                class="px-4 bg-blue-500 p-3 rounded-lg text-white hover:bg-blue-700"
                                @click="emit('closeOverlay')"
                            >
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
