<script setup>
import { computed } from 'vue';

const props = defineProps({
    fileDisplay: String,
    formErrors: Object,
    isValidFile: Boolean,
});

const emit = defineEmits(['file-selected']);

const handleFileChange = (e) => {
    emit('file-selected', e);
};

const showError = computed(() => {
    return (
        props.formErrors?.file?.[0] ||
        (props.isValidFile === false
            ? 'ファイルが受け付けられませんでした。'
            : null)
    );
});
</script>

<template>
    <div class="flex items-center bg-gray-100 w-full h-full overflow-hidden">
        <div v-if="!fileDisplay" class="flex flex-col items-center mx-auto">
            <label
                for="file"
                class="hover:bg-blue-700 bg-blue-500 rounded-lg p-2.5 text-white font-extrabold cursor-pointer"
            >
                写真を選択する
            </label>
            <input
                id="file"
                class="hidden"
                type="file"
                @change="handleFileChange($event)"
            />
            <div
                v-if="showError"
                class="text-red-500 text-center p-2 font-extrabold"
            >
                {{ showError }}
            </div>
        </div>
        <img
            v-if="fileDisplay && isValidFile === true"
            class="h-full w-full object-contain mx-auto p-4"
            :src="fileDisplay"
        />
    </div>
</template>
