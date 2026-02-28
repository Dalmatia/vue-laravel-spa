<script setup>
import { ref } from 'vue';
import CheckMark from 'vue-material-design-icons/Check.vue';

let timer;
const message = ref('');
const visible = ref(false);

const show = (text, duration = 2000) => {
    message.value = text;
    visible.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => (visible.value = false), duration);
};

defineExpose({ show });
</script>

<template>
    <transition name="fade">
        <div
            v-if="visible"
            class="flex items-center fixed right-1 md:top-5 md:right-5 bg-black text-white px-4 py-2 rounded shadow-lg z-50"
        >
            <CheckMark class="inline-block mr-2 align-middle" />
            <p class="font-bold">{{ message }}</p>
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
