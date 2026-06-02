<script setup>
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

defineProps({
    currentPage: {
        type: Number,
        required: true,
    },

    lastPage: {
        type: Number,
        required: true,
    },
});

defineEmits(['next-page', 'prev-page']);
</script>

<template>
    <div
        id="pager"
        class="clear-both mt-[20px] pt-0 pr-[10px] pb-20 pl-[10px] table w-full"
    >
        <p class="w-[30%] table-cell text-center align-middle">
            <span
                @click="$emit('prev-page')"
                class="bg-[#f6f7f8] flex justify-center items-center pt-[12px] pb-[12px] rounded-[4px]"
                :class="{
                    'opacity-40 pointer-events-none': currentPage <= 1,
                }"
            >
                <ChevronLeft class="text-[#aaa] text-[20px]" />
            </span>
        </p>

        <p class="text-center table-cell align-middle">
            <span class="tracking-[0.08em] text-[15px] block">
                {{ currentPage }}/{{ lastPage }}
            </span>

            <span class="text-[12px] block"> ページ </span>
        </p>

        <p class="w-[30%] table-cell text-center align-middle">
            <span
                @click="$emit('next-page')"
                class="bg-[#f6f7f8] flex justify-center items-center pt-[12px] pb-[12px] rounded-[4px]"
                :class="{
                    'opacity-40 pointer-events-none': currentPage >= lastPage,
                }"
            >
                <ChevronRight class="text-[#aaa] text-[20px]" />
            </span>
        </p>
    </div>
</template>
