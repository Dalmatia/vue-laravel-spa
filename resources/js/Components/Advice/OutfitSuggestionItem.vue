<script setup>
import OutfitReasonBlock from '../OutfitReasonBlock.vue';

const props = defineProps({
    part: Object,
    mainCategory: String,
    isGeneralAdvice: Boolean,
});
</script>

<template>
    <div class="flex justify-center bg-gray-50 rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-[96px_1fr] gap-4">
            <img
                v-if="part.item"
                :src="part.item.file"
                class="w-24 h-24 object-cover rounded"
            />

            <div>
                <div class="mb-2">
                    <span
                        class="inline-block text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700"
                    >
                        {{ mainCategory }}
                    </span>
                </div>

                <OutfitReasonBlock
                    v-if="!isGeneralAdvice && part.primaryReasons?.length"
                    title="このアイテムを選んだ理由"
                    :reasons="part.primaryReasons"
                />

                <OutfitReasonBlock
                    v-else-if="!isGeneralAdvice && part.alternatives?.length"
                    title="補足"
                    :reasons="part.alternatives[0].reasons"
                />
            </div>
        </div>
    </div>
</template>
