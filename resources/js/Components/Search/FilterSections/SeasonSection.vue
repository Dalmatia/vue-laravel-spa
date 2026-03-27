<script setup>
const props = defineProps({
    modelValue: [String, Number],
    isMobile: Boolean,
    seasons: Array,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
        <label
            for="season"
            :class="
                isMobile
                    ? 'block mb-1 text-base'
                    : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
            "
        >
            シーズン
        </label>
        <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
            <select
                id="season"
                :value="modelValue"
                @change="emit('update:modelValue', $event.target.value)"
                :class="isMobile ? 'w-full border-b pb-2 text-right' : 'w-full'"
            >
                <option value="">
                    {{ isMobile ? '指定なし' : 'シーズンを選択' }}
                </option>
                <option
                    v-for="season in seasons"
                    :key="season.id"
                    :value="season.id"
                >
                    {{ season.name }}
                </option>
            </select>
        </div>
    </div>
</template>
