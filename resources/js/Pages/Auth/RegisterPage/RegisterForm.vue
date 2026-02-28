<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../../stores/auth';

import RegisterFields from './RegisterFields.vue';

const authStore = useAuthStore();
const router = useRouter();
const form = ref({
    email: '',
    name: '',
    gender: null,
    birthdate: null,
    password: '',
    password_confirmation: '',
});
const genders = ref([]);
const errors = ref([]);

const signup = async () => {
    await axios.get('/sanctum/csrf-cookie');
    try {
        await axios.post('/api/register', form.value);
        await authStore.fetchUserData();
        router.push({ name: 'Home' });
    } catch (reason) {
        errors.value = reason?.response?.data?.errors ?? {};
    }
};

// 性別一覧の取得
const fetchGenders = async () => {
    try {
        const response = await axios.get('/api/get_genders');
        genders.value = response.data;
    } catch (error) {
        console.error('性別一覧の取得に失敗しました:', error);
    }
};

// 年齢計算
const age = computed(() => {
    if (!form.value.birthdate) return null;
    const today = new Date();
    const birth = new Date(form.value.birthdate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
});

onMounted(() => {
    fetchGenders();
});
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
                    <RegisterFields
                        v-model="form"
                        :genders="genders"
                        :age="age"
                        :errors="errors"
                    />

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
        <div class="pb-20"></div>
    </div>
</template>
