<script setup>
import { useRouter, useRoute } from 'vue-router';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Hanger from 'vue-material-design-icons/Hanger.vue';

const router = useRouter();
const route = useRoute();

const props = defineProps({
    userName: {
        type: String,
        required: true,
    },
    userImage: {
        type: String,
        required: false,
    },
    mainCategoryName: {
        type: String,
        required: true,
    },
    totalItems: {
        type: Number,
        required: true,
    },
});

const goBackToItems = () => {
    router.push({
        name: 'Items',
        params: {
            id: route.params.id,
        },
    });
};
</script>

<template>
    <!-- ユーザーヘッダーここから -->
    <div
        id="userHeaderMini"
        class="clear-both w-full pt-0 pr-[12px] pb-0 pl-[12px] h-[48px]"
    >
        <div id="main" class="mt-[8px] mr-[110px] pb-0 pl-0">
            <div id="container" class="table w-full table-fixed">
                <div class="hidden md:table-cell align-middle w-[32px]">
                    <ArrowLeft class="cursor-pointer" @click="goBackToItems" />
                </div>
                <p
                    id="img"
                    class="table-cell align-top w-[32px] text-[0px] leading-none tracking-normal"
                >
                    <img
                        class="rounded-full object-fit w-[32px] h-[32px] cursor-pointer"
                        :src="userImage"
                    />
                </p>
                <div
                    class="table-cell align-middle pt-[1px] pr-0 pb-0 pl-[7px]"
                    v-if="userName"
                >
                    <p id="name" class="leading-none">
                        <span class="nameFirst">
                            {{ userName }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div id="sub" class="absolute right-3 top-2 w-[100px]"></div>
    </div>
    <!-- ユーザーヘッダーここまで -->

    <!-- カテゴリー名と所持数の表示部分 -->
    <div id="intro" class="pt-[9px] pr-[12px] pb-0 pl-[12px] relative z-[2]">
        <h1 id="title" class="text-xl font-bold leading-[1.2]">
            {{ mainCategoryName }}
        </h1>
        <div id="itemCount" class="flex items-center mt-[6px] mr-0 mb-0 ml-0">
            <p
                id="closet icon"
                class="text-xs font-bold inline-block tracking-[0.08em] mt-0 mr-[5px] mb-0 ml-0 leading-[1.2]"
            >
                <Hanger
                    fillColor="#8E8E8E"
                    class="text-xs align-[-1px] mt-0 mr-[2px] mb-0 ml-0"
                />
            </p>
            <p class="text-xs font-bold leading-[1.2] mb-4">
                {{ totalItems }}
            </p>
        </div>
    </div>
    <!-- ここまで -->
</template>
