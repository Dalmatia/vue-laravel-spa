<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Toast from '@/components/Toast.vue';
import ToggleRow from '../Components/ToggleRow.vue';
import SkeletonRow from '../Components/SkeletonRow.vue';
import ThemeToggle from '../Components/Settings/ThemeToggle.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

const router = useRouter();
const authStore = useAuthStore();
const authUser = authStore.user;

// モック設定データ
const isLoading = ref(true);
const settings = ref({});

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'Login' });
};

onMounted(async () => {
    try {
        const res = await axios.get('/api/settings');
        settings.value = res.data;
    } finally {
        isLoading.value = false;
    }
});

// 設定更新
const updateSetting = async (key) => {
    try {
        await axios.put('/api/settings', {
            [key]: settings.value[key],
        });
    } catch (e) {
        console.error(e);
        toastRef.value.show('更新に失敗しました');
    }
};
</script>

<template>
    <Toast ref="toastRef" />
    <div
        class="max-w-2xl mx-auto md:w-[90vw] lg:ml-0 md:ml-20 p-6 space-y-8 pb-20"
    >
        <h1 class="hidden md:block text-2xl font-bold mb-4">設定</h1>

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
                <section class="border rounded-lg p-4 bg-white shadow-sm">
                    <h2 class="text-lg font-semibold mb-3">メール通知</h2>

                    <div class="space-y-4">
                        <template v-if="isLoading">
                            <SkeletonRow />
                            <SkeletonRow />
                            <SkeletonRow />
                        </template>
                        <template v-else>
                            <ToggleRow
                                label="登録完了メール"
                                v-model="settings.email_registration"
                                @change="updateSetting('email_registration')"
                            />

                            <ToggleRow
                                label="パスワード再設定メール"
                                v-model="settings.email_password_reset"
                                @change="updateSetting('email_password_reset')"
                            />

                            <ToggleRow
                                label="退会完了メール"
                                v-model="settings.email_withdrawal"
                                @change="updateSetting('email_withdrawal')"
                            />
                        </template>
                    </div>
                </section>

                <section class="border rounded-lg p-4 bg-white shadow-sm">
                    <h2 class="text-lg font-semibold mb-3">アプリ内通知</h2>

                    <div class="space-y-4">
                        <template v-if="isLoading">
                            <SkeletonRow />
                            <SkeletonRow />
                            <SkeletonRow />
                        </template>

                        <template v-else>
                            <ToggleRow
                                label="いいね！通知"
                                v-model="settings.inapp_like"
                                @change="updateSetting('inapp_like')"
                            />

                            <ToggleRow
                                label="コメント通知"
                                v-model="settings.inapp_comment"
                                @change="updateSetting('inapp_comment')"
                            />

                            <ToggleRow
                                label="フォロー通知"
                                v-model="settings.inapp_follow"
                                @change="updateSetting('inapp_follow')"
                            />
                        </template>
                    </div>
                </section>
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
