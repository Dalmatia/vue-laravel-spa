<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';
import Toast from './Components/Toast.vue';
import { useRoute } from 'vue-router';

let transitionName = ref('');
const route = useRoute();
const toastRef = ref();
const showToast = (msg) => toastRef.value.show(msg);

const handleEvent = (message, slideTransition = false) => {
    showToast(message);
    if (slideTransition) {
        transitionName.value = 'slide';
        setTimeout(() => {
            transitionName.value = '';
        }, 300);
    }
};

const onCreateOutfit = () => handleEvent('コーディネートを投稿しました!');
const onDeleteOutfit = () => handleEvent('コーディネートを削除しました!');
const onUpdateProfile = () => handleEvent('プロフィールを更新しました!', true);
const changePasswordSuccess = (e) =>
    handleEvent('パスワードを変更しました。', e.detail?.slide);
const changePasswordError = () =>
    handleEvent('パスワードの変更に失敗しました。');
const onUserDeleted = () => handleEvent('アカウントを削除しました。');
const onUserDeleteError = () => handleEvent('アカウント削除に失敗しました。');

onMounted(() => {
    window.addEventListener('outfit-created', onCreateOutfit);
    window.addEventListener('outfit-deleted', onDeleteOutfit);
    window.addEventListener('profile-updated', onUpdateProfile);
    window.addEventListener('password-changed', changePasswordSuccess);
    window.addEventListener('password-change-error', changePasswordError);
    window.addEventListener('user-deleted', onUserDeleted);
    window.addEventListener('user-delete-error', onUserDeleteError);
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', onCreateOutfit);
    window.removeEventListener('outfit-deleted', onDeleteOutfit);
    window.removeEventListener('profile-updated', onUpdateProfile);
    window.removeEventListener('password-changed', changePasswordSuccess);
    window.removeEventListener('password-change-error', changePasswordError);
    window.removeEventListener('user-deleted', onUserDeleted);
    window.removeEventListener('user-delete-error', onUserDeleteError);
});
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <Toast ref="toastRef" />

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
