<script setup>
import { computed } from 'vue';
const props = defineProps({
    modelValue: [String, Number],
    label: String,
    id: String,
    type: { type: String, default: 'text' },
    autocomplete: String,
    error: Array,
});
const emit = defineEmits(['update:modelValue']);
const inputValue = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
});
</script>

<template>
    <div>
        <label
            :for="id"
            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
        >
            {{ label }}:
        </label>
        <input
            :type="type"
            :id="id"
            :autocomplete="autocomplete"
            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
            v-model="inputValue"
        />
        <span v-if="error" class="text-sm text-red-700 m-1">
            {{ error[0] }}
        </span>
    </div>
</template>
