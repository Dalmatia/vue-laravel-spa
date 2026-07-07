<script setup>
import { useInitEnums } from '../../src/composables/useInitEnums';
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';

const { scenes } = useInitEnums();
defineProps({
    selectedTpo: { type: String, required: true },
});

defineEmits(['change']);
</script>

<template>
    <div class="flex justify-center mb-4 space-x-2">
        <div class="flex items-center space-x-1 flex-shrink-0">
            <PaletteOutline class="text-gray-700" />
            <span class="font-semibold text-gray-700">シーン｜</span>
        </div>

        <div class="flex overflow-x-auto space-x-2 scrollbar-hide">
            <button
                v-for="scene in scenes"
                :key="scene.key"
                class="px-3 py-1 rounded-full text-sm font-semibold border whitespace-nowrap transition"
                :class="
                    selectedTpo === scene.key
                        ? 'bg-blue-500 text-white border-blue-500'
                        : 'text-gray-500 hover:border-gray-400'
                "
                @click="$emit('change', scene.key)"
            >
                {{ scene.name }}
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
