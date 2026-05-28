<script setup>
import { ref } from 'vue';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';

import ShowOutfitOverlay from './Outfit/ShowOutfitOverlay.vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import Comment from 'vue-material-design-icons/Comment.vue';

const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();
const props = defineProps({
    outfits: {
        type: Array,
        required: true,
    },
    outfitCount: {
        type: Number,
        required: true,
    },
});

const hoveredId = ref(null);
</script>

<template>
    <div class="grid md:gap-4 gap-1 grid-cols-3 relative">
        <div
            class="content-card flex items-center justify-center cursor-pointer relative"
            v-for="outfit in props.outfits"
            :key="outfit.id"
            @click="toggleOutfitOverlay(outfit)"
            @mouseenter="hoveredId = outfit.id"
            @mouseleave="hoveredId = null"
        >
            <div
                v-if="hoveredId === outfit.id"
                class="absolute w-full h-full z-50 flex items-center justify-around text-lg font-extrabold text-white bg-black bg-opacity-40"
            >
                <div class="flex items-center justify-around w-[50%]">
                    <div class="flex items-center justify-center">
                        <Heart fillColor="#FFFFFF" :size="30" />
                        <div class="pl-1">
                            {{ outfit.likes_count }}
                        </div>
                    </div>
                    <div class="flex items-center justify-center">
                        <Comment fillColor="#FFFFFF" :size="30" />
                        <div class="pl-1">
                            {{ outfit.comments_count }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="aspect-square w-full relative overflow-hidden">
                <img
                    class="absolute inset-0 w-full h-full object-cover"
                    v-if="outfit.file"
                    :src="outfit.file"
                    loading="lazy"
                    decoding="async"
                />
            </div>
        </div>
    </div>
    <ShowOutfitOverlay
        v-if="overlayState.open"
        :outfit="overlayState.currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="toggleOutfitOverlay(null)"
    />
</template>

<style scoped>
.content-card {
    content-visibility: auto;
    contain-intrinsic-size: 140px 140px;
}

@media (min-width: 768px) {
    .content-card {
        contain-intrinsic-size: 240px 240px;
    }
}
</style>
