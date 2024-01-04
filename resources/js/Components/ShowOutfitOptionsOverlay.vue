<script setup>
import { onMounted, toRefs, ref } from 'vue';

const emit = defineEmits(['close', 'deleteSelected']);
const props = defineProps({ deleteType: String, id: Number });
const { deleteType, id } = toRefs(props);
const outfit = ref(null);

// 登録・更新時のアイテム情報取得
const fetchOutfit = async () => {
    try {
        const response = await axios.get(`/api/outfits/${id.value}`);

        outfit.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    fetchOutfit();
});
</script>

<template>
    <div
        id="ShowOutfitOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center"
        >
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
</template>
