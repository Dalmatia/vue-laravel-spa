<script setup>
import { useEditProfileForm } from '../../src/composables/useEditProfileForm';
import ProfileImageUploader from './ProfileImageUploader.vue';
import ProfileSelectGender from './ProfileSelectGender.vue';
import ProfileDatePicker from './ProfileDatePicker.vue';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

const {
    authStore,
    newPassword,
    genders,
    age,
    fileDisplay,
    isValidFile,
    updateProfile,
    fetchUserData,
    fetchGenders,
    profileImageChange,
} = useEditProfileForm();
</script>

<template>
    <main class="max-w-[880px] lg:ml-0 md:pl-20 px-4 w-[100vw] md:w-[84.5vw]">
        <!-- ヘッダー部分(モバイル用) -->
        <div
            class="fixed top-0 left-0 w-full bg-white border-b border-gray-300 flex items-center justify-between h-[53px] px-4 md:hidden"
        >
            <!-- 戻るボタン -->
            <button
                class="duration-[0.2s] min-h-[36px] min-w-[36px] cursor-pointer flex items-center"
                @click="$router.back()"
            >
                <ArrowLeft
                    class="h-[20px] w-[20px] text-gray-900"
                    fillColor="#000000"
                />
            </button>

            <!-- ページタイトル -->
            <h2 class="text-base font-bold text-center flex-grow text-black">
                プロフィールを編集
            </h2>

            <!-- 保存ボタン -->
            <button
                class="bg-black duration-[0.2s] min-w-[56px] min-h-[32px] px-4 rounded-full border border-solid border-gray-300 flex items-center justify-center"
                @click.prevent="updateProfile()"
            >
                <span class="text-white font-bold text-base">保存</span>
            </button>
        </div>

        <!-- デスクトップ/タブレットのヘッダー -->
        <div class="hidden md:flex items-center justify-between py-6">
            <div class="flex items-center space-x-2">
                <ArrowLeft
                    class="h-[24px] w-[24px] text-gray-900"
                    fillColor="#000000"
                    @click="$router.back()"
                />
                <h2 class="text-xl font-bold text-black">プロフィールを編集</h2>
            </div>
        </div>

        <!-- プロフィール編集部分 -->
        <div class="grow w-full mx-auto md:mt-16" v-if="authStore.user">
            <div class="pb-16">
                <!-- プロフィール画像表示部分 -->
                <ProfileImageUploader
                    :file-display="
                        fileDisplay ? fileDisplay : authStore.user.file
                    "
                    :user-file="authStore.user.file"
                    @change="profileImageChange($event)"
                />

                <!-- ユーザー名編集欄 -->
                <div class="mt-8 px-4">
                    <label
                        for="username"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        名前:
                    </label>
                    <input
                        type="text"
                        id="username"
                        autocomplete="off"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        v-model="authStore.user.name"
                        placeholder="新しいユーザー名を入力"
                    />
                </div>

                <!-- メールアドレス編集欄 -->
                <div class="mt-4 px-4">
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        メールアドレス:
                    </label>
                    <input
                        type="email"
                        id="email"
                        autocomplete="off"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        v-model="authStore.user.email"
                        placeholder="新しいメールアドレスを入力"
                    />
                </div>

                <!-- パスワード編集欄 -->
                <div class="mt-4 px-4">
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        パスワード:
                    </label>
                    <input
                        type="password"
                        id="password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        v-model="newPassword"
                        placeholder="新しいパスワードを入力"
                    />
                </div>

                <ProfileSelectGender
                    v-model="authStore.user.gender"
                    :genders="genders"
                />

                <ProfileDatePicker
                    v-model="authStore.user.birthdate"
                    :age="age"
                />

                <!-- 保存ボタン（デスクトップ/タブレット用） -->
                <div class="hidden md:flex justify-end mt-6">
                    <button
                        class="bg-black duration-[0.2s] min-w-[80px] min-h-[40px] px-6 rounded-full border border-solid border-gray-300 flex items-center justify-center"
                        @click.prevent="updateProfile()"
                    >
                        <span class="text-white font-bold text-base">保存</span>
                    </button>
                </div>
            </div>
        </div>
    </main>
</template>
