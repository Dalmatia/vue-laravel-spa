<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const authUser = authStore.user;

// モック設定データ
const settings = ref({
    emailNotifications: true,
    appNotifications: false,
    darkMode: false,
});

const logout = () => {
    authStore.logout();
    router.push({ name: 'Login' });
};

const deleteAccount = () => {
    if (confirm('本当に削除しますか？')) {
        alert('アカウント削除処理を実装予定です。');
    }
};
</script>

<template>
    <div
        class="max-w-2xl mx-auto md:w-[90vw] lg:ml-0 md:ml-20 p-6 space-y-8 pb-20"
    >
        <h1 class="text-2xl font-bold mb-4">設定</h1>

        <!-- プロフィール -->
        <section class="border rounded-lg p-4 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">プロフィール</h2>
            <p class="text-sm text-gray-600 mb-4">ユーザー情報を編集します。</p>
            <router-link
                :to="{ name: 'EditProfile', params: { id: authUser.id } }"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                プロフィールを編集
            </router-link>
        </section>

        <!-- 通知設定 -->
        <section class="border rounded-lg p-4 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">通知設定</h2>
            <div class="space-y-2">
                <label class="flex items-center justify-between">
                    <span>メール通知を受け取る</span>
                    <input
                        type="checkbox"
                        v-model="settings.emailNotifications"
                    />
                </label>
                <label class="flex items-center justify-between">
                    <span>アプリ内通知を有効にする</span>
                    <input
                        type="checkbox"
                        v-model="settings.appNotifications"
                    />
                </label>
            </div>
        </section>

        <!-- テーマ設定 -->
        <section class="border rounded-lg p-4 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">テーマ</h2>
            <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="settings.darkMode" />
                <span>ダークモードを有効にする</span>
            </label>
        </section>

        <!-- アカウント設定 -->
        <section class="border rounded-lg p-4 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">アカウント</h2>
            <div class="space-y-3">
                <button
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    @click="deleteAccount"
                >
                    アカウント削除
                </button>
                <button
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                    @click="logout()"
                >
                    ログアウト
                </button>
            </div>
        </section>
    </div>
</template>
