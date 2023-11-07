<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

import ShowItemOverlay from '../Components/ShowItemOverlay.vue';

let currentItem = ref(null);
let openOverlay = ref(false);

const items = ref([]);

const fetchItems = async () => {
    try {
        const response = await axios.get('/api/items');
        items.value = response.data.items;
    } catch (error) {
        console.error(error);
    }
};

const openItemOverlay = (item) => {
    currentItem.value = item;
    openOverlay.value = true;
};

onMounted(() => {
    fetchItems();
    window.addEventListener('item-created', fetchItems);
});

onUnmounted(() => {
    window.removeEventListener('item-created', fetchItems);
});
</script>

<template>
    <div
        v-for="item in items"
        :key="item.id"
        class="flex items-center justify-center cursor-pointer relative"
    >
        <img
            v-if="item.file"
            :src="item.file"
            class="aspect-square mx-auto z-0 object-cover cursor-pointer"
            @click="openItemOverlay(item)"
        />
    </div>
    <ShowItemOverlay
        v-if="openOverlay"
        :item="currentItem"
        @closeOverlay="openOverlay = false"
    />
</template>
