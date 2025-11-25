<script setup>
const props = defineProps({
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'change']);

const toggle = () => {
    emit('update:modelValue', !props.modelValue);
    emit('change');
};
</script>

<template>
    <button
        @click="toggle"
        class="relative w-16 h-8 rounded-full transition-colors duration-300 focus:outline-none"
        :class="modelValue ? 'bg-blue-500' : 'bg-gray-300'"
    >
        <div
            class="absolute top-0.5 left-0.5 w-7 h-7 bg-white rounded-full shadow-md transition-all duration-300"
            :class="modelValue ? 'translate-x-8' : 'translate-x-0'"
        ></div>
    </button>
</template>
