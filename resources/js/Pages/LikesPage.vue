<script setup>
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';
import ShowOutfitOverlay from '../Components/Outfit/ShowOutfitOverlay.vue';

let openOverlay = ref(false);
let currentOutfit = ref(null);
const likes = ref([]);

const fetchLikes = async () => {
    try {
        const response = await axios.get('/api/likes');
        likes.value = response.data.likes;
    } catch (error) {
        console.error('データの取得に失敗しました。:', error);
    }
};

const openOutfitOverlay = (like) => {
    currentOutfit.value = { ...like.outfit, user: like.user };
    openOverlay.value = true;
};

onMounted(() => {
    fetchLikes();
    window.addEventListener('liked-created', fetchLikes);
    window.addEventListener('liked-deleted', fetchLikes);
});

onUnmounted(() => {
    window.removeEventListener('liked-created', fetchLikes);
    window.removeEventListener('liked-deleted', fetchLikes);
});
</script>

<template>
    <div
        class="w-full max-w-[1000px] lg:ml-0 md:ml-[10px] md:pl-12 px-4 md:w-[90vw]"
    >
        <!-- お気に入りコーディネートページヘッダー部分 -->
        <header
            class="pt-[5px] pr-0 pb-[15px] pl-0 bg-white min-h-[10px] border-b-[1px] border-[#ddd] border-solid sticky top-0"
        >
            <div class="table w-full table-fixed">
                <div class="table w-[100%]">
                    <div
                        class="table-cell align-middle pr-[15px] w-[95%] border-r-[1px] border-[#ddd] border-solid"
                    >
                        <h1 class="text-lg leading-[1.5] font-bold break-words">
                            お気に入りコーディネート
                        </h1>
                    </div>
                    <div class="table-cell align-top">
                        <div class="pt-0 pr-0 pb-0 pl-[20px] float-right">
                            <div
                                class="leading-[1.4] text-right whitespace-nowrap"
                            >
                                <span
                                    class="text-[27px] font-bold tracking-wider"
                                >
                                    {{ likes.length }}
                                </span>
                                <span
                                    class="text-[13px] mt-0 mr-0 mb-0 ml-[5px] align-[1px]"
                                    >件</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- お気に入りコーディネート表示部分 -->
        <div
            id="favorite_outfit"
            class="relative h-[calc(100vh-121px)] overflow-y-auto pb-20"
        >
            <div
                id="outfit_list"
                class="container grid grid-cols-3 md:grid-cols-4 pt-0 pr-0 pb-[20px] pl-0"
            >
                <div
                    v-for="like in likes"
                    :key="like.id"
                    class="pt-[10px] pr-[6px] pb-[5px] pl-[6px] w-full"
                >
                    <div
                        class="relative float-left border-[1px] border-[#ddd] border-solid rounded-[3px] md:mt-[18px] md:mr-0 md:mb-0 md:ml-[18px] bg-white"
                    >
                        <a
                            @click.prevent="openOutfitOverlay(like)"
                            class="block"
                        >
                            <p
                                v-if="like.outfit && like.outfit.file"
                                class="relative w-full h-auto overflow-hidden bg-[#f6f7f8]"
                            >
                                <img
                                    :src="like.outfit.file"
                                    class="w-full aspect-[2/3] cursor-pointer"
                                />
                            </p>
                        </a>
                        <div
                            id="user_profile"
                            class="border-t-0 pt-[9px] pr-[10px] pb-[9px] pl-[10px]"
                        >
                            <div
                                id="profile_image"
                                class="relative float-left w-[22px] md:w-8"
                            >
                                <div
                                    id="image"
                                    class="text-[0px] leading-[1] tracking-[0] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                >
                                    <img
                                        v-if="like.user && like.user.file"
                                        :src="like.user.file"
                                        class="opacity-100 rounded-[50%] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                    />
                                </div>
                            </div>
                            <div
                                id="username"
                                class="w-auto float-right pt-[2px] pr-0 pb-0 pl-0"
                            >
                                <p
                                    class="text-[10px] md:text-[13.5px] font-bold"
                                >
                                    <span
                                        v-if="like.user && like.user.name"
                                        class="float-left max-w-full whitespace-nowrap overflow-hidden text-ellipsis"
                                    >
                                        {{ like.user.name }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @close-overlay="openOverlay = false"
    />
</template>
