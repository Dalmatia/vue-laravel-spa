<script setup>
import FileUploadPreview from '@/Components/FileUploadPreview.vue';
import FileUpdatePreview from '@/Components/FileUpdatePreview.vue';

const props = defineProps({
    isEdit: Boolean,
    fileDisplay: String,
    error: Object,
    isValidFile: Boolean,
    initialImage: String,
});

const emit = defineEmits(['file-selected']);
</script>

<template>
    <FileUploadPreview
        v-if="!isEdit"
        :fileDisplay="fileDisplay"
        :formErrors="error"
        :isValidFile="isValidFile"
        @file-selected="emit('file-selected', $event)"
    />

    <FileUpdatePreview
        v-else
        :file-display="fileDisplay"
        :form-errors="error"
        :is-valid-file="isValidFile"
        :initial-image="initialImage"
        @file-selected="emit('file-selected', $event)"
    />
</template>
