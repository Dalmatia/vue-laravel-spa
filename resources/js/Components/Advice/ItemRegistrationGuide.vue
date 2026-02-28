<script setup>
const emit = defineEmits(['go-to-items']);
</script>

<template>
    <div
        class="mt-4 border border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50"
    >
        <p class="text-sm text-gray-600 mb-2">
            お手持ちのアイテムを登録すると、より具体的なコーデ提案ができます
        </p>
        <button
            class="text-sm font-medium text-blue-600 hover:underline"
            @click="emit('go-to-items')"
        >
            アイテムを登録する
        </button>
    </div>
</template>
