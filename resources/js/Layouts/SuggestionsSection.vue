<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <div
        id="SuggestionsSection"
        class="lg:w-4/12 lg:block hidden text-black mt-10"
    >
        <div
            class="flex items-center justify-between max-w-[300px]"
            v-if="authStore.user"
        >
            <div class="flex items-center">
                <router-link
                    :to="{ name: 'User', params: { id: authStore.user.id } }"
                >
                    <img
                        class="rounded-full z-10 w-[58px] h-[58px]"
                        :src="authStore.user.file"
                    />
                </router-link>
                <div class="pl-4">
                    <router-link
                        class="text-black font-extrabold"
                        :to="{
                            name: 'User',
                            params: { id: authStore.user.id },
                        }"
                    >
                        {{ authStore.user.name }}
                    </router-link>
                    <div class="text-gray-500 text-extrabold text-sm">
                        NAME HERE
                    </div>
                </div>
            </div>
            <button
                class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
            >
                切り替え
            </button>
        </div>

        <div class="max-w-[300px] flex items-center justify-between py-3">
            <div class="text-gray-500 font-extrabold">おすすめのユーザー</div>
            <button
                class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
            >
                全て見る
            </button>
        </div>

        <!-- ユーザーのサンプルカード（今後リストで繰り返せるようにする） -->
        <a
            href="/"
            class="flex items-center justify-between max-w-[300px] pb-2"
        >
            <div class="flex items-center">
                <img
                    class="rounded-full z-0 w-[37px] h-[37px]"
                    src="https://picsum.photos/id/200/300/320"
                />
                <div class="pl-4">
                    <div class="text-black font-extrabold">NAME HERE</div>
                    <div class="text-gray-500 text-extrabold text-sm">
                        NAME HERE
                    </div>
                </div>
            </div>
            <button
                class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
            >
                フォロー
            </button>
        </a>
    </div>
</template>
