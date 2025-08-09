<script setup>
import { useAuthStore } from '@/stores/auth';

import Magnify from 'vue-material-design-icons/Magnify.vue';
import HomeOutline from 'vue-material-design-icons/HomeOutline.vue';
import Calendar from 'vue-material-design-icons/Calendar.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import AccountArrowRightOutline from 'vue-material-design-icons/AccountArrowRightOutline.vue';

const authStore = useAuthStore();
const emit = defineEmits(['open-create-post']);
</script>

<template>
    <div
        id="BottomNav"
        class="fixed z-30 bottom-0 w-full md:hidden flex items-center justify-around bg-white border-t py-2 border-t-gray-300"
    >
        <router-link :to="{ name: 'Home' }">
            <HomeOutline
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
        </router-link>

        <router-link :to="{ name: 'Search' }">
            <Magnify fillColor="#000000" :size="33" class="cursor-pointer" />
        </router-link>

        <Plus
            @click="emit('open-create-post')"
            fillColor="#000000"
            :size="33"
            class="cursor-pointer"
        />

        <router-link :to="{ name: 'Calendar' }">
            <Calendar fillColor="#000000" :size="33" class="cursor-pointer" />
        </router-link>

        <router-link :to="{ name: 'Login' }" v-if="!authStore.user">
            <AccountArrowRightOutline
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
        </router-link>

        <router-link
            :to="{ name: 'User', params: { id: authStore.user.id } }"
            v-if="authStore.user"
        >
            <AccountOutline
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
        </router-link>
    </div>
</template>
