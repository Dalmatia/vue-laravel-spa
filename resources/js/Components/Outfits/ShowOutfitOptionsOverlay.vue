<script setup>
import { onMounted, toRefs, ref, onUnmounted } from 'vue';

import EditOutfitOverlay from './EditOutfitOverlay.vue';

const emit = defineEmits(['close', 'deleteSelected']);
const props = defineProps({ deleteType: String, id: Number });
const { deleteType, id } = toRefs(props);
const outfit = ref(null);
let openEdit = ref(false);
let successMessage = ref(false);

// 登録・更新時のアイテム情報取得
const fetchOutfit = async () => {
    try {
        const response = await axios.get(`/api/outfit/${id.value}`);

        outfit.value = response.data.outfit;
    } catch (error) {
        console.error(error);
    }
};

const openEditOutfitOverlay = () => {
    openEdit.value = true;
};

// 投稿が更新された時にオーバーレイを閉じ、更新完了メッセージを表示
const closeOverlay = () => {
    openEdit.value = false;
    successMessage.value = true;
};

// 更新完了メッセージを閉じる
const closeSuccessMessage = () => {
    successMessage.value = false;
    emit('close');
};

onMounted(() => {
    fetchOutfit();
    window.addEventListener('outfit-updated', closeOverlay);
});

onUnmounted(() => {
    window.removeEventListener('outfit-updated', closeOverlay);
});
</script>

<template>
    <div
        id="ShowOutfitOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
        v-if="!successMessage"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center"
        >
            <button
                class="font-extrabold w-full text-blue-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="openEditOutfitOverlay(editOutfit)"
            >
                編集
            </button>
            <button
                class="font-extrabold w-full text-red-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
                @click="emit('deleteSelected', { deleteType, id })"
            >
                投稿の削除
            </button>
            <div class="p-3 text-lg cursor-pointer" @click="$emit('close')">
                キャンセル
            </div>
        </div>
    </div>

    <!-- 更新完了メッセージの表示 -->
    <div
        v-if="successMessage"
        class="fixed flex items-center justify-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60"
    >
        <div class="bg-white p-5 rounded-lg text-center">
            <p class="text-lg font-bold mb-4">
                コーディネートが更新されました！
            </p>
            <button
                class="bg-blue-500 text-white px-4 py-2 rounded"
                @click="closeSuccessMessage()"
            >
                OK
            </button>
        </div>
    </div>

    <EditOutfitOverlay
        v-if="openEdit"
        :editOutfit="outfit"
        @closeOverlay="openEdit = false"
    />
</template>
