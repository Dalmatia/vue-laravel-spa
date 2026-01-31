<script setup>
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';

defineProps({
    selectedTpo: { type: String, required: true },
});

defineEmits(['change']);

const tpoOptions = [
    { key: 'casual', label: 'カジュアル' },
    { key: 'date', label: 'デート' },
    { key: 'office', label: 'オフィス' },
    { key: 'outdoor', label: 'アウトドア' },
];
</script>

<template>
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
                @click="$emit('change', option.key)"
            >
                {{ option.label }}
            </button>
        </div>
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
