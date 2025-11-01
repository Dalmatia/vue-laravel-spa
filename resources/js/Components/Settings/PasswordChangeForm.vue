<script setup>
import { ref } from 'vue';

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

const changePassword = async () => {
    try {
        await axios.post(`/api/user/password`, {
            current_password: currentPassword.value,
            password: newPassword.value,
            password_confirmation: confirmPassword.value,
        });
        alert('パスワードが正常に変更されました。');
        currentPassword.value = '';
        newPassword.value = '';
        confirmPassword.value = '';
    } catch (error) {
        console.error(error);
        alert('パスワードの変更中にエラーが発生しました。');
    }
};
</script>

<template>
    <div class="space-y-3">
        <!-- 現在のパスワード -->
        <label class="block text-sm font-medium"> 現在のパスワード: </label>
        <input
            type="password"
            class="input-style"
            v-model="currentPassword"
            placeholder="現在のパスワードを入力"
        />

        <!-- 新しいパスワード -->
        <label for="password" class="block text-sm font-medium mb-1">
            新しいパスワード:
        </label>
        <input
            type="password"
            id="password"
            class="input-style"
            v-model="newPassword"
            placeholder="新しいパスワードを入力"
        />

        <!-- 確認用パスワード -->
        <label for="password" class="block text-sm font-medium mb-1">
            新しいパスワード(確認用):
        </label>
        <input
            type="password"
            id="confirmPassword"
            class="input-style"
            v-model="confirmPassword"
            placeholder="確認のためもう一度入力"
        />

        <div class="flex justify-end space-x-3 pt-3">
            <button
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                @click="changePassword"
            >
                パスワードを変更
            </button>
        </div>
    </div>
</template>

<style scoped>
.input-style {
    @apply mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500;
}
</style>
