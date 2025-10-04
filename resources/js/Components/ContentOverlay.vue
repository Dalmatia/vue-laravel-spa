<script setup>
import { ref, watch } from 'vue';
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
const isHover = ref([]);

watch(
    props.outfits,
    (newOutfits) => {
        isHover.value = Array(newOutfits.length).fill(false);
    },
    { immediate: true }
);
</script>

<template>
    <div class="grid md:gap-4 gap-1 grid-cols-3 relative">
        <div
            class="flex items-center justify-center cursor-pointer relative"
            v-for="(outfit, index) in props.outfits"
            :key="outfit.id"
            @click="toggleOutfitOverlay(outfit)"
            @mouseenter="isHover[index] = true"
            @mouseleave="isHover[index] = false"
        >
            <div
                v-if="isHover[index]"
                :class="isHover[index] ? 'bg-black bg-opacity-40' : ''"
                class="absolute w-full h-full z-50 flex items-center justify-around text-lg font-extrabold text-white"
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

            <img
                class="aspect-square mx-auto z-0 object-cover cursor-pointer"
                v-if="outfit.file"
                :src="outfit.file"
            />
        </div>
    </div>
    <ShowOutfitOverlay
        v-if="overlayState.open"
        :outfit="overlayState.currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="toggleOutfitOverlay(null)"
    />
</template>
