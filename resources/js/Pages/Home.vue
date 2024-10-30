<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useFollowStore } from '../stores/follow';

import ShowOutfitOverlay from '@/Components/Outfits/ShowOutfitOverlay.vue';

import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import Follow from 'vue-material-design-icons/AccountPlusOutline.vue';
import unFollow from 'vue-material-design-icons/AccountCheckOutline.vue';

let wWidth = ref(window.innerWidth);
let currentSlide = ref(0);
let currentOutfit = ref(null);
let openOverlay = ref(false);

const outfits = ref([]);
const authStore = useAuthStore();
const user = computed(() => (authStore.user ? authStore.user.id : null));
const followStore = useFollowStore();

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
                window.dispatchEvent(new Event('outfit-deleted'));
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

const openOutfitOverlay = (outfit) => {
    currentOutfit.value = outfit;
    openOverlay.value = true;
};

const followUser = async (userId) => {
    try {
        await followStore.pushFollow(userId);
        followStore.status[userId] = true;
    } catch (error) {
        console.error(error);
    }
};

// フォロー解除する
const deleteFollow = async (userId) => {
    try {
        await followStore.deleteFollow(userId);
        followStore.status[userId] = false;
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    window.addEventListener('resize', () => {
        wWidth.value = window.innerWidth;
    });
    fetchOutfits();
    window.addEventListener('outfit-created', fetchOutfits);
    window.addEventListener('outfit-updated', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', fetchOutfits);
    window.removeEventListener('outfit-updated', fetchOutfits);
});
</script>

<template>
    <div id="Posts" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <div class="grid md:gap-4 gap-1 grid-cols-3 relative">
            <article
                class="overflow-hidden rounded-lg shadow-lg"
                v-for="outfit in outfits"
                :key="outfit.id"
            >
                <a @click="openOutfitOverlay(outfit)">
                    <img
                        class="block h-[193px] w-[177px] md:h-[300px] md:w-full"
                        :src="outfit.file"
                    />
                </a>
                <p class="text-grey-darker text-sm">{{ outfit.outfit_date }}</p>
                <footer
                    class="flex items-center justify-between leading-none mt-3 md:p-4"
                >
                    <div
                        class="flex items-center no-underline hover:underline text-black"
                    >
                        <div
                            class="rounded-full w-8 h-8 overflow-hidden border-2"
                        >
                            <img
                                alt="Placeholder"
                                class="w-full h-full object-cover"
                                :src="outfit.user.file"
                            />
                        </div>
                        <p class="ml-2 text-xs">{{ outfit.user.name }}</p>
                    </div>
                    <div class="col-md-3">
                        <div
                            class="follow"
                            @click="followUser(outfit.user.id)"
                            v-if="
                                outfit.user.id !== user &&
                                !followStore.followStatus(outfit.user.id)
                            "
                        >
                            <Follow />
                        </div>
                        <div
                            class="unfollow"
                            @click="deleteFollow(outfit.user.id)"
                            v-if="
                                outfit.user.id !== user &&
                                followStore.followStatus(outfit.user.id)
                            "
                        >
                            <unFollow />
                        </div>
                    </div>
                </footer>
            </article>
        </div>
    </div>

    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
</template>
