<script setup>
import { ref, watch } from 'vue';

import ShowOutfitOverlay from './Outfits/ShowOutfitOverlay.vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import Comment from 'vue-material-design-icons/Comment.vue';

let currentOutfit = ref(null);
let openOverlay = ref(false);
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

const openOutfitOverlay = (outfit) => {
    currentOutfit.value = outfit;
    openOverlay.value = true;
    return currentOutfit.value;
};

// コーディネートの削除
const deleteOutfit = (object) => {
    let url = '';
    if (object.deleteType === 'Outfit') {
        url = `/api/outfit/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                openOverlay.value = false;
                window.dispatchEvent(new Event('outfit-deleted'));
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

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
            @click="openOutfitOverlay(outfit)"
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
        v-if="openOverlay"
        :outfit="currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
</template>
