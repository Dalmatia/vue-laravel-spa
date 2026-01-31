<script setup>
defineProps({
    isLoading: Boolean,
    error: Boolean,
    message: String,
});
</script>

<template>
    <div v-if="isLoading" class="flex justify-center py-10">
        <div
            class="h-6 w-6 border-4 border-blue-400 border-t-transparent rounded-full animate-spin mr-2"
        />
        <span class="text-gray-500">服装アドバイスを生成中...</span>
    </div>

    <p v-else-if="error" class="text-red-500 text-center py-10">
        {{ message }}
    </p>
</template>
