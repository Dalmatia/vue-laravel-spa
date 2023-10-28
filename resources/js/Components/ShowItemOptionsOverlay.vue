<script setup>
import axios from 'axios';
import { toRefs, ref, onMounted } from 'vue';

import EditItemOverlay from '../Components/EditItemOverlay.vue';

const emit = defineEmits(['close', 'deleteSelected']);
const props = defineProps({ deleteType: String, id: Number });

const { deleteType, id } = toRefs(props);

// 登録アイテムの削除
const deleteItem = () => {
    let url = '';
    if (deleteType.value === 'Item') {
        url = `/api/items/${id.value}`;
        setTimeout(() => {
            axios
                .delete(url)
                .then((response) => {
                    console.log(response);
                    emit('close');
                })
                .catch((error) => {
                    console.error(error);
                });
        }, 100);
    }
};

// アイテム情報取得
const item = ref(null);

const fetchItems = async () => {
    try {
        const response = await axios.get(`/api/items/${id.value}`);
        item.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

let editItem = ref(null);
let openEdit = ref(false);

const openEditItemOverlay = (item) => {
    editItem.value = item;
    openEdit.value = true;
};

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <div
        id="ShowPostOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center"
        >
            <button
                class="font-extrabold w-full text-blue-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="openEditItemOverlay(editItem)"
            >
                編集
            </button>
            <button
                class="font-extrabold w-full text-red-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="deleteItem"
            >
                アイテムの削除
            </button>
            <div class="p-3 text-lg cursor-pointer" @click="$emit('close')">
                キャンセル
            </div>
        </div>
    </div>
    <EditItemOverlay
        v-if="openEdit"
        :editItem="item"
        @closeOverlay="openEdit = false"
    />
</template>
