<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import Check from 'vue-material-design-icons/Check.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const router = useRouter();
const authStore = useAuthStore();
const showModal = ref(false);
const agreed = ref(false);

const deleteAccount = async () => {
    try {
        await axios.delete('/api/user/delete');
        authStore.$reset();
        router.push({ name: 'DeleteAccountSuccess' });
        window.dispatchEvent(new Event('user-deleted'));
    } catch (e) {
        console.error(e);
        window.dispatchEvent(new Event('user-delete-error'));
    }
};
</script>

<template>
    <form class="lg:ml-0 md:ml-20 pb-[47px] xl:pb-[80px]">
        <section
            class="w-full border-gray-300 bg-white xl:divide-y xl:divide-gray-300 xl:overflow-hidden xl:rounded-[5px] xl:border"
        >
            <h2
                class="bg-gray-50 px-[31px] py-[19px] text-[14px] font-bold leading-normal text-black"
            >
                アカウント削除
            </h2>
            <div>
                <div class="flex flex-col gap-y-5 px-[15px] xl:gap-y-4 xl:p-8">
                    <h3
                        class="text-[13px] leading-normal xl:text-[14px] xl:font-bold"
                    >
                        退会をご希望される場合は、下記の注意事項に同意の上退会してください。
                    </h3>
                    <div
                        class="flex flex-col gap-y-3 rounded-[5px] bg-gray-50 p-4 xl:gap-y-2 xl:bg-white xl:p-0"
                    >
                        <p
                            class="relative pl-[13px] text-[12px] leading-normal text-gray-600 before:absolute before:left-0 before:top-0 before:content-['・'] xl:whitespace-pre-wrap xl:text-[14px] xl:text-black-400"
                        >
                            アカウントを削除すると、登録されたデータは復元できません。
                        </p>
                        <p
                            class="relative pl-[13px] text-[12px] leading-normal text-gray-600 before:absolute before:left-0 before:top-0 before:content-['・'] xl:whitespace-pre-wrap xl:text-[14px] xl:text-black-400"
                        >
                            退会処理を行った場合、友達検索結果に表示されなくなり、プロフィールも他のユーザーから閲覧できなくなります。
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <div
            class="flex flex-col items-center gap-y-4 pt-[33px] xl:gap-y-[14px] xl:pt-[30px]"
        >
            <label
                class="flex w-fit items-center gap-x-[6px] xl:cursor-pointer"
            >
                <input
                    id="checkAgree"
                    class="peer sr-only"
                    type="checkbox"
                    v-model="agreed"
                />
                <div
                    class="flex items-center justify-center rounded-full bg-gray-400 peer-checked:bg-blue-400 aspect-square w-5 flex-shrink-0"
                >
                    <Check
                        :size="17"
                        class="text-[14px] text-white"
                        aria-hidden="true"
                    />
                </div>
                <span class="text-[13px] xl:text-[14px]">上記に同意する</span>
            </label>
            <button
                class="h-12 w-[260px] rounded-full bg-blue-400 text-[14px] font-bold text-white disabled:bg-blue-200 xl:w-[300px] xl:text-[16px] xl:hover:opacity-70"
                @click.prevent="showModal = true"
                :disabled="!agreed"
            >
                退会を進める
            </button>

            <ConfirmModal
                v-if="showModal"
                title="本当に削除しますか？"
                description="この操作は取り消せません。"
                @confirm="deleteAccount"
                @cancel="showModal = false"
            />
        </div>
    </form>
</template>
