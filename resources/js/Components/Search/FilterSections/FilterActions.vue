<script setup>
const props = defineProps({
    isMobile: Boolean,
});

const emit = defineEmits(['request-clear', 'request-submit']);
</script>

<template>
    <div
        :class="[
            isMobile
                ? 'flex flex-col items-center space-y-3 px-5 mt-6'
                : 'flex flex-row justify-center items-center gap-x-4 mt-6 max-w-[750px] mx-auto mb-7',
        ]"
    >
        <button
            type="button"
            :class="[
                'text-[#999] border border-[#ccc] rounded-sm text-sm font-bold h-[45px] bg-white',
                isMobile ? 'w-full' : 'w-[210px]',
            ]"
            @click="emit('request-clear')"
        >
            指定した条件をクリア
        </button>
        <button
            type="button"
            :class="[
                'bg-black text-white font-bold rounded-sm text-sm h-[45px]',
                isMobile ? 'w-full' : 'w-[210px]',
            ]"
            @click.prevent="emit('request-submit')"
        >
            この条件で絞り込む
        </button>
    </div>
</template>
