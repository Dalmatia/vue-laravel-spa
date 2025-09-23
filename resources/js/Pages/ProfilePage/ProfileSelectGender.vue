<script setup>
const props = defineProps({
    genders: Array,
    modelValue: Number,
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="mt-4 px-4">
        <label
            for="gender"
            class="block text-sm font-medium text-gray-700 mb-1"
        >
            性別:
        </label>
        <select
            id="gender"
            :value="modelValue"
            @change="emit('update:modelValue', Number($event.target.value))"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
            <option
                v-for="gender in genders"
                :key="gender.value"
                :value="gender.value"
            >
                {{ gender.label }}
            </option>
        </select>
    </div>
</template>
