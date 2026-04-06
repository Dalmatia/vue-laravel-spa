<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useOutfitForm } from '@/src/composables/outfit/useOutfitForm';
import { useInitEnums } from '../../../src/composables/useInitEnums';
import { useModal } from '@/src/composables/useModal';
import { useOutfitApi } from '@/src/composables/outfit/useOutfitApi';

import OutfitOverlayHeader from '@/Components/Outfit/Form/OutfitOverlayHeader.vue';
import OutfitImageSection from '@/Components/Outfit/Form/OutfitImageSection.vue';
import OutfitFormBody from '@/Components/Outfit/Form/OutfitFormBody.vue';

import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close']);
const props = defineProps({
    editOutfit: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => !!props.editOutfit);

const user = useAuthStore().user;

const {
    form,
    error,
    itemTypes,
    getItemByRole,
    handleItemSelected,
    fileDisplay,
    isValidFile,
    getUploadedImage,
    buildFormData,
    resetForm,
} = useOutfitForm(props.editOutfit);

const { seasons } = useInitEnums();
const { createOutfit, updateOutfit } = useOutfitApi();

const itemTypeEntries = Object.entries(itemTypes);

const {
    isOpen,
    showItemSelectionModal,
    selectedType: selectedItemType,
    openModal,
    toggleAccordion,
} = useModal();

if (isEdit.value) {
    isOpen.value = true;
}

const onFileSelected = (e) => {
    getUploadedImage(e);

    setTimeout(() => {
        document
            .getElementById('TextAreaSection')
            ?.scrollIntoView({ behavior: 'smooth' });
    }, 300);
};

const submit = async () => {
    try {
        const payload = buildFormData();

        if (isEdit.value) {
            await updateOutfit(form.id, payload);
            window.dispatchEvent(new Event('outfit-updated'));
        } else {
            await createOutfit(payload);
            window.dispatchEvent(new Event('outfit-created'));
        }

        closeOverlay();
    } catch (errors) {
        if (errors.response?.data?.errors) {
            Object.assign(error.value, errors.response.data.errors);
        }
    }
};

function closeOverlay() {
    emit('close');
}
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
        @click.self="closeOverlay"
    >
        <button class="absolute right-3 cursor-pointer" @click="closeOverlay">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl"
        >
            <OutfitOverlayHeader
                :isEdit="isEdit"
                @close="closeOverlay"
                @submit="submit"
            />

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <!-- 画像 -->
                <OutfitImageSection
                    :isEdit="isEdit"
                    :fileDisplay="fileDisplay"
                    :error="error"
                    :isValidFile="isValidFile"
                    :initialImage="form.file"
                    @file-selected="onFileSelected"
                />

                <!-- 右側 -->
                <OutfitFormBody
                    :user="user"
                    :form="form"
                    :error="error"
                    :seasons="seasons"
                    :itemTypeEntries="itemTypeEntries"
                    :getItemByRole="getItemByRole"
                    :isOpen="isOpen"
                    :showItemSelectionModal="showItemSelectionModal"
                    :selectedItemType="selectedItemType"
                    @toggleAccordion="toggleAccordion"
                    @openModal="openModal"
                    @itemSelected="
                        (item) => handleItemSelected(item, selectedItemType)
                    "
                    @closeItemModal="showItemSelectionModal = false"
                />
            </div>
        </div>
    </div>
</template>
