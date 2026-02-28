<script setup>
import { specialColors } from '../src/specialColors.js';

const props = defineProps({
    colors: Array,
    selectedColor: Number,
});

const emit = defineEmits(['close', 'colorSelected']);
const { getColorClass, getColorStyle } = specialColors();

const selectColor = (color) => {
    // 同じ色がクリックされた場合は選択解除（null を返す）
    if (color.id === props.selectedColor) {
        emit('colorSelected', null); // ← 選択解除
    } else {
        // 選択された色を親コンポーネントに伝える
        emit('colorSelected', color);
    }
    emit('close'); // モーダルを閉じる
};
</script>

<template>
    <div
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 px-4"
    >
        <div
            class="bg-white p-6 rounded-md max-w-md w-full max-h-screen overflow-y-auto shadow-lg"
        >
            <h2 class="text-lg font-semibold mb-4 text-center">カラーを選択</h2>
            <div class="flex flex-wrap gap-3 justify-center">
                <div
                    v-for="color in colors"
                    :key="color.id"
                    class="flex flex-col items-center"
                >
                    <div
                        class="w-12 h-12 rounded cursor-pointer border-2 transition-all duration-150"
                        :style="getColorStyle(color)"
                        @click="selectColor(color)"
                        :class="[
                            getColorClass(color),
                            color.id === selectedColor
                                ? 'border-black'
                                : 'border-gray-300',
                        ]"
                    ></div>
                    <span class="mt-1 text-sm text-gray-700 text-center">
                        {{ color.name }}
                    </span>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button
                    class="text-gray-500 hover:text-gray-700 transition"
                    @click="emit('close')"
                >
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</template>
