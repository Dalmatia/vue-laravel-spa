<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    items: Array,
});
</script>

<template>
    <ul class="flex flex-wrap px-[6px] lg:flex-col lg:gap-6 lg:px-0">
        <li
            v-for="item in items"
            :key="item.label"
            class="group w-1/3 px-[10px] lg:flex lg:w-full lg:flex-col lg:gap-6 lg:px-0"
        >
            <div class="lg:flex">
                <a
                    class="flex justify-center border border-gray-300 lg:h-[120px] lg:w-[100px] lg:shrink-0 lg:overflow-hidden lg:rounded-[3px] lg:border-none lg:hover:opacity-70"
                    :href="`/items/${item.data.id}`"
                >
                    <span class="item_style">
                        <img :src="item.data.file" class="item_image" />
                    </span>
                </a>
                <div class="min-w-0 lg:grow lg:pl-[18px] lg:pr-[23px]">
                    <p
                        class="truncate pt-1 text-[10px] leading-[1.4] lg:mt-[-3px] lg:pt-0 lg:text-[15px] lg:font-bold lg:leading-[1.6] lg:tracking-wide"
                    >
                        <span class="hidden lg:inline">{{ item.label }}</span>
                    </p>
                    <p
                        v-if="item.category && item.color"
                        class="truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                    >
                        <a
                            :href="`/items/${item.data.id}`"
                            class="hidden lg:inline lg:text-blue-500 xl:hover:underline"
                        >
                            {{ item.category }} ({{ item.color }})
                        </a>
                    </p>
                    <p
                        v-else
                        class="text-gray-400 truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                    >
                        読み込み中...
                    </p>
                </div>
            </div>
            <div class="hidden lg:block lg:group-last:hidden">
                <div class="bg-gray-300 h-[1px]"></div>
            </div>
        </li>
    </ul>
</template>
