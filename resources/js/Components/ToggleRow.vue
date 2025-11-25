<script setup>
import ReusableToggle from '@/components/ReusableToggle.vue';

defineProps({
    label: String,
    modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue', 'change']);
</script>

<template>
    <div class="flex items-center justify-between py-2">
        <span>{{ label }}</span>

        <ReusableToggle
            :model-value="modelValue"
            @update:modelValue="emit('update:modelValue', $event)"
            @change="emit('change')"
        />
    </div>
</template>
