<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNotification } from '../src/composables/useNotification';
import BellOutline from 'vue-material-design-icons/BellOutline.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import Setting from 'vue-material-design-icons/Cog.vue';
import Logout from 'vue-material-design-icons/Logout.vue';

const props = defineProps({
    isDropdownOpen: Boolean,
});
const { unreadCount } = useNotification();
const emit = defineEmits(['toggle-menu', 'logout']);

const authStore = useAuthStore();
const account = ref(null);
defineExpose({ account });

const toggleMenu = (type, event) => emit('toggle-menu', type, event);
const logout = () => emit('logout');
</script>

<template>
    <div
        id="TopNavHome"
        class="fixed z-30 md:hidden block w-full bg-white h-[61px] border-b border-b-gray-300"
    >
        <div class="flex items-center justify-between h-full">
            <router-link :to="{ name: 'Home' }">
                <h1 class="font-aurore w-[105px] ml-6 cursor-pointer">
                    daily outfit
                </h1>
            </router-link>

            <div class="flex items-center justify-end w-full">
                <router-link :to="{ name: 'Likes' }" class="pl-4 pr-3">
                    <HeartOutline fillColor="#000000" :size="27" />
                </router-link>

                <!-- 通知アイコン -->
                <div class="relative pl-4 pr-4">
                    <router-link
                        v-if="authStore.user"
                        :to="{
                            name: 'Notifications',
                            params: { id: authStore.user.id },
                        }"
                        class="relative"
                    >
                        <BellOutline fillColor="#000000" :size="27" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-0 right-0 translate-x-1/2 translate-y-1/2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-red-100 bg-red-600 rounded-full"
                        >
                            {{ unreadCount }}
                        </span>
                    </router-link>
                </div>

                <!-- アカウントアイコン -->
                <div class="relative" ref="account" v-if="authStore.user">
                    <div
                        @click="toggleMenu('account', $event)"
                        class="flex items-center cursor-pointer p-2 rounded-full hover:bg-gray-100 transition-all duration-300"
                    >
                        <div
                            class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-300"
                        >
                            <img
                                :src="authStore.user.file"
                                alt="Profile"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>

                    <!-- ドロップダウンメニューの内容 -->
                    <div
                        v-if="isDropdownOpen"
                        class="absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
                    >
                        <ul class="py-1">
                            <li
                                class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                            >
                                <router-link
                                    :to="{
                                        name: 'EditProfile',
                                        params: { id: authStore.user.id },
                                    }"
                                    class="flex items-center space-x-2 text-gray-700"
                                >
                                    <AccountOutline class="w-5 h-5" />
                                    <span>プロフィール編集</span>
                                </router-link>
                            </li>
                            <li
                                class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                            >
                                <router-link
                                    :to="{
                                        name: 'Settings',
                                        params: { id: authStore.user.id },
                                    }"
                                    class="flex items-center space-x-2 text-gray-700"
                                >
                                    <Setting class="w-5 h-5" />
                                    <span>設定</span>
                                </router-link>
                            </li>
                            <li
                                class="px-4 py-2 hover:bg-gray-50 transition-colors duration-200"
                            >
                                <div
                                    @click.prevent="logout()"
                                    class="flex items-center space-x-2 text-red-600 cursor-pointer"
                                >
                                    <Logout class="w-5 h-5" />
                                    <span>ログアウト</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
