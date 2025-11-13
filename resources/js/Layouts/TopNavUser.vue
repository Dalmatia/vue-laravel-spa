<script setup>
import { defineProps } from 'vue';
import { useRouter } from 'vue-router';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';

const props = defineProps({
    title: { type: String, default: '' },
    showBackButton: { type: Boolean, default: true },
    backRoute: { type: [String, Object, null], default: null },
    sticky: { type: Boolean, default: true },
});

const router = useRouter();

const goBack = () => {
    if (props.backRoute) {
        router.push(props.backRoute);
    } else {
        router.back();
    }
};
</script>

<template>
    <header
        :class="[
            'fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300 px-4',
            sticky ? 'fixed top-0' : '',
        ]"
    >
        <!-- 左側: 戻るボタン or slot -->
        <div class="flex justify-start items-center min-w-[3rem]">
            <slot name="left">
                <button
                    v-if="showBackButton"
                    @click="goBack"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <ChevronLeft :size="28" />
                </button>
            </slot>
        </div>

        <!-- 中央: タイトル or slot -->
        <div class="flex-1 text-center font-bold text-lg truncate">
            <slot name="center">{{ title }}</slot>
        </div>

        <!-- 右側: slot -->
        <div class="flex justify-end items-center min-w-[3rem]">
            <slot name="right"></slot>
        </div>
    </header>
</template>
