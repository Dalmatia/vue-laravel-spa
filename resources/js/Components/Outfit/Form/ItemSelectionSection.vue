<script setup>
const props = defineProps({
    itemId: Number,
    image: String,
    label: String,
});

const emit = defineEmits(['open']);
</script>

<template>
    <div
        class="container border border-gray-300 h-48 lg:h-56 w-full p-2 cursor-pointer"
        @click="emit('open')"
    >
        <div
            v-if="!itemId"
            class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
        >
            {{ label }}
        </div>
        <img
            v-if="image"
            class="w-full h-full object-contain"
            :src="image"
            :alt="label + '画像'"
        />
    </div>
</template>
