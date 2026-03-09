<script setup>
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
const props = defineProps({
    isEdit: Boolean,
});

const emit = defineEmits(['close', 'submit']);
</script>

<template>
    <div
        class="flex items-center justify-between w-full rounded-t-xl p-3 border-b"
    >
        <ArrowLeft
            class="cursor-pointer"
            :size="30"
            fillColor="#000000"
            @click="emit('close')"
        />

        <div class="text-lg font-extrabold">
            {{ isEdit ? 'コーディネート編集' : '新規投稿' }}
        </div>

        <button
            class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
            @click="emit('submit')"
        >
            {{ isEdit ? '更新' : '投稿' }}
        </button>
    </div>
</template>
