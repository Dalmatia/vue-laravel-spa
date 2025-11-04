<script setup>
import { useRouter } from 'vue-router';
import EmoticonExcitedOutline from 'vue-material-design-icons/EmoticonExcitedOutline.vue';
const router = useRouter();
</script>

<template>
    <div
        class="fade-in flex flex-col items-center justify-center min-h-[70vh] px-6 text-center"
    >
        <h1
            class="text-[20px] xl:text-[24px] font-bold text-gray-800 mb-5 flex items-center gap-2"
        >
            退会が完了しました
            <EmoticonExcitedOutline
                :size="24"
                class="inline-block text-gray-800"
            />
        </h1>
        <p class="text-gray-600 text-sm leading-relaxed mb-8">
            ご利用いただきありがとうございました。<br />
            またの機会にお会いできることを願っております。
        </p>

        <button
            class="px-8 py-3 rounded-full bg-blue-500 text-white font-semibold transition hover:opacity-80"
            @click="router.push({ name: 'Login' })"
        >
            ログインページへ戻る
        </button>
    </div>
</template>

<style scoped>
@keyframes fadeInSmooth {
    0% {
        opacity: 0;
        transform: translateY(6px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeInSmooth 0.45s ease-out;
}
</style>
