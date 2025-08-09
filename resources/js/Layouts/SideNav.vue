<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useLayoutState } from '@/src/composables/useLayoutState';
import MenuItem from '@/Components/MenuItem.vue';

const emit = defineEmits(['open-create-post', 'toggle-menu', 'logout']);

const authStore = useAuthStore();
const { sideNavZIndex } = useLayoutState();

const notifications = ref(null);

const toggleMenu = (type, event) => {
    if (event) event.stopPropagation();
    if (type === 'notifications') emit('toggle-menu', type, event);
};

const logout = () => emit('logout');
</script>

<template>
    <div
        id="SideNav"
        class="fixed h-full bg-white xl:w-[255px] w-[80px] md:block hidden border-r border-r-gray-300 z-10"
        :style="{ zIndex: sideNavZIndex }"
    >
        <router-link :to="{ name: 'Home' }">
            <img
                class="xl:hidden block w-[25px] mt-10 ml-[25px] mb-10 cursor-pointer"
                src="@/assets/icons/hanger.svg"
            />
            <h1
                class="font-aurore xl:block hidden w-[120px] mt-10 ml-6 mb-10 cursor-pointer"
            >
                daily outfit
            </h1>
        </router-link>

        <div class="px-3">
            <router-link :to="{ name: 'Home' }">
                <MenuItem iconString="Home" class="mb-4 lg:mb-2" />
            </router-link>
            <router-link :to="{ name: 'Search' }">
                <MenuItem iconString="Search" class="mb-4 lg:mb-2" />
            </router-link>
            <router-link :to="{ name: 'Calendar' }" v-if="authStore.user">
                <MenuItem iconString="Calendar" class="mb-4 lg:mb-2" />
            </router-link>
            <div
                ref="notifications"
                v-if="authStore.user"
                @click="toggleMenu('notifications', $event)"
            >
                <MenuItem iconString="Notifications" class="mb-4 lg:mb-2" />
            </div>
            <router-link :to="{ name: 'Likes' }" v-if="authStore.user">
                <MenuItem iconString="Likes" class="mb-4 lg:mb-2" />
            </router-link>
            <MenuItem
                v-if="authStore.user"
                @click="emit('open-create-post')"
                iconString="Create"
                class="mb-4 lg:mb-2"
            />
            <router-link :to="{ name: 'Login' }" v-if="!authStore.user">
                <MenuItem iconString="Login" class="mb-4 lg:mb-2" />
            </router-link>
            <router-link
                :to="{ name: 'User', params: { id: authStore.user?.id } }"
                v-if="authStore.user"
            >
                <MenuItem iconString="Profile" class="mb-4 lg:mb-2" />
            </router-link>
        </div>

        <button
            type="button"
            class="absolute bottom-0 px-3 w-full"
            @click="logout"
            v-if="authStore.user"
        >
            <MenuItem iconString="Logout" class="mb-4" />
        </button>
    </div>
</template>
