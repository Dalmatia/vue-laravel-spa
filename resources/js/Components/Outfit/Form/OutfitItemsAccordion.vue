<script setup>
import ItemSelectionSection from '@/Components/Outfit/Form/ItemSelectionSection.vue';
import SelectItemsOverlay from '@/Components/Outfit/Form/SelectItemsOverlay.vue';

const props = defineProps({
    itemTypeEntries: Array,
    getItemByRole: Function,
    isOpen: Boolean,
    showItemSelectionModal: Boolean,
    selectedItemType: Number,
});

const emit = defineEmits([
    'toggleAccordion',
    'openModal',
    'itemSelected',
    'closeItemModal',
]);
</script>

<template>
    <div
        class="flex items-center justify-between border-b p-3 cursor-pointer"
        @click="emit('toggleAccordion')"
    >
        <div class="text-lg font-extrabold text-gray-500">着用アイテム</div>

        <svg
            class="w-3 transition-all duration-200 transform"
            :class="isOpen ? 'rotate-180' : 'rotate-0'"
            viewBox="0 0 16 10"
        >
            <path
                d="M15 1.2l-7 7-7-7"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </div>

    <div
        class="transition-all duration-400 overflow-y-auto"
        :class="isOpen ? 'max-h-full' : 'max-h-0'"
    >
        <div class="p-2 grid grid-cols-2 gap-4">
            <ItemSelectionSection
                v-for="[type, { label }] in itemTypeEntries"
                :key="type"
                :itemId="getItemByRole(Number(type))?.item_id"
                :image="getItemByRole(Number(type))?.file"
                :label="label"
                @open="emit('openModal', Number(type))"
            />
        </div>
    </div>

    <SelectItemsOverlay
        v-if="showItemSelectionModal"
        :itemType="selectedItemType ?? null"
        @onItemSelected="emit('itemSelected', $event)"
        @close="emit('closeItemModal')"
    />
</template>
