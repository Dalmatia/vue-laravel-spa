<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';

let successMessage = ref('');

const showMessage = (message) => {
    successMessage.value = message;
    setTimeout(() => {
        successMessage.value = '';
    }, 3000); // 3秒後にメッセージを非表示にする
};

onMounted(() => {
    window.addEventListener('outfit-created', () => {
        showMessage('コーディネートを投稿しました!');
    });

    window.addEventListener('outfit-deleted', () => {
        showMessage('コーディネートを削除しました!');
    });
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', () => {
        showMessage('コーディネートを投稿しました!');
    });

    window.removeEventListener('outfit-deleted', () => {
        showMessage('コーディネートを削除しました!');
    });
});
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <div
            v-if="successMessage"
            class="fixed bg-blue-500 text-white py-[10px] px-[12px] rounded z-50 top-[30px] right-[30px]"
        >
            {{ successMessage }}
        </div>

        <router-view></router-view>
    </MainLayout>
</template>
