<script setup>
import { ref } from 'vue';

const message = ref('');
const visible = ref(false);

const show = (text, duration = 2000) => {
    message.value = text;
    visible.value = true;
    setTimeout(() => (visible.value = false), duration);
};

defineExpose({ show });
</script>

<template>
    <transition name="fade">
        <div
            v-if="visible"
            class="fixed right-1 md:top-5 md:right-5 bg-black text-white px-4 py-2 rounded shadow-lg z-50"
        >
            {{ message }}
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
