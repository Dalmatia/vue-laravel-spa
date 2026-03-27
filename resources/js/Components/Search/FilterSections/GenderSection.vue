<script setup>
import { computed } from 'vue';

import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import Male from 'vue-material-design-icons/HumanMale.vue';
import Female from 'vue-material-design-icons/HumanFemale.vue';
import Child from 'vue-material-design-icons/HumanChild.vue';

const props = defineProps({
    isMobile: Boolean,
    modelValue: Number,
    genders: Array,
});

const emit = defineEmits(['update:modelValue', 'open']);

const genderIcons = {
    1: Male,
    2: Female,
    3: Child,
};

const genderIconColors = {
    1: 'text-blue-500',
    2: 'text-pink-400',
    3: 'text-yellow-500',
};

const selectedGender = computed(() => {
    return props.genders.find((g) => g.value === props.modelValue);
});
</script>

<template>
    <div
        :class="
            isMobile
                ? 'mb-4 border-b border-[#f2f2f2]'
                : 'flex border-y border-[#f2f2f2]'
        "
    >
        <span
            :class="
                isMobile
                    ? 'block mb-1 text-base'
                    : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right'
            "
        >
            性別
        </span>
        <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
            <div class="flex items-center cursor-pointer" @click="emit('open')">
                <div
                    v-if="selectedGender"
                    :class="
                        isMobile
                            ? 'ml-auto flex items-center gap-2'
                            : 'flex items-center gap-2'
                    "
                >
                    <component
                        :is="genderIcons[selectedGender.value]"
                        :class="genderIconColors[selectedGender.value]"
                    />
                    <span>{{ selectedGender.label }}</span>
                </div>
                <div v-else>性別を選択</div>

                <ChevronRight />
            </div>
        </div>
    </div>
</template>
