<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    sortOrder: String,
    sortOptions: Object,
});

const emit = defineEmits(['update:sortOrder']);

const sortDropdown = ref(null);
const showSortOptions = ref(false);

const toggleDropdown = () => {
    showSortOptions.value = !showSortOptions.value;
};

const handleClickOutside = (event) => {
    if (sortDropdown.value && !sortDropdown.value.contains(event.target)) {
        showSortOptions.value = false;
    }
};

const handleChangeSort = (key) => {
    emit('update:sortOrder', key);
    showSortOptions.value = false;
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="sortDropdown">
        <button
            class="bg-transparent border-none cursor-pointer"
            @click.stop="toggleDropdown()"
        >
            <slot name="icon" />
        </button>

        <!-- ドロップダウンメニュー -->
        <transition
            name="fade-slide"
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150"
        >
            <ul
                v-if="showSortOptions"
                class="absolute left-0 mt-4 md:mt-0 w-40 bg-white border border-gray-200 rounded shadow-md z-50"
            >
                <li
                    v-for="(label, key) in sortOptions"
                    :key="key"
                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                    :class="{
                        'bg-gray-100 font-semibold': sortOrder === key,
                    }"
                    @click="handleChangeSort(key)"
                >
                    {{ label }}
                </li>
            </ul>
        </transition>
    </div>
</template>

<style scoped>
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}
.fade-slide-enter-to {
    opacity: 1;
    transform: translateY(0);
}
.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
