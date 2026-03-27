<script setup>
import Male from 'vue-material-design-icons/HumanMale.vue';
import Female from 'vue-material-design-icons/HumanFemale.vue';
import Child from 'vue-material-design-icons/HumanChild.vue';

const props = defineProps({
    genders: Array,
    modelValue: Number,
});

const emit = defineEmits(['update:modelValue', 'close']);

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
</script>

<template>
    <div
        class="fixed inset-0 bg-black/50 z-50 flex items-end"
        @click.self="emit('close')"
    >
        <div class="bg-white w-full rounded-t-xl p-4">
            <div
                v-for="gender in genders"
                :key="gender.value"
                class="flex items-center gap-3 p-3 border-b cursor-pointer"
                @click="
                    emit('update:modelValue', gender.value);
                    emit('close');
                "
            >
                <component
                    :is="genderIcons[gender.value]"
                    :class="genderIconColors[gender.value]"
                />
                <span>{{ gender.label }}</span>
            </div>
        </div>
    </div>
</template>
