<script setup>
import { ref } from 'vue';

import MainLayout from '@/Layouts/MainLayout.vue';

let loggedIn = ref(false);

const updateSidebar = () => {
    loggedIn.value = !loggedIn.value;
};
</script>

<template>
    <Head title="Home" />

    <MainLayout>
        <router-view
            :loggedIn="loggedIn"
            @update-sidebar="updateSidebar"
        ></router-view>
    </MainLayout>
</template>
