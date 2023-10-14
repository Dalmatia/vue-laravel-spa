<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);

const fetchItems = async () => {
    try {
        const response = await axios.get('/api/items');
        items.value = response.data.items;
        console.log(items.value);
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    fetchItems();
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
        />
    </div>
</template>
