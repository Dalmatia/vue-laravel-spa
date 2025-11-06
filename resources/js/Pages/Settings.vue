<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Toast from '@/components/Toast.vue';
import ThemeToggle from '../Components/Settings/ThemeToggle.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

const router = useRouter();
const authStore = useAuthStore();
const authUser = authStore.user;

// モック設定データ
const settings = ref({
    emailNotifications: true,
    appNotifications: false,
    darkMode: false,
});

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'Login' });
};
</script>

<template>
    <Toast ref="toastRef" />
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
        <ThemeToggle />

        <!-- アカウント設定 -->
        <section class="border rounded-lg p-4 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">アカウント</h2>

            <div class="mt-4 leading-none border">
                <router-link
                    class="py-3 px-4 bg-white items-center flex box-border"
                    :to="{ name: 'PasswordChangeForm' }"
                >
                    パスワードを変更
                    <div class="ml-auto">
                        <ChevronRight :size="20" />
                    </div>
                </router-link>
            </div>

            <div class="mt-4 leading-none border">
                <router-link
                    class="py-3 px-4 bg-white items-center flex box-border"
                    :to="{ name: 'DeleteAccountConfirm' }"
                >
                    退会する
                    <div class="ml-auto">
                        <ChevronRight :size="20" />
                    </div>
                </router-link>
            </div>
            <div class="mt-4 flex items-center justify-center">
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
