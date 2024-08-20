<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useFollowStore } from '../stores/follow';

import ShowOutfitOverlay from '@/Components/Outfits/ShowOutfitOverlay.vue';

import Sort from 'vue-material-design-icons/SortVariant.vue';
import Filter from 'vue-material-design-icons/Tune.vue';
import Close from 'vue-material-design-icons/Close.vue';

let openFilter = ref(false);
let currentOutfit = ref(null);
let openOverlay = ref(false);

const outfits = ref([]);
const authStore = useAuthStore();
const user = computed(() => (authStore.user ? authStore.user.id : null));
const followStore = useFollowStore();
const seasons = ref([]);

// 投稿したコーディネートの表示
const fetchOutfits = async () => {
    try {
        const response = await axios.get('/api/home');
        outfits.value = response.data.outfits;

        // 各ユーザーのフォロー状態をチェック
        const follows = outfits.value.map((outfit) => outfit.user.id);
        await followStore.fetchFollowStatus(follows);
    } catch (error) {
        console.error('コーディネート一覧の取得に失敗しました。', error);
    }
};

const openOutfitOverlay = (outfit) => {
    currentOutfit.value = outfit;
    openOverlay.value = true;
};

const getSeason = async () => {
    try {
        const response = await axios.get('/api/enums');
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

onMounted(() => {
    fetchOutfits();
    getSeason();
});
</script>

<template>
    <div
        class="box-border mx-auto max-w-6xl min-h-fit md:pl-[96px] lg:px-7 relative w-full"
    >
        <!-- デスクトップ用レイアウト -->
        <div class="hidden md:block">
            <h1 class="text-center mb-5">コーディネート検索</h1>
            <header class="flex justify-between items-center border-t">
                <div class="flex items-center justify-between w-full px-4 h-11">
                    <button
                        class="flex items-center p-0 bg-transparent border-none cursor-pointer"
                    >
                        <Sort :size="27" />
                    </button>

                    <button
                        class="flex items-center p-0 bg-transparent border-none cursor-pointer"
                        @click="openFilter = !openFilter"
                    >
                        <div class="items-center flex justify-end">
                            <Filter id="filter" v-if="!openFilter" :size="27" />
                            <Close id="close_filter" v-else :size="27" />
                        </div>
                    </button>
                </div>
            </header>

            <!-- 絞り込み検索ドロップダウンメニュー -->
            <div class="absolute w-full m-0 p-0 border-0 z-40 bg-white">
                <form action="/search/" v-if="openFilter">
                    <table class="w-full mb-10 border-separate">
                        <tbody
                            class="m-0 border-0 text-[100%] align-baseline table-row-group"
                        >
                            <tr
                                class="m-0 p-0 border-0 text-[100%] align-baseline table-row"
                            >
                                <td
                                    class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    メインカテゴリー
                                </td>
                                <td
                                    class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2] relative text-left font-normal align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    <div
                                        class="flex items-center justify-between text-left m-0 p-0 border-0 text-[100%] align-baseline"
                                    >
                                        <a
                                            class="flex items-center !text-sm cursor-pointer m-0 p-0 border-0 align-baseline bg-transparent"
                                        >
                                            メインカテゴリーを選択
                                        </a>
                                        <input
                                            type="hidden"
                                            class="leading-normal m-0 align-middle p-[1px] !hidden !overflow-clip"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr
                                class="m-0 p-0 border-0 text-[100%] align-baseline table-row"
                            >
                                <td
                                    class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    サブカテゴリー
                                </td>
                                <td
                                    class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2] relative text-left font-normal align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    <div
                                        class="flex items-center justify-between text-left m-0 p-0 border-0 text-[100%] align-baseline"
                                    >
                                        <a
                                            href=""
                                            class="flex items-center !text-sm cursor-pointer m-0 p-0 border-0 align-baseline bg-transparent"
                                        >
                                            サブカテゴリーを選択
                                        </a>
                                        <input
                                            type="hidden"
                                            class="leading-normal m-0 align-middle p-[1px] !hidden !overflow-clip"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr
                                class="m-0 p-0 border-0 text-[100%] align-baseline table-row"
                            >
                                <td
                                    class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    カラー
                                </td>
                                <td
                                    class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2] relative text-left font-normal align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    <div
                                        class="flex items-center justify-between text-left m-0 p-0 border-0 text-[100%] align-baseline"
                                    >
                                        <a
                                            href=""
                                            class="flex items-center !text-sm cursor-pointer m-0 p-0 border-0 align-baseline bg-transparent"
                                        >
                                            カラーを選択
                                        </a>
                                        <input
                                            type="hidden"
                                            class="leading-normal m-0 align-middle p-[1px] !hidden !overflow-clip"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr
                                class="m-0 p-0 border-0 text-[100%] align-baseline table-row"
                            >
                                <td
                                    class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    シーズン
                                </td>
                                <td
                                    class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2] relative text-left font-normal align-middle m-0 border-0 text-[100%] table-cell"
                                >
                                    <div
                                        class="flex items-center justify-between text-left m-0 p-0 border-0 text-[100%] align-baseline"
                                    >
                                        <a
                                            href=""
                                            class="flex items-center !text-sm cursor-pointer m-0 p-0 border-0 align-baseline bg-transparent"
                                        >
                                            シーズンを選択
                                        </a>
                                        <input
                                            type="hidden"
                                            class="leading-normal m-0 align-middle p-[1px] !hidden !overflow-clip"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div
                        class="flex justify-center items-center mx-auto mb-7 w-full max-w-[750px] h-[50px] space-x-4"
                    >
                        <input
                            type="button"
                            class="float-left text-[#999999] bg-white border border-solid border-[#cccccc] ml-0 w-[210px] h-[50px] text-sm mb-5 font-bold block box-border rounded-sm cursor-pointer leading-normal m-0 align-middle p-[1px]"
                            value="指定した条件をクリア"
                        />
                        <input
                            type="submit"
                            class="w-[210px] h-[50px] text-sm ml-[15px] float-left mb-5 border-0 text-white font-bold bg-black block box-border rounded-sm cursor-pointer leading-normal m-0 align-middle p-[1px]"
                            value="この条件で絞り込む"
                        />
                    </div>
                </form>
            </div>
            <!-- ドロップダウンメニューここまで -->

            <!-- コーディネート表示部分 -->
            <div id="outfit" class="relative z-[1]">
                <div
                    id="outfit_list"
                    class="grid grid-cols-3 md:grid-cols-4 pt-0 pr-0 pb-[20px] pl-0"
                >
                    <div
                        v-for="outfit in outfits"
                        :key="outfit.id"
                        class="pt-[10px] pr-[6px] pb-[5px] pl-[6px] w-full"
                    >
                        <div
                            class="relative float-left border-[1px] border-[#ddd] border-solid rounded-[3px] md:mt-[18px] md:mr-0 md:mb-0 md:ml-[18px] bg-white"
                        >
                            <p
                                class="relative w-full h-auto overflow-hidden bg-[#f6f7f8]"
                            >
                                <a
                                    @click="openOutfitOverlay(outfit)"
                                    class="block"
                                >
                                    <img
                                        :src="outfit.file"
                                        class="w-full h-auto opacity-100 cursor-pointer"
                                    />
                                </a>
                            </p>
                            <div
                                id="user_profile"
                                class="border-t-0 pt-[9px] pr-[10px] pb-[9px] pl-[10px]"
                            >
                                <div
                                    id="profile_image"
                                    class="relative float-left w-[22px] md:w-[40px]"
                                >
                                    <p
                                        id="image"
                                        class="text-[0px] leading-[1] tracking-[0] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                    >
                                        <a href="">
                                            <img
                                                src="https://picsum.photos/id/32/32/32"
                                                class="opacity-100 rounded-[50%] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                            />
                                        </a>
                                    </p>
                                </div>
                                <div
                                    id="username"
                                    class="w-auto float-right pt-[2px] pr-0 pb-0 pl-0"
                                >
                                    <p
                                        class="text-[10px] md:text-[13.5px] font-bold"
                                    >
                                        <span
                                            v-if="
                                                outfit.user && outfit.user.name
                                            "
                                            class="float-left max-w-full whitespace-nowrap overflow-hidden text-ellipsis"
                                        >
                                            {{ outfit.user.name }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- コーディネート表示部分ここまで -->
        </div>
        <!-- モバイル用レイアウト -->
        <div class="block md:hidden">
            <!-- ヘッダー部分 -->
            <nav class="relative flex-col items-stretch box-border">
                <div class="relative flex-col items-stretch box-border">
                    <header
                        class="bg-white flex flex-col flex-wrap text-[16px] font-semibold left-0 md:left-20 xl:left-64 fixed right-0 top-0 z-[1] border-b border-solid md:pl-0 xl:pl-0"
                    >
                        <div
                            class="fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300"
                        >
                            <div class="items-center flex basis-8 flex-row">
                                <button
                                    class="p-0 bg-transparent border-none cursor-pointer flex items-center"
                                    type="button"
                                >
                                    <Sort :size="27" />
                                </button>
                            </div>
                            <h1 class="text-center">コーディネート検索</h1>
                            <div class="flex items-center">
                                <button
                                    class="p-0 bg-transparent border-none cursor-pointer flex items-center"
                                    type="button"
                                    @click="openFilter = !openFilter"
                                >
                                    <Filter v-if="!openFilter" :size="27" />

                                    <Close v-if="openFilter" :size="27" />
                                </button>
                            </div>
                        </div>
                    </header>
                </div>
            </nav>
            <!-- ヘッダーここまで -->

            <!-- 絞り込み検索ドロップダウンメニュー -->
            <div
                class="fixed w-full overflow-hidden z-40 bg-white"
                v-if="openFilter"
            >
                <ul
                    class="w-[200%] list-none m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
                >
                    <li
                        class="pb-[115px] float-left w-[50%] transition-none block bg-white text-left m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                    >
                        <form
                            action="/search/"
                            class="m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
                        >
                            <div
                                class="overflow-hidden overflow-y-scroll text-left m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
                            >
                                <ul
                                    class="list-none m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block ms-0 me-0"
                                >
                                    <li
                                        class="cursor-pointer block py-0 px-[10px] text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                    >
                                        <div
                                            class="border-y border-solid border-[#f0f0f0] box-border h-auto py-0 px-[10px] relative flex items-center justify-between w-full text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                        >
                                            <span
                                                class="w-auto text-base shrink-0 !pr-5 m-0 p-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                メインカテゴリー
                                            </span>
                                            <span
                                                class="w-full box-border text-xs leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                指定なし
                                            </span>
                                            <input
                                                type="hidden"
                                                class="leading-normal m-0 align-middle !hidden !overflow-clip appearance-none cursor-default"
                                            />
                                        </div>
                                    </li>
                                    <li
                                        class="cursor-pointer block py-0 px-[10px] text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                    >
                                        <div
                                            class="border-b border-solid border-[#f0f0f0] box-border h-auto py-0 px-[10px] relative flex items-center justify-between w-full text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                        >
                                            <span
                                                class="w-auto text-base shrink-0 !pr-5 m-0 p-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                サブカテゴリー
                                            </span>
                                            <span
                                                class="w-full box-border text-xs leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                指定なし
                                            </span>
                                            <input
                                                type="hidden"
                                                class="leading-normal m-0 align-middle !hidden !overflow-clip appearance-none cursor-default"
                                            />
                                        </div>
                                    </li>
                                    <li
                                        class="cursor-pointer block py-0 px-[10px] text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                    >
                                        <div
                                            class="border-b border-solid border-[#f0f0f0] box-border h-auto py-0 px-[10px] relative flex items-center justify-between w-full text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                        >
                                            <span
                                                class="w-auto text-base shrink-0 !pr-5 m-0 p-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                カラー
                                            </span>
                                            <span
                                                class="w-full box-border text-xs leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                指定なし
                                            </span>
                                            <input
                                                type="hidden"
                                                class="leading-normal m-0 align-middle !hidden !overflow-clip appearance-none cursor-default"
                                            />
                                        </div>
                                    </li>
                                    <li
                                        class="cursor-pointer block py-0 px-[10px] text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                    >
                                        <div
                                            class="border-b border-solid border-[#f0f0f0] box-border h-auto py-0 px-[10px] relative flex items-center justify-between w-full text-left m-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                                        >
                                            <span
                                                class="w-auto text-base shrink-0 !pr-5 m-0 p-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                シーズン
                                            </span>
                                            <span
                                                class="w-full box-border text-xs leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                指定なし
                                            </span>
                                            <input
                                                type="hidden"
                                                class="leading-normal m-0 align-middle !hidden !overflow-clip appearance-none cursor-default"
                                            />
                                        </div>
                                    </li>
                                </ul>
                                <div class="py-0 px-5 mt-[25px] text-left">
                                    <div
                                        class="bg-white rounded border border-solid border-[#cccccc] box-border text-[#333333] cursor-pointer block text-sm font-bold h-[45px] leading-[44px] my-0 mx-auto p-0 text-center w-full"
                                    >
                                        指定した条件をクリア
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-white box-border left-0 mb-[60px] mt-[10px] py-0 px-5 w-full z-[1000]"
                            >
                                <button
                                    class="m-0 p-0 w-full bg-black rounded border border-solid border-transparent box-border text-white cursor-pointer block text-sm font-bold h-[45px] leading-[44px] text-center overflow-visible"
                                >
                                    この条件で絞り込む
                                </button>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
            <!-- ドロップダウンメニューここまで -->

            <!-- コーディネート表示部分 -->
            <div id="outfit" class="relative z-[1]">
                <div
                    id="outfit_list"
                    class="container grid grid-cols-3 md:grid-cols-4 pt-0 pr-0 pb-[20px] pl-0"
                >
                    <div
                        v-for="outfit in outfits"
                        :key="outfit.id"
                        class="pt-[10px] pr-[6px] pb-[5px] pl-[6px] w-full"
                    >
                        <div
                            class="relative float-left border-[1px] border-[#ddd] border-solid rounded-[3px] md:mt-[18px] md:mr-0 md:mb-0 md:ml-[18px] bg-white"
                            @click="openOutfitOverlay(outfit)"
                        >
                            <p
                                class="relative w-full h-auto overflow-hidden bg-[#f6f7f8]"
                            >
                                <a
                                    @click="openOutfitOverlay(outfit)"
                                    class="block"
                                >
                                    <img
                                        :src="outfit.file"
                                        class="w-full h-auto opacity-100 cursor-pointer"
                                    />
                                </a>
                            </p>
                            <div
                                id="user_profile"
                                class="border-t-0 pt-[9px] pr-[10px] pb-[9px] pl-[10px]"
                            >
                                <div
                                    id="profile_image"
                                    class="relative float-left w-[22px] md:w-[40px]"
                                >
                                    <p
                                        id="image"
                                        class="text-[0px] leading-[1] tracking-[0] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                    >
                                        <a href="">
                                            <img
                                                src="https://picsum.photos/id/32/32/32"
                                                class="opacity-100 rounded-[50%] w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                            />
                                        </a>
                                    </p>
                                </div>
                                <div
                                    id="username"
                                    class="w-auto float-right pt-[2px] pr-0 pb-0 pl-0"
                                >
                                    <p
                                        class="text-[10px] md:text-[13.5px] font-bold"
                                    >
                                        <span
                                            v-if="
                                                outfit.user && outfit.user.name
                                            "
                                            class="float-left max-w-full whitespace-nowrap overflow-hidden text-ellipsis"
                                        >
                                            {{ outfit.user.name }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- コーディネート表示部分ここまで -->
        </div>
    </div>
    <ShowOutfitOverlay
        v-if="openOverlay"
        :outfit="currentOutfit"
        @close-overlay="openOverlay = false"
    />
</template>
