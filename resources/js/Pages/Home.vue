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
            <ShowPostOverlay />
        </div>
    </div>
</template>
