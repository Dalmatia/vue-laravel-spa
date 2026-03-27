<script setup>
const props = defineProps({
    modelValue: [String, Number],
    isMobile: Boolean,
    subCategories: Array,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
        <label
            for="subCategory"
            :class="
                isMobile
                    ? 'block mb-1 text-base'
                    : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
            "
        >
            サブカテゴリー
        </label>
        <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
            <select
                id="subCategory"
                :value="modelValue"
                @change="emit('update:modelValue', $event.target.value)"
                :class="isMobile ? 'w-full border-b pb-2 text-right' : 'w-full'"
            >
                <option value="">
                    {{ isMobile ? '指定なし' : 'サブカテゴリーを選択' }}
                </option>
                <option
                    v-for="subCategory in subCategories"
                    :key="subCategory.id"
                    :value="subCategory.id"
                >
                    {{ subCategory.name }}
                </option>
            </select>
        </div>
    </div>
</template>
