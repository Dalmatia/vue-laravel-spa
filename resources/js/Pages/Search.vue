<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useFollowStore } from '../stores/follow';

import ShowOutfitOverlay from '@/Components/Outfits/ShowOutfitOverlay.vue';
import SelectColor from './SelectColor.vue';
import { specialColors } from '../src/specialColors';

import Sort from 'vue-material-design-icons/SortVariant.vue';
import Filter from 'vue-material-design-icons/Tune.vue';
import Close from 'vue-material-design-icons/Close.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';
import axios from 'axios';

let openFilter = ref(false);
let currentOutfit = ref(null);
let openOverlay = ref(false);
const outfits = ref([]);
const followStore = useFollowStore();
const { getColorClass, getColorStyle } = specialColors();

// 検索する項目毎のデータの取得
const mainCategories = ref([]);
const subCategories = ref([]);
const colors = ref([]);
const seasons = ref([]);

const filters = ref({
    mainCategory: '',
    subCategory: '',
    color: null,
    season: '',
});

const openModal = ref(false);

// 投稿したコーディネートの表示
const fetchOutfits = async () => {
    try {
        const response = await axios.get('/api/outfits', {
            params: filters.value,
        });
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

// コーディネートの削除
const deleteOutfit = (object) => {
    let url = '';
    if (object.deleteType === 'Outfit') {
        url = `/api/outfit/` + object.id;
        axios
            .delete(url)
            .then((response) => {
                console.log(response);
                openOverlay.value = false;
                window.dispatchEvent(new Event('outfit-deleted'));
                fetchOutfits();
            })
            .catch((error) => {
                console.error(error);
            });
    }
};

const getEnums = async () => {
    try {
        const response = await axios.get('/api/enums');
        mainCategories.value = response.data.mainCategories;
        subCategories.value = response.data.subCategories;
        colors.value = response.data.colors;
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

const selectColor = (color) => {
    openModal.value = true;
    filters.value.color = color;
};

const filterByCategory = () => {
    fetchOutfits();
    openFilter.value = false;
};

// 指定した条件をクリアする
const clearFilters = () => {
    filters.value = {
        mainCategory: '',
        subCategory: '',
        color: null,
        season: '',
    };
    filterByCategory();
};

onMounted(async () => {
    try {
        await Promise.all([fetchOutfits(), getEnums()]);
    } catch (error) {
        console.error('データの取得に失敗しました。', error);
    }

    window.addEventListener('outfit-created', fetchOutfits);
    window.addEventListener('outfit-updated', fetchOutfits);
});

onUnmounted(() => {
    window.removeEventListener('outfit-created', fetchOutfits);
    window.removeEventListener('outfit-updated', fetchOutfits);
});
</script>

<template>
    <div
        class="box-border mx-auto max-w-6xl min-h-fit md:pl-[88px] lg:px-7 w-full"
    >
        <!-- デスクトップ用レイアウト -->
        <div class="hidden md:block md:w-[650px] lg:w-[880px]">
            <nav
                class="flex flex-col box-border items-stretch sticky top-0 bg-white"
            >
                <div class="relative flex flex-col h-auto">
                    <header class="relative flex flex-col h-auto">
                        <h1 class="text-center mb-5 text-lg font-semibold">
                            コーディネート検索
                        </h1>
                        <div
                            class="flex justify-between items-center border-t border-gray-200"
                        >
                            <div
                                class="flex items-center justify-between w-full px-4 h-11"
                            >
                                <button
                                    class="bg-transparent border-none cursor-pointer"
                                >
                                    <Sort :size="27" />
                                </button>

                                <button
                                    class="p-0 bg-transparent border-none cursor-pointer"
                                    @click="openFilter = !openFilter"
                                >
                                    <div
                                        class="p-0 bg-transparent border-none cursor-pointer"
                                    >
                                        <Filter v-if="!openFilter" :size="27" />
                                        <Close v-else :size="27" />
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- 絞り込み検索ドロップダウンメニュー -->
                        <div
                            class="absolute top-20 left-0 w-full bg-white"
                            v-if="openFilter"
                        >
                            <form class="w-full p-4">
                                <table class="w-full mb-10 border-separate">
                                    <tbody>
                                        <tr>
                                            <td
                                                class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white"
                                            >
                                                メインカテゴリー
                                            </td>
                                            <td
                                                class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2]"
                                            >
                                                <select
                                                    id="mainCategory"
                                                    v-model="
                                                        filters.mainCategory
                                                    "
                                                    class="w-full"
                                                >
                                                    <option value="">
                                                        メインカテゴリーを選択
                                                    </option>
                                                    <option
                                                        v-for="mainCategory in mainCategories"
                                                        :key="mainCategory.id"
                                                        :value="mainCategory.id"
                                                    >
                                                        {{ mainCategory.name }}
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                            >
                                                サブカテゴリー
                                            </td>
                                            <td
                                                class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2]"
                                            >
                                                <select
                                                    id="subCategory"
                                                    v-model="
                                                        filters.subCategory
                                                    "
                                                    class="w-full"
                                                >
                                                    <option value="">
                                                        サブカテゴリーを選択
                                                    </option>
                                                    <option
                                                        v-for="subCategory in subCategories"
                                                        :key="subCategory.id"
                                                        :value="subCategory.id"
                                                    >
                                                        {{ subCategory.name }}
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                            >
                                                カラー
                                            </td>
                                            <td
                                                class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2]"
                                            >
                                                <button
                                                    aria-label="カラーを選択"
                                                    type="button"
                                                    class="w-full border border-gray-300 px-4 py-2 rounded text-left"
                                                    @click="
                                                        selectColor(
                                                            filters.color
                                                        )
                                                    "
                                                >
                                                    <div
                                                        v-if="filters.color"
                                                        class="flex items-center gap-2"
                                                    >
                                                        <span>
                                                            選択中のカラー:
                                                        </span>
                                                        <div
                                                            class="w-5 h-5 rounded-full border"
                                                            :style="
                                                                getColorStyle(
                                                                    filters.color
                                                                )
                                                            "
                                                            :class="
                                                                getColorClass(
                                                                    filters.color
                                                                )
                                                            "
                                                        ></div>
                                                        <span class="text-sm">
                                                            {{
                                                                filters.color
                                                                    .name
                                                            }}
                                                        </span>
                                                    </div>
                                                    <div
                                                        v-else
                                                        class="flex items-center gap-2"
                                                    >
                                                        カラーを選択
                                                    </div>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="w-[310px] h-20 bg-[#f2f2f2] py-0 px-[30px] font-bold text-right border-y border-solid border-b-white box-border relative align-middle m-0 border-0 text-[100%] table-cell"
                                            >
                                                シーズン
                                            </td>
                                            <td
                                                class="py-0 pr-5 pl-10 border-y border-solid border-y-[#f2f2f2]"
                                            >
                                                <select
                                                    id="season"
                                                    v-model="filters.season"
                                                    class="w-full"
                                                >
                                                    <option value="">
                                                        シーズンを選択
                                                    </option>
                                                    <option
                                                        v-for="season in seasons"
                                                        :key="season.id"
                                                        :value="season.id"
                                                    >
                                                        {{ season.name }}
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div
                                    class="flex justify-center items-center mx-auto mb-7 w-full max-w-[750px] h-[50px] space-x-4"
                                >
                                    <button
                                        type="button"
                                        class="float-left text-[#999999] bg-white border border-solid border-[#cccccc] ml-0 w-[210px] h-[50px] text-sm mb-5 font-bold block box-border rounded-sm cursor-pointer leading-normal m-0 align-middle p-[1px]"
                                        @click="clearFilters()"
                                    >
                                        指定した条件をクリア
                                    </button>
                                    <button
                                        type="button"
                                        class="w-[210px] h-[50px] text-sm ml-[15px] float-left mb-5 border-0 text-white font-bold bg-black block box-border rounded-sm cursor-pointer leading-normal m-0 align-middle p-[1px]"
                                        @click="filterByCategory()"
                                    >
                                        この条件で絞り込む
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- ドロップダウンメニューここまで -->
                    </header>
                </div>
            </nav>

            <!-- コーディネート表示部分 -->
            <div id="outfit_section">
                <div
                    v-if="outfits.length === 0"
                    class="flex justify-center items-center w-full h-[calc(100vh-215px)]"
                >
                    <p class="text-gray-600 text-2xl font-bold text-center">
                        該当するコーディネートが見つかりませんでした。
                    </p>
                </div>
                <div
                    v-else
                    id="outfit_list"
                    class="grid grid-cols-3 md:grid-cols-4 pt-0 pr-0 pb-[20px] pl-0"
                >
                    <div
                        v-for="outfit in outfits"
                        :key="outfit.id"
                        class="pt-[10px] pr-[6px] pb-[5px] pl-[6px] w-full"
                    >
                        <div
                            class="float-left border-[1px] border-[#ddd] border-solid rounded-[3px] bg-white"
                        >
                            <p
                                class="w-full h-auto overflow-hidden bg-[#f6f7f8]"
                                @click="openOutfitOverlay(outfit)"
                            >
                                <img
                                    :src="outfit.file"
                                    class="lg:w-[203px] lg:h-[304px] md:w-[156px] md:h-[234px] cursor-pointer"
                                />
                            </p>
                            <div
                                id="user_profile"
                                class="border-t-0 pt-[9px] pr-[10px] pb-[9px] pl-[10px]"
                            >
                                <div
                                    id="profile_image"
                                    class="float-left w-[22px] md:w-[40px]"
                                >
                                    <img
                                        :src="outfit.user.file"
                                        class="rounded-full w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                                    />
                                </div>
                                <div
                                    id="username"
                                    class="w-auto float-right pt-[2px]"
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
                <header
                    class="bg-white flex flex-wrap text-[16px] font-semibold left-0 md:left-20 xl:left-64 fixed right-0 top-0 z-[1] border-b border-solid md:pl-0 xl:pl-0"
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
            </nav>
            <!-- ヘッダーここまで -->

            <!-- 絞り込み検索ドロップダウンメニュー -->
            <div
                class="fixed w-full left-0 inset-x-0 overflow-hidden z-40 bg-white top-14 pt-1"
                v-if="openFilter"
            >
                <ul
                    class="w-full list-none m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
                >
                    <li
                        class="transition-none block bg-white text-left m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent"
                    >
                        <form
                            class="m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
                        >
                            <div
                                class="overflow-hidden overflow-y-auto text-left m-0 p-0 border-0 text-[100%] align-baseline outline-0 bg-transparent block"
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
                                            <select
                                                id="mainCategory"
                                                v-model="filters.mainCategory"
                                                class="w-full box-border text-base leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                <option value="">
                                                    指定なし
                                                </option>
                                                <option
                                                    v-for="mainCategory in mainCategories"
                                                    :key="mainCategory.id"
                                                    :value="mainCategory.id"
                                                >
                                                    {{ mainCategory.name }}
                                                </option>
                                            </select>
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
                                            <select
                                                id="subCategory"
                                                v-model="filters.subCategory"
                                                class="w-full box-border text-base leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                <option value="">
                                                    指定なし
                                                </option>
                                                <option
                                                    v-for="subCategory in subCategories"
                                                    :key="subCategory.id"
                                                    :value="subCategory.id"
                                                >
                                                    {{ subCategory.name }}
                                                </option>
                                            </select>
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
                                            <button
                                                aria-label="カラーを選択"
                                                type="button"
                                                class="w-full box-border text-base leading-normal text-right pt-[15px] pr-0 pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                                @click="
                                                    selectColor(filters.color)
                                                "
                                            >
                                                <div
                                                    v-if="filters.color"
                                                    class="flex justify-end items-center gap-2"
                                                >
                                                    <span>
                                                        選択中のカラー:
                                                    </span>
                                                    <div
                                                        class="w-5 h-5 rounded-full border"
                                                        :style="
                                                            getColorStyle(
                                                                filters.color
                                                            )
                                                        "
                                                        :class="
                                                            getColorClass(
                                                                filters.color
                                                            )
                                                        "
                                                    ></div>
                                                    <span class="text-sm">
                                                        {{ filters.color.name }}
                                                    </span>
                                                </div>
                                                <div
                                                    v-else
                                                    class="flex justify-end items-center gap-2"
                                                >
                                                    カラーを選択
                                                    <ChevronRight />
                                                </div>
                                            </button>
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
                                            <select
                                                id="season"
                                                v-model="filters.season"
                                                class="w-full box-border text-base leading-normal text-right pt-[15px] pr-[15px] pb-4 pl-0 m-0 border-0 align-baseline outline-0 bg-transparent"
                                            >
                                                <option value="">
                                                    指定なし
                                                </option>
                                                <option
                                                    v-for="season in seasons"
                                                    :key="season.id"
                                                    :value="season.id"
                                                >
                                                    {{ season.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </li>
                                </ul>
                                <div class="py-0 px-5 mt-[25px] text-left">
                                    <div
                                        class="bg-white rounded border border-solid border-[#cccccc] box-border text-[#333333] cursor-pointer block text-sm font-bold h-[45px] leading-[44px] my-0 mx-auto p-0 text-center w-full"
                                        @click="clearFilters()"
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
                                    @click.prevent="filterByCategory()"
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
            <div id="outfit" class="z-[1]">
                <div
                    v-if="outfits.length === 0"
                    class="flex justify-center items-center w-full h-[calc(100vh-225px)]"
                >
                    <p
                        class="text-gray-600 font-semibold text-base text-center"
                    >
                        該当するコーディネートが見つかりませんでした。
                    </p>
                </div>
                <div
                    v-else
                    id="outfit_list"
                    class="grid grid-cols-3 pt-0 pr-0 pb-20 pl-0"
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
                                class="relative w-full h-auto overflow-hidden box-border border-0"
                                @click="openOutfitOverlay(outfit)"
                            >
                                <img
                                    :src="outfit.file"
                                    class="object-cover w-full aspect-[1/1.3]"
                                />
                            </p>
                            <div
                                id="user_profile"
                                class="flex items-center px-0 py-2 border-t border-gray-300"
                            >
                                <div
                                    id="profile_image"
                                    class="relative float-left w-[22px] mr-1"
                                >
                                    <img
                                        :src="outfit.user.file"
                                        class="opacity-100 rounded-[50%] w-[22px] h-[22px]"
                                    />
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
        @delete-selected="deleteOutfit($event)"
        @close-overlay="openOverlay = false"
    />
    <SelectColor
        v-if="openModal"
        :colors="colors"
        :selectedColor="filters.color?.id"
        @color-selected="selectColor($event)"
        @close="openModal = false"
    />
</template>
