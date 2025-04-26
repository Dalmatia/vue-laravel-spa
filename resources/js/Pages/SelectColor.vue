<script setup>
const props = defineProps({
    colors: Array,
    selectedColor: Number,
});

const emit = defineEmits(['close', 'colorSelected']);

const selectColor = (color) => {
    // 同じ色がクリックされた場合は選択解除（null を返す）
    if (color.value === props.selectedColor) {
        emit('colorSelected', null); // ← 選択解除
    } else {
        // 選択された色を親コンポーネントに伝える
        emit('colorSelected', color);
    }
    emit('close'); // モーダルを閉じる
};

// クラスで特殊色のスタイルを分岐
const getColorClass = (color) => {
    if (color.name === 'ネオン') {
        return 'neon-glow';
    } else if (color.name === 'ボーダー柄') {
        return 'border-pattern';
    } else if (color.name === 'パターン柄') {
        return 'patterned-pattern';
    } else if (color.name === 'シルバー') {
        return 'silver';
    } else if (color.name === 'ゴールド') {
        return 'gold';
    } else if (color.name === 'その他') {
        return 'other-color';
    } else {
        return '';
    }
};

// 通常色は直接背景色で表現
const getColorStyle = (color) => {
    const special = [
        'ネオン',
        'ボーダー柄',
        'パターン柄',
        'シルバー',
        'ゴールド',
    ];
    if (special.includes(color.name)) return {};
    return { backgroundColor: color.hex };
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
                    :key="color.value"
                    class="flex flex-col items-center"
                >
                    <div
                        class="w-12 h-12 rounded cursor-pointer border-2 transition-all duration-150"
                        :style="getColorStyle(color)"
                        @click="selectColor(color)"
                        :class="[
                            getColorClass(color),
                            color.value === selectedColor
                                ? 'border-black'
                                : 'border-gray-300',
                        ]"
                    ></div>
                    <span class="mt-1 text-sm text-gray-700 text-center">{{
                        color.name
                    }}</span>
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

<style scoped>
.neon-glow {
    background-color: #39ff14;
    box-shadow: 0 0 10px #39ff14, 0 0 20px #39ff14;
}

.border-pattern {
    background-image: repeating-linear-gradient(
        90deg,
        #d1d5db,
        #d1d5db 5px,
        #ffffff 5px,
        #ffffff 10px
    );
}

.patterned-pattern {
    background-image: repeating-linear-gradient(
            45deg,
            #e5e7eb,
            #e5e7eb 5px,
            #ffffff 5px,
            #ffffff 10px
        ),
        repeating-linear-gradient(
            -45deg,
            #e5e7eb,
            #e5e7eb 5px,
            #ffffff 5px,
            #ffffff 10px
        );
    background-blend-mode: multiply;
}

.silver {
    background: linear-gradient(
        45deg,
        #757575 0%,
        #9e9e9e 45%,
        #e8e8e8 70%,
        #9e9e9e 85%,
        #757575 90% 100%
    );
}

.gold {
    background: linear-gradient(
        45deg,
        #b67b03 0%,
        #daaf08 45%,
        #fee9a0 70%,
        #daaf08 85%,
        #b67b03 90% 100%
    );
}

.other-color {
    background: linear-gradient(to right, red 50%, blue 50%) top,
        linear-gradient(to right, green 50%, yellow 50%) bottom;
    background-size: 100% 50%;
    background-repeat: no-repeat;
}
</style>
