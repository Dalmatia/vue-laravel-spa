<script setup>
const emit = defineEmits(['close', 'deleteSelected']);
</script>

<template>
    <div
        id="NotificationOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-slate-50 bg-opacity-60 p-3"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center shadow-lg"
        >
            <p class="p-5 text-lg font-medium">この通知を削除しますか？</p>
            <div class="flex justify-around p-3 border-t">
                <button
                    @click="$emit('deleteSelected')"
                    class="text-red-500 hover:text-red-700 font-bold py-2 px-4"
                >
                    削除
                </button>
                <button
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4"
                >
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</template>
