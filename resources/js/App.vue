<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';
import CheckMark from 'vue-material-design-icons/Check.vue';
import Close from 'vue-material-design-icons/Close.vue';
import { useRoute } from 'vue-router';

let successMessage = ref('');
let timer;
let transitionName = ref('');
const route = useRoute();

const showMessage = (message) => {
    successMessage.value = message;

    clearTimeout(timer);
    timer = setTimeout(() => {
        successMessage.value = '';
    }, 3000); // 3秒後にメッセージを非表示にする
};

const closeToast = () => {
    successMessage.value = '';
    clearTimeout(timer);
};

const handleEvent = (message, slideTransition = false) => {
    showMessage(message);
    if (slideTransition) {
        transitionName.value = 'slide';
        setTimeout(() => {
            transitionName.value = '';
        }, 300);
    }
};

onMounted(() => {
    window.addEventListener('outfit-created', () =>
        handleEvent('コーディネートを投稿しました!')
    );

    window.addEventListener('outfit-deleted', () =>
        handleEvent('コーディネートを削除しました!')
    );

    window.addEventListener('profile-updated', () =>
        handleEvent('プロフィールを更新しました!', true)
    );
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', handleEvent);

    window.removeEventListener('outfit-deleted', handleEvent);

    window.removeEventListener('profile-updated', handleEvent);

    clearTimeout(timer);
});
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <transition name="fade">
            <div v-if="successMessage" class="fixed top-14 right-2 z-50">
                <div
                    class="bg-blue-500 text-white py-[10px] px-[12px] rounded transition hover:bg-blue-600"
                >
                    <div class="flex items-center space-x-2">
                        <CheckMark class="text-3xl" />
                        <p class="font-bold">{{ successMessage }}</p>
                        <button
                            @click="closeToast"
                            class="rounded-md text-white hover:text-white/[.5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-white transition-all"
                        >
                            <Close class="text-3xl" />
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <router-view v-slot="{ Component }">
            <transition :name="transitionName">
                <div :key="route.fullPath">
                    <component :is="Component" />
                </div>
            </transition>
        </router-view>
    </MainLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}

/* ページ遷移用スライドアニメーション */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(-100%);
}
.slide-leave-to {
    transform: translateX(100%);
}
</style>
