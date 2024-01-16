<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

import ShowOutfitOverlay from './ShowOutfitOverlay.vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import Comment from 'vue-material-design-icons/Comment.vue';

let isHover = ref(false);
let currentOutfit = ref(null);
let openOverlay = ref(false);

const authStore = useAuthStore();
const outfits = ref([]);

const fetchOutfits = async () => {
    try {
        const response = await axios.get(`/api/users/${authStore.user.id}`);
        outfits.value = response.data.outfits;
    } catch (error) {
        console.error(error);
    }
};

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
                fetchOutfits();
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

onMounted(() => {
    fetchOutfits();
    window.addEventListener('outfit-updated', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('outfit-updated', fetchOutfits);
});
</script>

<template>
    <div
        @mouseenter="isHover = true"
        @mouseleave="isHover = false"
        class="flex items-center justify-center cursor-pointer relative"
        v-for="outfit in outfits"
        :key="outfit.id"
        @click="openOutfitOverlay(outfit)"
    >
        <div
            v-if="isHover"
            :class="isHover ? 'bg-black bg-opacity-40' : ''"
            class="absolute w-full h-full z-50 flex items-center justify-around text-lg font-extrabold text-white"
        >
            <div class="flex items-center justify-around w-[50%]">
                <div class="flex items-center justify-center">
                    <Heart fillColor="#FFFFFF" :size="30" />
                    <div class="pl-1">3</div>
                </div>
                <div class="flex items-center justify-center">
                    <Comment fillColor="#FFFFFF" :size="30" />
                    <div class="pl-1">5</div>
                </div>
            </div>
        </div>

        <img
            class="aspect-square mx-auto z-0 object-cover cursor-pointer"
            v-if="outfit.file"
            :src="outfit.file"
        />
    </div>
    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
</template>
