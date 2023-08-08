<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = ref({
    email: '',
    name: '',
    password: '',
    password_confirmation: '',
});
const errors = ref([]);

const signup = async () => {
    await axios.get('/sanctum/csrf-cookie');
    await axios
        .post('/api/register', form.value)
        .then(() => {
            router.push('/login');
        })
        .catch((reason) => {
            errors.value = reason?.response?.data?.errors ?? {};
        });
};
</script>

<template>
    <div class="bg-white sm:py-8 lg:py-12">
        <div class="max-w-screen-2xl px-4 md:px-8 mx-auto">
            <h2
                class="text-gray-800 text-2xl lg:text-3xl font-bold text-center mb-4 md:mb-8"
            >
                新規登録
            </h2>

            <form
                class="max-w-lg border rounded-lg mx-auto"
                @submit.prevent="signup()"
            >
                <div class="flex flex-col gap-4 p-4 md:p-8">
                    <div>
                        <label
                            for="name"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                        >
                            アカウント名:
                        </label>
                        <input
                            type="text"
                            id="username"
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="form.name"
                        />
                        <span
                            v-if="errors.name"
                            class="text-sm text-red-700 m-1"
                            role="alert"
                        >
                            {{ errors.name[0] }}
                        </span>
                    </div>

                    <div>
                        <label
                            for="email"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                            >メールアドレス:
                        </label>
                        <input
                            type="text"
                            id="email"
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="form.email"
                        />
                        <span
                            v-if="errors.email"
                            class="text-sm text-red-700 m-1"
                            role="alert"
                        >
                            {{ errors.email[0] }}
                        </span>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                            >パスワード:
                        </label>
                        <input
                            type="password"
                            id="password"
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="form.password"
                        />
                        <span
                            v-if="errors.password"
                            class="text-sm text-red-700 m-1"
                            role="alert"
                        >
                            {{ errors.password[0] }}
                        </span>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                            >パスワード(確認):
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="form.password_confirmation"
                        />
                    </div>

                    <button
                        type="submit"
                        class="block bg-gray-800 hover:bg-gray-700 active:bg-gray-600 focus-visible:ring ring-gray-300 text-white text-sm md:text-base font-semibold text-center rounded-lg outline-none transition duration-100 px-8 py-3"
                    >
                        新規登録
                    </button>
                </div>
            </form>

            <p class="mt-2 text-center text-sm text-gray-500">
                アカウントをすでにお持ちですか?
                <router-link
                    to="/login"
                    class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500"
                >
                    ログイン
                </router-link>
            </p>
        </div>
    </div>
</template>

<style></style>
