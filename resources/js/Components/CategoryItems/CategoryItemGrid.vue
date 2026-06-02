<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
    },
    subCategoryName: {
        type: Function,
        required: true,
    },
});

defineEmits(['item-click']);
</script>

<template>
    <div class="container grid grid-cols-3 pt-0 pr-0 pb-[20px] pl-0">
        <div
            v-for="item in items"
            :key="item.id"
            class="pt-0 pr-[6px] pb-[5px] pl-[6px] w-full"
        >
            <div class="relative">
                <a @click="$emit('item-click', item)">
                    <img
                        v-if="item.file"
                        :src="item.file"
                        loading="lazy"
                        decoding="async"
                        class="aspect-square w-full object-cover"
                    />
                </a>
            </div>

            <div class="pt-[6px]">
                <p
                    class="text-[10px] font-bold leading-[1.2] w-full whitespace-nowrap overflow-hidden text-ellipsis"
                >
                    {{ subCategoryName(item) }}
                </p>
            </div>
        </div>
    </div>
</template>
