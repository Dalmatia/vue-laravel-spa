<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import VueDatePicker from '@vuepic/vue-datepicker';

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
    await axios
        .post('/api/register', form.value)
        .then(() => {
            router.push('/login');
        })
        .catch((reason) => {
            errors.value = reason?.response?.data?.errors ?? {};
        });
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
                    <div>
                        <label
                            for="name"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                        >
                            アカウント名:
                        </label>
                        <input
                            type="text"
                            id="name"
                            autocomplete="name"
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
                            autocomplete="email"
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
                            for="gender"
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                            >性別:
                        </label>
                        <select
                            id="gender"
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="form.gender"
                        >
                            <option :value="null">選択してください</option>
                            <option
                                v-for="gender in genders"
                                :key="gender.value"
                                :value="gender.value"
                            >
                                {{ gender.label }}
                            </option>
                        </select>
                        <span
                            v-if="errors.gender"
                            class="text-sm text-red-700 m-1"
                            role="alert"
                        >
                            {{ errors.gender[0] }}
                        </span>
                    </div>

                    <div>
                        <div
                            class="inline-block text-gray-800 text-sm sm:text-base mb-2"
                        >
                            生年月日:
                        </div>
                        <VueDatePicker
                            id="birthdate"
                            uid="birthdate"
                            v-model="form.birthdate"
                            teleport-center
                            locale="ja"
                            format="yyyy/MM/dd"
                            model-type="yyyy-MM-dd"
                            week-start="0"
                            :enable-time-picker="false"
                            auto-apply
                            class="block w-full bg-gray-50 text-gray-800 focus:ring ring-indigo-300 rounded outline-none transition duration-100"
                        />
                        <p
                            v-if="age !== null"
                            class="mt-2 text-sm text-gray-600"
                        >
                            年齢: {{ age }} 歳
                        </p>
                        <span
                            v-if="errors.birthdate"
                            class="text-sm text-red-700 m-1"
                            role="alert"
                        >
                            {{ errors.birthdate[0] }}
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
        <div class="pb-20"></div>
    </div>
</template>
