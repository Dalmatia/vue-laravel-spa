<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';

import ShowOutfitOverlay from './Outfits/ShowOutfitOverlay.vue';

import Heart from 'vue-material-design-icons/Heart.vue';
import Comment from 'vue-material-design-icons/Comment.vue';

let currentOutfit = ref(null);
let openOverlay = ref(false);
const route = useRoute();
const outfits = ref([]);
const isHover = ref(Array(outfits.value.length).fill(false));

const fetchOutfits = async (userId) => {
    try {
        const response = await axios.get(`/api/users/${userId}`);
        outfits.value = response.data.outfits;
    } catch (error) {
        console.error(error);
    }
};

// 投稿作成時の更新
const onOutfitCreated = () => {
    fetchOutfits(route.params.id);
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
                window.dispatchEvent(new Event('outfit-deleted'));
                fetchOutfits(route.params.id);
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

watch(
    () => route.params.id,
    (newId) => {
        if (newId) {
            fetchOutfits(newId);
        }
    },
    { immediate: true }
);

onMounted(() => {
    const userId = route.params.id;
    if (userId) {
        fetchOutfits(userId);
    }
    window.addEventListener('outfit-created', onOutfitCreated);
    window.addEventListener('outfit-updated', onOutfitCreated);
});

// コンポーネントアンマウント時
onUnmounted(() => {
    window.removeEventListener('outfit-created', onOutfitCreated);
    window.removeEventListener('outfit-updated', onOutfitCreated);
});
</script>

<template>
    <div class="grid md:gap-4 gap-1 grid-cols-3 relative">
        <div
            class="flex items-center justify-center cursor-pointer relative"
            v-for="(outfit, index) in outfits"
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
    </div>
    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
</template>
