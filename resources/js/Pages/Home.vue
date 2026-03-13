<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useFollowStore } from '../stores/follow';
import OutfitCard from '../Components/Outfit/OutfitCard.vue';
import { useOutfitOverlay } from '../src/composables/useOutfitOverlay';

import ShowOutfitOverlay from '@/Components/Outfit/ShowOutfitOverlay.vue';
import WeatherForecast from './WeatherForecast.vue';

let wWidth = ref(window.innerWidth);
const outfits = ref([]);
const followStore = useFollowStore();
const { overlayState, toggleOutfitOverlay, deleteOutfit } = useOutfitOverlay();

const resizeHandler = () => {
    wWidth.value = window.innerWidth;
};

// 投稿したコーディネートの表示
const fetchOutfits = async () => {
    try {
        const response = await axios.get('/api/home');
        outfits.value = response.data.outfits;

        // 各ユーザーのフォロー状態をチェック
        const follows = outfits.value.map((outfit) => outfit.user.id);
        await followStore.fetchFollowStatus(follows);
    } catch (error) {
        console.error('コーディネート一覧の取得に失敗しました。', error);
    }
};

onMounted(() => {
    window.addEventListener('resize', resizeHandler);
    fetchOutfits();
    window.addEventListener('outfit-created', fetchOutfits);
    window.addEventListener('outfit-updated', fetchOutfits);
    window.addEventListener('outfit-deleted', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('resize', resizeHandler);
    window.removeEventListener('outfit-created', fetchOutfits);
    window.removeEventListener('outfit-updated', fetchOutfits);
    window.removeEventListener('outfit-deleted', fetchOutfits);
});
</script>

<template>
    <div id="Posts" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <!-- 天気予報 -->
        <WeatherForecast />
        <!-- コーディネート一覧 -->
        <span class="font-bold text-lg pr-6">他ユーザーのコーディネート</span>
        <div class="grid gap-6 mt-4">
            <OutfitCard
                v-for="outfit in outfits"
                :key="outfit.id"
                :outfit="outfit"
                @click="toggleOutfitOverlay(outfit)"
            />
        </div>
        <div class="pb-20"></div>
    </div>

    <ShowOutfitOverlay
        v-if="overlayState.open"
        :outfit="overlayState.currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="toggleOutfitOverlay(null)"
    />
</template>
