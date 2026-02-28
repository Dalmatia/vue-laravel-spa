<script setup>
import OutfitSuggestionItem from './OutfitSuggestionItem.vue';

const props = defineProps({
    items: { type: Array, required: true },
    isGeneralAdvice: Boolean,
    open: Boolean,
});

defineEmits(['update:open']);
</script>

<template>
    <div
        v-if="isGeneralAdvice"
        class="border rounded-lg bg-gray-50 px-4 py-2 mb-3"
    >
        <button
            class="w-full flex justify-between text-sm text-blue-600"
            @click="$emit('update:open', !open)"
        >
            <span>参考としてアイテム候補を見る</span>
            <span>{{ open ? '▲' : '▼' }}</span>
        </button>
    </div>

    <div
        v-if="items.length > 0 && (!isGeneralAdvice || open)"
        class="border rounded-lg bg-white shadow-sm p-4 space-y-4"
    >
        <OutfitSuggestionItem
            v-for="item in items"
            :key="item.categoryLabel"
            :part="item.part"
            :mainCategory="item.categoryLabel"
            :isGeneralAdvice="isGeneralAdvice"
        />
    </div>
</template>
