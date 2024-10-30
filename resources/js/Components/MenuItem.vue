<script setup>
import { toRefs } from 'vue';
import { useAuthStore } from '../stores/auth';

import HomeOutline from 'vue-material-design-icons/HomeOutline.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import Calendar from 'vue-material-design-icons/Calendar.vue';
import SendOutline from 'vue-material-design-icons/SendOutline.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import Menu from 'vue-material-design-icons/Menu.vue';
import Login from 'vue-material-design-icons/Login.vue';

const props = defineProps({ iconString: String });
const { iconString } = toRefs(props);

const user = useAuthStore().user;
let icon = null;

if (iconString.value === 'Home') icon = HomeOutline;
if (iconString.value === 'Search') icon = Magnify;
if (iconString.value === 'Calendar') icon = Calendar;
if (iconString.value === 'Messages') icon = SendOutline;
if (iconString.value === 'Notifications') icon = HeartOutline;
if (iconString.value === 'Create') icon = Plus;
if (iconString.value === 'Login') icon = Login;
if (iconString.value === 'Profile') icon = AccountOutline;
if (iconString.value === 'Logout') icon = Menu;
</script>

<template>
    <div
        class="w-full xl:inline-block xl:hover:bg-gray-100 p-2 rounded-full transition duration-300 ease-in-out cursor-pointer"
    >
        <div class="flex items-center">
            <img
                v-if="iconString === 'Profile'"
                :class="{ 'mr-1': iconString === 'Profile' }"
                class="rounded-full ml-[2px] w-[30px] cursor-pointer"
                :src="user.file"
            />
            <component v-else :is="icon" fillColor="#000000" :size="36" />
            <span
                class="xl:block hidden text-black font-extrabold text-[18px] pl-2 mt-0.5"
            >
                {{ iconString }}
            </span>
        </div>
    </div>
</template>
