<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import Magnify from 'vue-material-design-icons/Magnify.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import HomeOutline from 'vue-material-design-icons/HomeOutline.vue';
import Compass from 'vue-material-design-icons/Compass.vue';
import SendOutline from 'vue-material-design-icons/SendOutline.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import AccountPlusOutline from 'vue-material-design-icons/AccountPlusOutline.vue';

import MenuItem from '@/Components/MenuItem.vue';
import CreatePostOverlay from '@/Components/CreatePostOverlay.vue';

let showCreatePost = ref(false);

const router = useRouter();

let loggedIn = ref(false);
// const emit = defineEmits(['updateSidebar']);

const name = ref('');
onMounted(() => {
    if (localStorage.getItem('authenticated')) {
        loggedIn.value = true;
    } else {
        loggedIn.value = false;
    }

    axios
        .get('/api/user')
        .then((response) => {
            name.value = response?.data?.name;
        })
        .catch((error) => {
            if (error.response.status === 401) {
                // emit('updateSidebar');
                localStorage.removeItem('authenticated');
                loggedIn.value = false;
                router.push({ name: 'Login' });
            }
        });
});

const logout = async () => {
    await axios.post('/api/logout').then(() => {
        router.push('/login');
        loggedIn.value = false;
        localStorage.removeItem('authenticated');
        // emit('updateSidebar');
    });
};
</script>

<template>
    <div id="MainLayout" class="w-full h-screen">
        <div
            v-if="this.$route.path == '/'"
            id="TopNavHome"
            class="fixed z-30 md:hidden block w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <div class="flex items-center justify-between h-full">
                <router-link :to="{ name: 'Home' }">
                    <h1 class="font-aurore w-[105px] ml-6 cursor-pointer">
                        daily outfit
                    </h1>
                </router-link>

                <div class="flex items-center w-[50%]">
                    <div
                        class="flex items-center w-full bg-gray-100 rounded-lg"
                    >
                        <Magnify class="pl-4" fillColor="#8E8E8E" :size="27" />
                        <input
                            type="text"
                            placeholder="検索する"
                            class="bg-transparent w-full placeholder-[#8E8E8E] border-0 ring-0 focus:ring-0"
                        />
                    </div>
                    <HeartOutline
                        class="pl-4 pr-3"
                        fillColor="#000000"
                        :size="27"
                    />
                </div>
            </div>
        </div>

        <div
            v-if="this.$route.path !== '/'"
            id="TopNavUser"
            class="md:hidden fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <router-link :to="{ name: 'Home' }" class="px-4">
                <ChevronLeft :size="30" class="cursor-pointer"></ChevronLeft>
            </router-link>
            <div class="font-extrabold text-lg" v-if="loggedIn">{{ name }}</div>
            <AccountPlusOutline :size="30" class="px-4"></AccountPlusOutline>
        </div>

        <div
            id="SideNav"
            class="fixed h-full bg-white xl:w-[255px] w-[80px] md:block hidden border-r border-r-gray-300"
        >
            <router-link :to="{ name: 'Home' }">
                <img
                    class="xl:hidden block w-[25px] mt-10 ml-[25px] mb-10 cursor-pointer"
                    src="/icons/hanger.svg"
                />
                <h1
                    class="font-aurore xl:block hidden w-[120px] mt-10 ml-6 mb-10 cursor-pointer"
                >
                    daily outfit
                </h1>
            </router-link>

            <div class="px-3">
                <router-link :to="{ name: 'Home' }">
                    <MenuItem iconString="Home" class="mb-4" />
                </router-link>
                <MenuItem iconString="Search" class="mb-4" />
                <!-- <MenuItem iconString="Explore" class="mb-4" /> -->
                <!-- <MenuItem iconString="Messages" class="mb-4" /> -->
                <MenuItem iconString="Notifications" class="mb-4" />
                <MenuItem
                    @click="showCreatePost = true"
                    iconString="Create"
                    class="mb-4"
                />
                <router-link :to="{ name: 'Login' }" v-if="!loggedIn">
                    <MenuItem iconString="Login" class="mb-4" />
                </router-link>
                <router-link :to="{ name: 'User' }" v-if="loggedIn">
                    <MenuItem iconString="Profile" class="mb-4" />
                </router-link>
            </div>

            <button
                type="button"
                class="absolute bottom-0 px-3 w-full"
                @click="logout"
                v-if="loggedIn"
            >
                <MenuItem iconString="Logout" class="mb-4" />
            </button>
        </div>

        <div
            class="flex lg:justify-between bg-white h-full w-[100%-280px] xl:pl-[280px] lg:pl-[100px] overflow-auto"
        >
            <div
                class="mx-auto md:pt-6 pt-20"
                :class="
                    this.$route.path === '/'
                        ? 'lg:w-8/12 w-full'
                        : 'max-w-[1200px]'
                "
            >
                <main>
                    <slot />
                </main>
            </div>

            <div
                v-if="this.$route.path == '/'"
                id="SuggestionsSection"
                class="lg:w-4/12 lg:block hidden text-black mt-10"
            >
                <a
                    href="/"
                    class="flex items-center justify-between max-w-[300px]"
                    v-if="loggedIn"
                >
                    <div class="flex items-center">
                        <img
                            class="rounded-full z-10 w-[58px] h-[58px]"
                            src="https://picsum.photos/id/50/300/320"
                        />
                        <div class="pl-4">
                            <div class="text-black font-extrabold">
                                {{ name }}
                            </div>
                            <div class="text-gray-500 text-extrabold text-sm">
                                NAME HERE
                            </div>
                        </div>
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        切り替え
                    </button>
                </a>

                <div
                    class="max-w-[300px] flex items-center justify-between py-3"
                >
                    <div class="text-gray-500 font-extrabold">
                        おすすめのユーザー
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        全て見る
                    </button>
                </div>

                <a
                    href="/"
                    class="flex items-center justify-between max-w-[300px] pb-2"
                >
                    <div class="flex items-center">
                        <img
                            class="rounded-full z-10 w-[37px] h-[37px]"
                            src="https://picsum.photos/id/200/300/320"
                        />
                        <div class="pl-4">
                            <div class="text-black font-extrabold">
                                NAME HERE
                            </div>
                            <div class="text-gray-500 text-extrabold text-sm">
                                NAME HERE
                            </div>
                        </div>
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        フォロー
                    </button>
                </a>
            </div>
        </div>

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
            <Compass fillColor="#000000" :size="33" class="cursor-pointer" />
            <Plus
                @click="showCreatePost = true"
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
            <SendOutline
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />
            <router-link :to="{ name: 'Login' }" v-if="!loggedIn">
                <AccountOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </router-link>
            <router-link :to="{ name: 'User' }" v-if="loggedIn">
                <img
                    class="rounded-full w-[30px] cursor-pointer"
                    src="https://picsum.photos/id/200/300/320"
                />
            </router-link>
        </div>
    </div>

    <CreatePostOverlay v-if="showCreatePost" @close="showCreatePost = false" />
</template>

<style></style>
