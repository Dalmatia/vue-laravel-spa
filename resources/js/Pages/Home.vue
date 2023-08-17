<script setup>
import { ref, onMounted, toRefs } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';
import ShowPostOverlay from '@/Components/ShowPostOverlay.vue';

import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

let wWidth = ref(window.innerWidth);
let currentSlide = ref(0);
let currentPost = ref(null);
let openOverlay = ref(false);

const props = defineProps({ posts: Object, allUsers: Object });
const { posts, allUsers } = toRefs(props);

onMounted(() => {
    window.addEventListener('resize', () => {
        wWidth.value = window.innerWidth;
    });
});
</script>

<template>
    <div id="Posts" class="md:pr-1.5 lg:pl-0 md:pl-[90px]">
        <div class="grid md:gap-4 gap-1 grid-cols-3 relative">
            <article class="overflow-hidden rounded-lg shadow-lg">
                <a @click="openOverlay = true">
                    <img
                        class="block h-[193px] w-[177px] md:h-auto md:w-full"
                        src="https://picsum.photos/id/600/480/?random"
                    />
                </a>
                <p class="text-grey-darker text-sm">11/1/19</p>
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
                        <p class="ml-2 text-xs">Author Name</p>
                    </a>
                    <a
                        href="#"
                        class="float-right bg-blue-500 py-1 text-white font-semibold text-sm rounded text-center hidden md:inline-block"
                    >
                        フォロー
                    </a>
                </footer>
            </article>

            <article class="overflow-hidden rounded-lg shadow-lg">
                <a href="/">
                    <img
                        class="block h-[193px] w-[177px] md:h-auto md:w-full"
                        src="https://picsum.photos/id/600/480/?random"
                    />
                </a>
                <p class="text-grey-darker text-sm">11/1/19</p>
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
                        <p class="ml-2 text-xs">Author Name</p>
                    </a>
                    <a
                        href="#"
                        class="float-right bg-blue-500 py-1 text-white font-semibold text-sm rounded text-center hidden md:inline-block"
                    >
                        フォロー
                    </a>
                </footer>
            </article>

            <article class="overflow-hidden rounded-lg shadow-lg">
                <a href="/">
                    <img
                        class="block h-[193px] w-[177px] md:h-auto md:w-full"
                        src="https://picsum.photos/id/600/480/?random"
                    />
                </a>
                <p class="text-grey-darker text-sm">11/1/19</p>
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
                        <p class="ml-2 text-xs">Author Name</p>
                    </a>
                    <a
                        href="#"
                        class="float-right bg-blue-500 py-1 text-white font-semibold text-sm rounded text-center hidden md:inline-block"
                    >
                        フォロー
                    </a>
                </footer>
            </article>

            <article class="overflow-hidden rounded-lg shadow-lg">
                <a href="/">
                    <img
                        class="block h-[193px] w-[177px] md:h-auto md:w-full"
                        src="https://picsum.photos/id/600/480/?random"
                    />
                </a>
                <p class="text-grey-darker text-sm">11/1/19</p>
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
                        <p class="ml-2 text-xs">Author Name</p>
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

    <ShowPostOverlay
        v-if="openOverlay"
        :post="currentPost"
        @closeOverlay="openOverlay = false"
    />
</template>
