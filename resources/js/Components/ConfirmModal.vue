<script setup>
const props = defineProps({
    title: String,
    description: String,
});
const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        @click.self="emit('cancel')"
    >
        <div class="bg-white p-6 rounded shadow-lg w-80 space-y-4 z-50">
            <h2 class="text-lg font-semibold">{{ title }}</h2>
            <p class="text-gray-600 text-sm">
                {{ description }}
            </p>

            <div class="flex justify-end space-x-3">
                <button
                    class="px-4 py-2 border rounded"
                    @click="$emit('cancel')"
                >
                    キャンセル
                </button>
                <button
                    class="px-4 py-2 bg-red-500 text-white rounded"
                    type="button"
                    @click="$emit('confirm')"
                >
                    削除する
                </button>
            </div>
        </div>
    </div>
</template>
