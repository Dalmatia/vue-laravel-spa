<script setup>
import Upload from 'vue-material-design-icons/Upload.vue';

const props = defineProps({
    fileDisplay: {
        type: [String, File, null],
        default: null,
    },
    userFile: {
        type: [String, File, null],
        default: null,
    },
});
const emit = defineEmits(['change']);
</script>

<template>
    <div class="max-w-[200px] mx-auto relative">
        <div
            class="flex justify-center items-center border-2 border-gray-300 rounded-full overflow-hidden h-[200px] w-[200px] bg-gray-300"
        >
            <!-- プロフィール画像 -->
            <img
                :src="fileDisplay"
                alt="プロフィール画像"
                class="object-cover h-full w-full"
            />
        </div>

        <!-- 編集ボタン（画像アップロード用） -->
        <button
            type="button"
            class="absolute bottom-2 right-2 bg-white border border-gray-300 rounded-full p-2 shadow hover:shadow-lg pointer-events-none"
        >
            <Upload fill="none" class="h-6 w-6 text-gray-700" />
        </button>

        <input
            type="file"
            class="absolute inset-0 opacity-0 cursor-pointer"
            @change="emit('change', $event)"
        />
    </div>
</template>
