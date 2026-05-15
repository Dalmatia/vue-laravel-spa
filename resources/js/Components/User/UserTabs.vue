<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import UserTabButton from './UserTabButton.vue';
import Grid from 'vue-material-design-icons/Grid.vue';
import Hanger from 'vue-material-design-icons/Hanger.vue';
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue';

const route = useRoute();

const emit = defineEmits(['openCreateItem']);

defineProps({
    user: Object,
    authUser: Boolean,
});

const isUserTab = computed(() => route.name === 'User');

const isItemsTab = computed(() => route.name === 'Items');
</script>

<template>
    <!-- モバイル用レイアウト -->
    <div
        class="md:hidden w-full flex items-center justify-between border-t border-t-gray-300"
        v-if="user"
    >
        <UserTabButton
            :to="{ name: 'User', params: { id: user.id } }"
            :active="isUserTab"
            mobile
        >
            <Grid
                :size="28"
                class="cursor-pointer"
                :class="{
                    'text-[#8E8E8E]': !isUserTab,
                    'text-[#0095F6]': isUserTab,
                }"
            />
        </UserTabButton>

        <div class="p-3 w-1/3 flex justify-center border-t" v-if="authUser">
            <PlusCircle
                @click="emit('openCreateItem')"
                :size="28"
                fillColor="#8E8E8E"
                class="cursor-pointer"
            />
        </div>

        <UserTabButton
            v-if="authUser"
            :to="{ name: 'Items', params: { id: user.id } }"
            :active="isItemsTab"
            mobile
        >
            <Hanger
                :size="28"
                class="cursor-pointer"
                :class="{
                    'text-[#8E8E8E]': !isItemsTab,
                    'text-[#0095F6]': isItemsTab,
                }"
            />
        </UserTabButton>
    </div>

    <!-- PC/タブレット用レイアウト -->
    <div class="md:block mt-10 hidden border-t border-t-gray-300" v-if="user">
        <div
            class="flex items-center justify-between max-w-[600px] mx-auto font-extrabold text-gray-400 text-[15px]"
        >
            <UserTabButton
                :to="{ name: 'User', params: { id: user.id } }"
                :active="isUserTab"
            >
                <Grid :size="15" />
                <div class="ml-2 -mb-[1px]">POSTS</div>
            </UserTabButton>

            <div
                class="p-[17px] w-1/3 flex justify-center items-center"
                v-if="authUser"
            >
                <PlusCircle
                    @click="emit('openCreateItem')"
                    :size="40"
                    fillColor="#8E8E8E"
                    class="cursor-pointer"
                />
            </div>

            <UserTabButton
                v-if="authUser"
                :to="{ name: 'Items', params: { id: user.id } }"
                :active="isItemsTab"
            >
                <Hanger :size="15" />
                <span class="ml-2 -mb-[1px]">ITEMS</span>
            </UserTabButton>
        </div>
    </div>
</template>
