<script setup>
import { ref, onMounted, toRefs } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';
import ShowOutfitOverlay from '@/Components/ShowOutfitOverlay.vue';

import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

let wWidth = ref(window.innerWidth);
let currentSlide = ref(0);
let currentOutfit = ref(null);
let openOverlay = ref(false);

// const props = defineProps({ posts: Object, allUsers: Object });
// const { posts, allUsers } = toRefs(props);
const outfits = ref([]);

// 投稿したコーディネートの表示
const fetchOutfits = async () => {
    try {
        const response = await axios.get('/api/home');
        outfits.value = response.data.outfits;
    } catch (error) {
        console.error(error);
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

onMounted(() => {
    window.addEventListener('resize', () => {
        wWidth.value = window.innerWidth;
    });
    fetchOutfits();
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
                    <a
                        class="flex items-center no-underline hover:underline text-black"
                        href="#"
                    >
                        <img
                            alt="Placeholder"
                            class="block rounded-full"
                            src="https://picsum.photos/id/32/32/32"
                        />
                        <p class="ml-2 text-xs">{{ outfit.user.name }}</p>
                    </a>
                    <a
                        href="#"
                        class="float-right bg-blue-500 py-1 text-white font-semibold text-sm rounded text-center hidden md:inline-block"
                    >
                        フォロー
                    </a>
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
