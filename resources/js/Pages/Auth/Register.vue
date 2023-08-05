<template>
    <div class="bg-white my-6 py-6 sm:py-8 lg:py-12">
        <div class="max-w-screen-2xl px-4 md:px-8 mx-auto">
            <h2
                class="text-gray-800 text-2xl lg:text-3xl font-bold text-center mb-4 md:mb-8"
            >
                新規登録
            </h2>

            <form
                class="max-w-lg border rounded-lg mx-auto"
                @submit.prevent="submit"
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
                            class="w-full bg-gray-50 text-gray-800 border focus:ring ring-indigo-300 rounded outline-none transition duration-100 px-3 py-2"
                            v-model="fields.name"
                        />
                        <span v-if="errors.name" class="error">{{
                            errors.name[0]
                        }}</span>
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
                            v-model="fields.email"
                        />
                        <span v-if="errors.email" class="error">{{
                            errors.email[0]
                        }}</span>
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
                            v-model="fields.password"
                        />
                        <span v-if="errors.password" class="error">
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
                            v-model="fields.password_confirmation"
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
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            fields: {},
            errors: {},
        };
    },
    methods: {
        submit() {
            axios
                .post('/api/register', this.fields)
                .then(() => {
                    this.$router.push({ name: 'Home' });
                    localStorage.setItem('authenticated', 'true');
                    this.$emit('updateHeader');
                    location.reload();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
    },
};
</script>

<style></style>
