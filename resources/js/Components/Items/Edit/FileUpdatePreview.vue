<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    fileDisplay: String,
    formErrors: Object,
    isValidFile: Boolean,
    initialImage: {
        type: [String, File],
        default: null,
    },
});

const emit = defineEmits(['file-selected']);
const fileInput = ref(null);

const selectNewImage = () => {
    fileInput.value?.click();
};

const handleFileChange = (e) => {
    emit('file-selected', e);
};

const showError = computed(() => {
    if (props.formErrors?.file) {
        return Array.isArray(props.formErrors.file)
            ? props.formErrors.file[0]
            : props.formErrors.file;
    }
    if (props.isValidFile === false) {
        return 'ファイルが受け付けられませんでした。';
    }
    return null;
});

const previewImage = computed(() => {
    if (props.fileDisplay) {
        return props.fileDisplay;
    }

    if (typeof File !== 'undefined' && props.initialImage instanceof File) {
        return URL.createObjectURL(props.initialImage);
    }

    return props.initialImage || null;
});
</script>

<template>
    <div
        class="relative flex items-center bg-gray-100 w-full md:w-1/2 h-full md:h-auto overflow-hidden cursor-pointer"
        @click="selectNewImage()"
    >
        <input
            ref="fileInput"
            class="hidden"
            type="file"
            aria-label="画像を選択"
            @change="handleFileChange($event)"
        />
        <div
            v-if="!previewImage && !showError"
            class="flex flex-col items-center mx-auto absolute top-3 left-6"
        >
            <div
                class="hover:bg-blue-700 bg-blue-500 rounded-lg p-2.5 text-white font-extrabold"
            >
                写真を選択する
            </div>
        </div>
        <!-- プレビュー画像 -->
        <img
            v-if="previewImage && isValidFile !== false"
            :key="previewImage"
            class="h-full w-full object-contain mx-auto p-4 cursor-pointer"
            :src="previewImage"
            alt="プレビュー画像"
        />

        <div
            v-if="showError"
            class="absolute inset-0 flex items-center justify-center text-red-500 text-center p-2 font-bold"
        >
            {{ showError }}
        </div>
    </div>
</template>
