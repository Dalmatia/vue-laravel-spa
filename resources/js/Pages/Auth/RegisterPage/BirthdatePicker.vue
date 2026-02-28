<script setup>
import VueDatePicker from '@vuepic/vue-datepicker';

const props = defineProps({
    modelValue: {
        type: [String, Date, null],
        default: null,
    },
    age: {
        type: Number,
        default: null,
    },
    error: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label
            for="birthdate"
            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
        >
            生年月日:
        </label>

        <VueDatePicker
            id="birthdate"
            uid="birthdate"
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
            teleport-center
            locale="ja"
            format="yyyy/MM/dd"
            model-type="yyyy-MM-dd"
            value-type="format"
            week-start="0"
            :enable-time-picker="false"
            auto-apply
            :ui="{ input: 'my-custom-input-class' }"
        />

        <p
            v-if="age !== null"
            class="mt-2 text-sm text-gray-600 text-right pr-2"
        >
            年齢: {{ age }} 歳
        </p>

        <span
            v-if="error && error.length"
            class="text-sm text-red-700 m-1"
            role="alert"
        >
            {{ error[0] }}
        </span>
    </div>
</template>

<style>
.my-custom-input-class {
    width: 100%;
    background-color: #f9fafb;
    color: #1f2937;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    outline: none;
    transition: all 0.1s ease-in-out;
}

.my-custom-input-class:focus {
    box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.75);
}
</style>
