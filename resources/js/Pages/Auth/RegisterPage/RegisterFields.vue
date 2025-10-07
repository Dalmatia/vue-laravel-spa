<script setup>
import FormInput from '../../../Components/FormInput.vue';
import FormSelect from '../../../Components/FormSelect.vue';
import BirthdatePicker from './BirthdatePicker.vue';

const props = defineProps({
    modelValue: Object,
    errors: Object,
    genders: Array,
    age: Number,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <FormInput
            label="アカウント名"
            id="name"
            v-model="modelValue.name"
            :error="errors.name"
        />
        <FormInput
            label="メールアドレス"
            id="email"
            v-model="modelValue.email"
            :error="errors.email"
        />

        <FormSelect
            label="性別"
            id="gender"
            v-model="modelValue.gender"
            :options="genders"
            :error="errors.gender"
        />

        <BirthdatePicker
            v-model="modelValue.birthdate"
            :age="age"
            :error="errors.birthdate"
        />

        <FormInput
            label="パスワード"
            id="password"
            type="password"
            v-model="modelValue.password"
            :error="errors.password"
        />
        <FormInput
            label="パスワード(確認)"
            id="password_confirmation"
            type="password"
            v-model="modelValue.password_confirmation"
        />
    </div>
</template>
