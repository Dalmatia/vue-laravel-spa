<script setup>
import { defineProps, defineEmits } from 'vue';
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';
import { getEnumStore } from '@/stores/enum';

const enumStore = getEnumStore();

const props = defineProps({
    advice: { type: Object, default: null },
    selectedTpo: { type: String, required: true },
    isAdviceLoading: { type: Boolean, required: true },
});

const emit = defineEmits(['change-tpo']);

const tpoOptions = [
    { key: 'casual', label: 'カジュアル' },
    { key: 'date', label: 'デート' },
    { key: 'office', label: 'オフィス' },
    { key: 'outdoor', label: 'アウトドア' },
];
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
            <svg
                class="animate-spin h-6 w-6 text-blue-400 mr-2"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8z"
                ></path>
            </svg>
            <span class="text-gray-500">服装アドバイスを生成中...</span>
        </div>

        <p v-else-if="advice?.error" class="text-red-500 text-center py-10">
            {{ advice.message }}
        </p>

        <template v-else-if="advice">
            <p class="text-sm text-gray-700 mb-4 whitespace-pre-line">
                {{ advice.advice }}
            </p>

            <div
                v-if="advice.outfit_suggestion"
                class="grid grid-cols-2 sm:grid-cols-4 gap-4"
            >
                <div
                    v-for="(part, mainCategory) in advice.outfit_suggestion"
                    :key="mainCategory"
                    class="flex flex-col items-center bg-gray-50 rounded-lg shadow-sm p-2"
                >
                    <span class="font-semibold mb-1">
                        {{ enumStore.getMainCategoryName(mainCategory) }}
                    </span>
                    <img
                        v-if="part?.item"
                        :src="part.item.file"
                        class="w-24 h-24 object-cover rounded mb-2"
                    />
                    <span v-if="part?.keyword" class="text-sm text-gray-700">
                        {{ part.keyword }}
                    </span>
                    <div v-else class="text-red-500 text-sm">未登録</div>
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
