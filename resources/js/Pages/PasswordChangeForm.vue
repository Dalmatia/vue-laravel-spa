<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import FormPasswordField from '../Components/Settings/FormPasswordField.vue';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';

const router = useRouter();
const emit = defineEmits(['success', 'error']);
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
        window.dispatchEvent(
            new CustomEvent('password-changed', { detail: { slide: true } })
        );
        currentPassword.value = '';
        newPassword.value = '';
        confirmPassword.value = '';
        router.push({ name: 'Settings' });
    } catch (error) {
        console.error(error);
        window.dispatchEvent(new Event('password-change-error'));
    }
};
</script>

<template>
    <div class="max-w-2xl mx-auto w-[100vw] lg:ml-0 md:ml-20 space-y-8 pb-20">
        <!-- スマホ時の戻るボタン + タイトル -->
        <div
            class="relative md:flex xl:hidden items-center justify-center gap-2 px-4 py-4 hidden"
        >
            <router-link
                :to="{ name: 'Settings' }"
                class="absolute left-4 flex items-center text-blue-500 hover:opacity-70"
            >
                <ChevronLeft :size="24" />
            </router-link>
            <h1 class="text-[18px] font-bold leading-normal text-center">
                パスワードの変更
            </h1>
        </div>

        <!-- PC時の戻るリンク -->
        <router-link
            :to="{ name: 'Settings' }"
            class="hidden xl:flex items-center gap-x-1 py-4 px-4 xl:px-0 text-[14px] text-blue-500 hover:opacity-70"
        >
            <ChevronLeft :size="20" /> 設定に戻る
        </router-link>
        <form
            @submit.prevent="changePassword"
            class="flex flex-col items-center gap-y-10 pb-[60px] xl:p-0"
        >
            <section
                class="w-full border-gray-300 bg-white xl:divide-y xl:divide-gray-300 xl:overflow-hidden xl:rounded-[5px] border-b xl:border"
            >
                <div
                    class="hidden xl:block bg-white py-8 xl:border-none xl:pb-[26px] xl:pt-[14px]"
                >
                    <h1
                        class="text-center text-[18px] font-bold leading-normal xl:mx-auto xl:w-[990px] xl:text-left xl:text-[22px] xl:leading-[1.8]"
                    >
                        パスワードの変更
                    </h1>
                </div>
                <div>
                    <div class="flex flex-col gap-[17px] xl:gap-8 xl:p-8">
                        <div
                            class="grid grid-cols-1 xl:grid-cols-[auto_1fr] xl:items-start xl:gap-x-8 xl:gap-y-6"
                        >
                            <!-- 現在のパスワード -->
                            <FormPasswordField
                                label="現在のパスワード:"
                                v-model="currentPassword"
                                placeholder="現在のパスワードを入力"
                            />

                            <!-- 新しいパスワード -->
                            <FormPasswordField
                                label="新しいパスワード:"
                                v-model="newPassword"
                                placeholder="新しいパスワードを入力"
                            />

                            <!-- 新しいパスワード(確認用) -->
                            <FormPasswordField
                                label="新しいパスワード(確認用):"
                                v-model="confirmPassword"
                                placeholder="確認のためもう一度入力"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <button
                type="submit"
                class="h-12 w-[260px] rounded-full bg-blue-400 text-[14px] font-bold text-white disabled:bg-blue-200 xl:w-[280px] xl:text-[16px] xl:hover:opacity-70"
            >
                パスワードを変更
            </button>
        </form>
    </div>
</template>
