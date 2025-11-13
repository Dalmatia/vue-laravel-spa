<script setup>
import { defineProps, useId } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: String,
    required: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue']);
const inputId = useId();
</script>

<template>
    <div class="xl:flex xl:items-center xl:pt-[7px]">
        <label
            :for="inputId"
            class="flex w-full items-center border-y border-gray-300 bg-gray-50 px-[15px] py-[5px] text-[12px] text-gray-900/60 xl:border-none xl:bg-transparent xl:p-0 xl:text-[14px] xl:leading-normal xl:text-black-400 gap-1"
        >
            {{ label }}
            <span v-if="required" class="text-red-500 text-[10px]">必須</span>
        </label>
    </div>

    <div class="px-4 xl:p-0 py-3">
        <input
            :id="inputId"
            type="password"
            :placeholder="placeholder"
            class="h-9 w-full rounded-[8px] border border-gray-300 px-3 text-[13px] placeholder:text-gray-500 focus:border-blue-400 focus:outline-0 xl:rounded-[4px] xl:text-[14px]"
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>
