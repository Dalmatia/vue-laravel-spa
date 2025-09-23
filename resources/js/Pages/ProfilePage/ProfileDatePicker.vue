<script setup>
import VueDatePicker from '@vuepic/vue-datepicker';

const props = defineProps({
    modelValue: [String, Date],
    age: Number,
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="mt-4 px-4">
        <div class="block text-sm font-medium text-gray-700 mb-1">
            生年月日:
        </div>
        <VueDatePicker
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
            uid="birthdate"
            teleport-center
            locale="ja"
            format="yyyy/MM/dd"
            model-type="yyyy-MM-dd"
            week-start="0"
            :enable-time-picker="false"
            auto-apply
            class="mt-1 block w-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        />

        <p v-if="age !== null" class="mt-2 text-sm text-gray-600">
            年齢: {{ age }} 歳
        </p>
    </div>
</template>
