<script setup>
import { defineProps, defineEmits, onMounted, ref, computed, watch } from 'vue';
import { useCategoryData } from '../src/composables/useCategoryData';
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';
import OutfitReasonBlock from './OutfitReasonBlock.vue';

const { getMainCategoryName, initEnums } = useCategoryData();
const props = defineProps({
    advice: { type: Object, default: null },
    selectedTpo: { type: String, required: true },
    isAdviceLoading: { type: Boolean, required: true },
});

const emit = defineEmits(['change-tpo', 'go-to-items']);

const tpoOptions = [
    { key: 'casual', label: 'カジュアル' },
    { key: 'date', label: 'デート' },
    { key: 'office', label: 'オフィス' },
    { key: 'outdoor', label: 'アウトドア' },
];

const showOutfitSuggestion = ref(false);

const isGeneralAdvice = computed(() => {
    return props.advice?.mode === 'general_advice';
});

watch(
    () => props.selectedTpo,
    () => {
        showOutfitSuggestion.value = false;
    },
);

onMounted(() => {
    initEnums();
});
</script>

<template>
    <div class="mt-6 border rounded-lg p-4 bg-white shadow-sm">
        <h3 class="text-lg font-bold mb-3">服装アドバイス</h3>

        <!-- TPO切替 -->
        <div class="flex justify-center mb-4 space-x-2">
            <div class="flex items-center space-x-1 flex-shrink-0">
                <PaletteOutline class="text-gray-700" />
                <span class="font-semibold text-gray-700">シーン｜</span>
            </div>

            <div class="flex overflow-x-auto space-x-2 scrollbar-hide">
                <button
                    v-for="option in tpoOptions"
                    :key="option.key"
                    class="px-3 py-1 rounded-full text-sm font-semibold border whitespace-nowrap transition"
                    :class="
                        selectedTpo === option.key
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'text-gray-500 hover:border-gray-400'
                    "
                    @click="emit('change-tpo', option.key)"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <!-- アドバイス表示部 -->
        <div
            v-if="isAdviceLoading"
            class="flex justify-center items-center py-10"
        >
            <div class="flex justify-center items-center py-10">
                <div
                    class="h-6 w-6 border-4 border-blue-400 border-t-transparent rounded-full animate-spin mr-2"
                ></div>
                <span class="text-gray-500">服装アドバイスを生成中...</span>
            </div>
        </div>

        <p v-else-if="advice?.error" class="text-red-500 text-center py-10">
            {{ advice.message }}
        </p>

        <template v-else-if="advice">
            <p
                v-if="isGeneralAdvice"
                class="text-xs text-gray-500 mb-2 text-center"
            >
                登録アイテムが少ないため、一般的な服装アドバイスを表示しています
            </p>

            <p class="text-sm text-gray-700 mb-4 whitespace-pre-line">
                {{ advice.advice }}
            </p>

            <div
                v-if="isGeneralAdvice && advice.notes?.length"
                class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3"
            >
                <ul
                    class="text-sm text-blue-700 list-disc list-inside space-y-1"
                >
                    <li v-for="(note, i) in advice.notes" :key="i">
                        {{ note }}
                    </li>
                </ul>
            </div>

            <div
                v-if="isGeneralAdvice"
                class="mt-4 border border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50"
            >
                <p class="text-sm text-gray-600 mb-2">
                    お手持ちのアイテムを登録すると、より具体的なコーデ提案ができます
                </p>

                <button
                    class="text-sm font-medium text-blue-600 hover:underline"
                    @click="$emit('go-to-items')"
                >
                    アイテムを登録する
                </button>
            </div>

            <!-- 折りたたみトグル -->
            <div
                v-if="isGeneralAdvice"
                class="border rounded-lg bg-gray-50 px-4 py-2 mb-3 shadow-sm"
            >
                <button
                    class="w-full flex justify-between items-center text-sm font-medium text-blue-600"
                    @click="showOutfitSuggestion = !showOutfitSuggestion"
                >
                    <span>参考としてアイテム候補を見る</span>
                    <span class="text-xs text-gray-500">
                        {{ showOutfitSuggestion ? '▲' : '▼' }}
                    </span>
                </button>
            </div>

            <div
                v-if="
                    advice.outfit_suggestion &&
                    (!isGeneralAdvice || showOutfitSuggestion)
                "
                class="border rounded-lg bg-white shadow-sm p-4 space-y-4"
            >
                <div
                    v-for="(part, mainCategory) in advice.outfit_suggestion"
                    :key="mainCategory"
                    class="flex justify-center w-full bg-gray-50 rounded-lg shadow-sm p-3"
                >
                    <div class="flex max-w-md w-full">
                        <img
                            v-if="part.item"
                            :src="part.item.file"
                            class="w-24 h-24 object-cover rounded mr-4 flex-shrink-0"
                        />

                        <div class="flex-1">
                            <div class="font-semibold mb-1">
                                {{ getMainCategoryName(mainCategory) }}
                            </div>

                            <OutfitReasonBlock
                                v-if="
                                    !isGeneralAdvice &&
                                    part.primaryReasons?.length
                                "
                                title="このアイテムを選んだ理由"
                                :reasons="part.primaryReasons"
                            />

                            <OutfitReasonBlock
                                v-else-if="
                                    !isGeneralAdvice &&
                                    part.alternatives?.length
                                "
                                title="補足"
                                :reasons="part.alternatives[0].reasons"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
    scroll-behavior: smooth;
}
</style>
