<script setup>
import { ref } from 'vue';

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
        window.dispatchEvent(new Event('password-changed'));
        currentPassword.value = '';
        newPassword.value = '';
        confirmPassword.value = '';
    } catch (error) {
        console.error(error);
        window.dispatchEvent(new Event('password-change-error'));
    }
};
</script>

<template>
    <form
        @submit.prevent="changePassword"
        class="flex flex-col items-center gap-y-10 pb-[60px] xl:p-0"
    >
        <section
            class="w-full border-gray-300 bg-white xl:divide-y xl:divide-gray-300 xl:overflow-hidden xl:rounded-[5px] border-b xl:border"
        >
            <div class="bg-white py-8 xl:border-none xl:pb-[26px] xl:pt-[14px]">
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
                        <div class="xl:flex xl:items-center xl:pt-[7px]">
                            <label
                                class="flex w-full items-center border-y border-gray-300 bg-gray-50 px-[15px] py-[5px] text-[12px] text-gray-900/60 xl:border-none xl:bg-transparent xl:p-0 xl:text-[14px] xl:leading-normal xl:text-black-400 after:text-red-500 after:content-['必須'] xl:after:text-[10px] gap-1"
                            >
                                現在のパスワード:
                            </label>
                        </div>
                        <div class="px-4 xl:p-0 py-3">
                            <div class="flex flex-col gap-y-2 xl:gap-y-[9px]">
                                <input
                                    type="password"
                                    class="h-9 w-full rounded-[8px] border border-gray-300 px-3 text-[13px] placeholder:text-gray-500 focus:border-blue-400 focus:outline-0 xl:rounded-[4px] xl:text-[14px]"
                                    v-model="currentPassword"
                                    placeholder="現在のパスワードを入力"
                                />
                                <p
                                    class="text-[11px] leading-none text-red-300 empty:hidden xl:text-[12px]"
                                ></p>
                            </div>
                        </div>
                        <div class="xl:flex xl:items-center xl:pt-[7px]">
                            <label
                                class="flex w-full items-center border-y border-gray-300 bg-gray-50 px-[15px] py-[5px] text-[12px] text-gray-900/60 xl:border-none xl:bg-transparent xl:p-0 xl:text-[14px] xl:leading-normal xl:text-black-400 after:text-red-500 after:content-['必須'] xl:after:text-[10px] gap-1"
                            >
                                新しいパスワード:
                            </label>
                        </div>
                        <div class="px-4 xl:p-0 py-3">
                            <div class="flex flex-col gap-y-2 xl:gap-y-[9px]">
                                <input
                                    type="password"
                                    class="h-9 w-full rounded-[8px] border border-gray-300 px-3 text-[13px] placeholder:text-gray-500 focus:border-blue-400 focus:outline-0 xl:rounded-[4px] xl:text-[14px]"
                                    v-model="newPassword"
                                    placeholder="新しいパスワードを入力"
                                />
                                <p
                                    class="text-[11px] leading-none text-red-300 empty:hidden xl:text-[12px]"
                                ></p>
                            </div>
                        </div>
                        <div class="xl:flex xl:items-center xl:pt-[7px]">
                            <label
                                class="flex w-full items-center border-y border-gray-300 bg-gray-50 px-[15px] py-[5px] text-[12px] text-gray-900/60 xl:border-none xl:bg-transparent xl:p-0 xl:text-[14px] xl:leading-normal xl:text-black-400 after:text-red-500 after:content-['必須'] xl:after:text-[10px] gap-1"
                            >
                                新しいパスワード(確認用):
                            </label>
                        </div>
                        <div class="px-4 xl:p-0 py-3">
                            <div class="flex flex-col gap-y-2 xl:gap-y-[9px]">
                                <input
                                    type="password"
                                    class="h-9 w-full rounded-[8px] border border-gray-300 px-3 text-[13px] placeholder:text-gray-500 focus:border-blue-400 focus:outline-0 xl:rounded-[4px] xl:text-[14px]"
                                    v-model="confirmPassword"
                                    placeholder="確認のためもう一度入力"
                                />
                                <p
                                    class="text-[11px] leading-none text-red-300 empty:hidden xl:text-[12px]"
                                ></p>
                            </div>
                        </div>
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
</template>
