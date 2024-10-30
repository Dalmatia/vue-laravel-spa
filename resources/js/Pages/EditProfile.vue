<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute } from 'vue-router';

import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Upload from 'vue-material-design-icons/Upload.vue';

const authStore = useAuthStore();
const newPassword = ref('');
let isValidFile = ref(null);
let fileDisplay = ref('');
const router = useRoute();

// ユーザー情報の取得
const fetchUserData = async () => {
    try {
        await authStore.fetchUserData();
    } catch (error) {
        if (error.response && error.response.status === 401) {
            handleUnauthorized();
        }
    }
};

// ユーザー認証の判別
const handleUnauthorized = () => {
    localStorage.removeItem('authenticated');
    authStore.logout();
    router.push({ name: 'Login' });
};

const updateProfile = async () => {
    const formData = new FormData();
    formData.append('name', authStore.user.name);
    formData.append('email', authStore.user.email);

    if (newPassword.value) {
        formData.append('password', newPassword.value);
    }
    formData.append('file', authStore.user.file);

    try {
        const response = await axios.post(
            `/api/user/${authStore.user.id}/update`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        if (response.status === 200) {
            window.dispatchEvent(new Event('profile-updated'));
            await fetchUserData(); // 更新後に再度ユーザー情報を取得
        }
    } catch (error) {
        console.error('プロフィール更新エラー:', error);
        alert('プロフィールの更新に失敗しました。');
    }
};

// ファイルアップロード
const getUploadedImage = (e) => {
    // 以前のファイルURLがあれば解放する
    if (fileDisplay.value) {
        URL.revokeObjectURL(fileDisplay.value);
    }

    const file = e.target.files[0];
    const extension = file.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得

    isValidFile.value = ['png', 'jpg', 'jpeg'].includes(extension);
    if (!isValidFile.value) return;

    authStore.user.file = file;
    fileDisplay.value = URL.createObjectURL(file);

    // ファイル選択をリセット
    e.target.value = '';
};

onMounted(() => {
    fetchUserData();
});
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
                <div class="max-w-[200px] mx-auto relative">
                    <div
                        class="flex justify-center items-center border-2 border-gray-300 rounded-full overflow-hidden h-[200px] w-[200px] bg-gray-300"
                    >
                        <!-- プロフィール画像 -->
                        <img
                            :src="
                                fileDisplay ? fileDisplay : authStore.user.file
                            "
                            alt="プロフィール画像"
                            class="object-cover h-full w-full"
                        />
                    </div>

                    <!-- 編集ボタン（画像アップロード用） -->
                    <input
                        type="file"
                        class="absolute bottom-2 right-2 opacity-0 w-full h-full cursor-pointer"
                        v-if="!fileDisplay"
                        @change="getUploadedImage($event)"
                    />
                    <button
                        class="absolute bottom-2 right-2 bg-white border border-gray-300 rounded-full p-2 shadow hover:shadow-lg"
                    >
                        <Upload fill="none" class="h-6 w-6 text-gray-700" />
                    </button>
                </div>

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
