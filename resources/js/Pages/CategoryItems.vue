<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { getEnumStore } from '../stores/enum';

import ShowItemOverlay from '../Components/Items/ShowItemOverlay.vue';

import Hanger from 'vue-material-design-icons/Hanger.vue';
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

let currentItem = ref(null);
let openOverlay = ref(false);

const emit = defineEmits(['close']);
const authStore = useAuthStore();
const route = useRoute();
const items = ref([]);
const mainCategoryName = ref('');
const getCategoryName = getEnumStore();

// カテゴリごとのアイテムを格納するためのデータ構造
const categorizedItems = reactive({});

// 登録アイテムの表示
const fetchItems = async () => {
    try {
        const response = await axios.get('/api/items');
        items.value = response.data.items;

        // カテゴリごとにアイテムを分類
        categorizeItems();
    } catch (error) {
        console.error(error);
    }
};

// カテゴリごとにアイテムを分類する関数
const categorizeItems = () => {
    // カテゴリごとのデータを一度クリア
    for (const key in categorizedItems) {
        delete categorizedItems[key];
    }

    items.value.forEach((item) => {
        // 新しいアイテムだけを分類
        if (!categorizedItems[item.main_category]) {
            categorizedItems[item.main_category] = [];
        }
        categorizedItems[item.main_category].push(item);
    });
};

// アイテム詳細ページオーバーレイ表示
const openItemOverlay = (item) => {
    currentItem.value = item;
    openOverlay.value = true;
};

// 登録アイテムの削除
const deleteItem = (object) => {
    let url = '';
    if (object.deleteType === 'Item') {
        url = `/api/items/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                openOverlay.value = false;
                fetchItems();
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

onMounted(() => {
    // ページ遷移時にパラメータを取得
    const routeParams = route.params;
    if (routeParams.mainCategory) {
        mainCategoryName.value = routeParams.mainCategory;
    }

    fetchItems();
    window.addEventListener('item-created', fetchItems);
    window.addEventListener('item-updated', fetchItems);
});

onUnmounted(() => {
    window.removeEventListener('item-created', fetchItems);
    window.removeEventListener('item-updated', fetchItems);
});
</script>

<template>
    <div
        id="CategorizedItemPage"
        class="w-full max-w-[1000px] lg:ml-0 md:ml-[10px] md:pl-20 px-4 md:w-[90vw]"
    >
        <div id="contentsBody" class="pt-0">
            <div id="main_content">
                <!-- ヘッダーここから -->
                <div
                    id="userHeaderMini"
                    class="clear-both w-full pt-0 pr-[12px] pb-0 pl-[12px] h-[48px]"
                >
                    <div id="main" class="mt-[8px] mr-[110px] pb-0 pl-0">
                        <div id="container" class="table w-full table-fixed">
                            <p
                                id="img"
                                class="table-cell align-top w-[32px] text-[0px] leading-none tracking-normal"
                            >
                                <router-link :to="{ name: 'User' }">
                                    <img
                                        class="rounded-full object-fit w-[32px] h-[32px] cursor-pointer"
                                        :src="authStore.user.file"
                                    />
                                </router-link>
                            </p>
                            <div
                                class="table-cell align-middle pt-[1px] pr-0 pb-0 pl-[7px]"
                                v-if="authStore.user"
                            >
                                <p id="name" class="leading-none">
                                    <span class="nameFirst">
                                        {{ authStore.user.name }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        id="sub"
                        class="absolute right-3 top-2 w-[100px]"
                    ></div>
                </div>
                <!-- ヘッダーここまで -->

                <!-- カテゴリー名と所持数の表示部分 -->
                <div
                    id="intro"
                    class="pt-[9px] pr-[12px] pb-0 pl-[12px] relative z-[2]"
                >
                    <h1 id="title" class="text-xl font-bold leading-[1.2]">
                        {{
                            getCategoryName.getMainCategoryName(
                                mainCategoryName
                            )
                        }}
                    </h1>
                    <div
                        id="itemCount"
                        class="flex items-center mt-[6px] mr-0 mb-0 ml-0"
                    >
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
                            {{
                                categorizedItems[mainCategoryName]?.length || 0
                            }}
                        </p>
                    </div>
                </div>
                <!-- ここまで -->

                <div id="item-list" class="relative z-[1]">
                    <!-- アイテムリスト -->
                    <div
                        class="container grid grid-cols-3 pt-0 pr-0 pb-[20px] pl-0"
                    >
                        <div
                            class="pt-0 pr-[6px] pb-[5px] pl-[6px] w-full"
                            v-for="item in categorizedItems[mainCategoryName]"
                            :key="item.id + item.main_category"
                        >
                            <!-- アイテム画像表示 -->
                            <div id="imgContainer" class="relative">
                                <a @click="openItemOverlay(item)">
                                    <p id="img">
                                        <img
                                            v-if="item.file"
                                            :src="item.file"
                                            class="w-[121px] h-[146px] md:w-80 md:h-80 opacity-100 cursor-pointer"
                                        />
                                    </p>
                                </a>
                                <!-- アイテム画像表示ここまで -->
                            </div>
                            <div class="pt-[6px]">
                                <p
                                    class="text-[10px] font-bold leading-[1.2] w-full whitespace-nowrap overflow-hidden text-ellipsis"
                                >
                                    {{
                                        getCategoryName.getSubCategoryName(
                                            item.sub_category
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- アイテムリストここまで -->
                </div>

                <!-- ページネーション部分 -->
                <div
                    id="pager"
                    class="clear-both mt-[20px] mr-0 mb-0 ml-0 pt-0 pr-[10px] pb-20 pl-[10px] table w-full"
                >
                    <p
                        id="prev btn"
                        class="w-[30%] table-cell text-center align-middle"
                    >
                        <span
                            class="bg-[#f6f7f8] flex justify-center items-center pt-[12px] pr-0 pb-[12px] pl-0 text-[#fff] rounded-[4px] text-[0px] leading-none tracking-[0]"
                        >
                            <ChevronLeft class="text-[#aaa] text-[20px]" />
                        </span>
                    </p>
                    <p
                        id="pg-num"
                        class="text-center text-[#333] table-cell align-middle"
                    >
                        <span
                            id="num"
                            class="tracking-[0.08em] text-[15px] block leading-[1.4]"
                        >
                            1/1
                        </span>
                        <span
                            id="label"
                            class="text-[12px] block leading-[1.4]"
                        >
                            ページ
                        </span>
                    </p>
                    <p
                        id="next btn"
                        class="w-[30%] table-cell text-center align-middle"
                    >
                        <span
                            class="bg-[#f6f7f8] flex justify-center items-center pt-[12px] pr-0 pb-[12px] text-[#fff] rounded-[4px] text-[0px] leading-none tracking-[0]"
                        >
                            <ChevronRight class="text-[#aaa] text-[20px]" />
                        </span>
                    </p>
                </div>
                <!-- ページネーション部分ここまで -->
            </div>
        </div>
    </div>

    <ShowItemOverlay
        v-if="openOverlay"
        :item="currentItem"
        @delete-selected="deleteItem($event)"
        @close-overlay="openOverlay = false"
    />
</template>
