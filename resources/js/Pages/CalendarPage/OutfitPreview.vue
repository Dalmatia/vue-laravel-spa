<script setup>
import Plus from 'vue-material-design-icons/Plus.vue';

defineProps({
    selectedDay: { type: String, default: null },
    selectedOutfit: { type: Object, default: null },
});

const emit = defineEmits(['prev', 'next', 'openOutfit', 'createOutfit']);
</script>

<template>
    <div
        v-if="selectedDay"
        class="flex items-center justify-center space-x-4"
        :class="{
            'mt-4': selectedOutfit,
            'mt-10': !selectedOutfit,
        }"
    >
        <!-- ←ボタン -->
        <button @click="emit('prev')" class="px-2 py-1 bg-gray-200 rounded">
            ←
        </button>

        <!-- 投稿あり: プレビュー画像 -->
        <div
            v-if="selectedOutfit"
            class="text-center cursor-pointer"
            @click="emit('openOutfit', selectedDay)"
        >
            <p class="font-bold mb-2">{{ selectedDay }}</p>
            <img
                class="w-40 h-52 md:w-80 md:h-96 mx-auto"
                :src="selectedOutfit.file"
                :alt="`プレビュー: ${selectedDay}`"
            />
        </div>

        <!-- 投稿なし: プラスアイコン -->
        <div
            v-else
            class="w-40 h-52 md:w-80 md:h-96 flex items-center justify-center bg-gray-200 rounded-lg cursor-pointer"
            @click="emit('createOutfit')"
        >
            <Plus class="text-gray-500" :size="32" />
        </div>

        <!-- →ボタン -->
        <button @click="emit('next')" class="px-2 py-1 bg-gray-200 rounded">
            →
        </button>
    </div>
</template>
