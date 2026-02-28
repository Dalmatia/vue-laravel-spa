<script setup>
defineProps({
    advice: Object,
    isGeneralAdvice: Boolean,
});
</script>

<template>
    <p v-if="isGeneralAdvice" class="text-xs text-gray-500 mb-2 text-center">
        登録アイテムが少ないため、一般的な服装アドバイスを表示しています
    </p>

    <p class="text-sm text-gray-700 mb-4 whitespace-pre-line">
        {{ advice.advice }}
    </p>

    <div
        v-if="isGeneralAdvice && advice.notes?.length"
        class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3"
    >
        <ul class="text-sm text-blue-700 list-disc list-inside">
            <li v-for="(note, i) in advice.notes" :key="i">
                {{ note }}
            </li>
        </ul>
    </div>
</template>
