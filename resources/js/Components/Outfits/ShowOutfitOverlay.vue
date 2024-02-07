<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { getEnumStore } from '../../stores/enum';

import LikesSection from '../LikesSection.vue';
import ShowOutfitOptionsOverlay from './ShowOutfitOptionsOverlay.vue';

import Close from 'vue-material-design-icons/Close.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import EmoticonHappyOutline from 'vue-material-design-icons/EmoticonHappyOutline.vue';

const authStore = useAuthStore();

let comment = ref('');
let deleteType = ref(null);
let id = ref(null);

const props = defineProps(['outfit']);
const outfit = ref(props.outfit);
const postsByUser = outfit.value.user.name;
const tops = ref({});
const outer = ref({});
const bottoms = ref({});
const shoes = ref({});
const season = ref(null);

const tops_category = ref(null);
const tops_color = ref(null);
const outer_category = ref(null);
const outer_color = ref(null);
const bottoms_category = ref(null);
const bottoms_color = ref(null);
const shoes_category = ref(null);
const shoes_color = ref(null);
// 選択したシーズン情報の取得
const selectData = getEnumStore();

defineEmits(['closeOverlay', 'addComment', 'updateLike', 'deleteSelected']);

const fetchItem = async () => {
    try {
        const fetchItemData = async (itemId, item, category, color) => {
            const response = await axios.get(`/api/items/${itemId}`);
            const itemData = response.data;

            item.value = itemData;
            category.value = selectData.getSubCategoryName(
                itemData.sub_category
            );
            color.value = selectData.getColor(itemData.color);
        };

        const fetchPromises = [];

        if (outfit.value.tops) {
            fetchPromises.push(
                fetchItemData(
                    outfit.value.tops,
                    tops,
                    tops_category,
                    tops_color
                )
            );
        }

        if (outfit.value.outer) {
            fetchPromises.push(
                fetchItemData(
                    outfit.value.outer,
                    outer,
                    outer_category,
                    outer_color
                )
            );
        }

        if (outfit.value.bottoms) {
            fetchPromises.push(
                fetchItemData(
                    outfit.value.bottoms,
                    bottoms,
                    bottoms_category,
                    bottoms_color
                )
            );
        }

        if (outfit.value.shoes) {
            fetchPromises.push(
                fetchItemData(
                    outfit.value.shoes,
                    shoes,
                    shoes_category,
                    shoes_color
                )
            );
        }

        await Promise.all(fetchPromises);
        season.value = selectData.getSeason(outfit.value.season);
    } catch (error) {
        console.error('データの取得に失敗しました:', error);
    }
};

// 登録・更新時のアイテム情報取得
const fetchOutfit = async () => {
    try {
        const response = await axios.get(`/api/outfit/${outfit.value.id}`);

        // EditOutfitOverlay.vueのOutfitUpdateメソッドで変更された場合、データを反映
        const updatedOutfit = response.data;
        outfit.value = updatedOutfit;

        fetchItem();
    } catch (error) {
        console.error(error);
    }
};

const textareaInput = (e) => {
    textarea.value.style.height = 'auto';
    textarea.value.style.height = `${e.target.scrollHeight}px`;
};

onMounted(() => {
    fetchItem();
    fetchOutfit();
    // EditOutfitOverlay.vueのoutfitUpdateメソッドで定義したイベントの購読
    window.addEventListener('outfit-updated', fetchOutfit);
});

onUnmounted(() => {
    window.removeEventListener('outfit-updated', fetchOutfit);
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <button class="absolute right-3" @click="$emit('closeOverlay')">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl overflow-auto"
        >
            <!-- ヘッダーセクション -->
            <div class="top-[54px] z-contentHeader lg:block">
                <div
                    class="w-full bg-white pb-[14px] pt-[11px] border-b-[1px] border-gray-300 rounded-xl"
                >
                    <div class="relative mx-auto flex w-full">
                        <div class="flex items-center">
                            <p
                                class="h-12 w-12 overflow-hidden rounded-[50%] border border-gray-300 hover:opacity-70"
                            >
                                <a href="">
                                    <img
                                        class="rounded-full h-12 w-12"
                                        src="https://picsum.photos/id/54/800/820"
                                    />
                                </a>
                            </p>
                            <div class="pl-[10px]">
                                <div
                                    class="items-center pt-[2px] leading-[1.2] tracking-wider"
                                >
                                    <p class="font-extrabold text-[15px]">
                                        {{ postsByUser }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-3 mt-2">
                                <div class="w-[110px]">
                                    <button
                                        class="bg-blue-500 text-white lg:border lg:border-blue-700 lg:bg-gradient-to-b lg:from-blue-500 lg:to-blue-600 lg:hover:opacity-70 block text-center rounded-[4px] leading-[1] lg:rounded-[2px] w-full"
                                        type="button"
                                    >
                                        <span
                                            class="block pb-3 pt-[10px] font-bold tracking-[0.03em]"
                                            >フォローする</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="ml-auto mt-2">
                            <button
                                v-if="authStore.user.id === outfit.user_id"
                                @click="
                                    deleteType = 'Outfit';
                                    id = outfit.id;
                                "
                            >
                                <DotsHorizontal
                                    class="cursor-pointer"
                                    :size="27"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ここまでヘッダーセクション -->

            <!-- メインセクション -->
            <section
                class="lg:mx-auto lg:grid lg:w-[990px] lg:grid-cols-[558px_1fr] lg:gap-x-5 lg:pt-5 lg:grid-areas-[main_sub,main-bottom_sub]"
            >
                <!-- コーディネート・お気に入りやコメント等表示部分 -->
                <section
                    class="border-y border-gray-300 lg:h-fit lg:overflow-hidden lg:rounded-t-[3px] lg:border lg:place-items-center lg:grid-in-[main]"
                >
                    <!-- コーディネート画像表示部分 -->
                    <article class="relative">
                        <div
                            class="hide-scroll relative flex snap-x snap-mandatory overflow-x-auto"
                        >
                            <section
                                class="relative flex w-full shrink-0 snap-center"
                            >
                                <h1 class="flex">
                                    <span
                                        class="relative overflow-hidden max-w-full aspect-w-16 aspect-h-9"
                                    >
                                        <img
                                            class="w-full h-full lg:w-[556px] lg:h-[742px] p-4 mx-auto rounded-xl"
                                            :src="outfit.file"
                                        />
                                    </span>
                                </h1>
                            </section>
                        </div>
                    </article>

                    <!-- いいね!等の表示 -->
                    <div
                        class="justify-between border-t border-gray-300 bg-white px-[23px] py-6 lg:flex"
                    >
                        <LikesSection class="px-2 border-t mb-2" />
                    </div>

                    <!-- コメント欄 -->
                    <div class="hidden lg:block">
                        <div class="border-t border-gray-300 bg-white pt-5">
                            <div class="px-[23px]">
                                <h2
                                    class="text-[16px] font-bold leading-[1] tracking-wide"
                                >
                                    {{ postsByUser }} さんへのコメント
                                </h2>
                            </div>

                            <div class="pb-[30px]">
                                <div
                                    class="flex flex-col gap-4 pt-[17px] lg:pt-2"
                                >
                                    <div>
                                        <div class="flex flex-col gap-4">
                                            <div
                                                class="flex w-full gap-4 lg:gap-[15px] flex-row-reverse"
                                            >
                                                <a
                                                    class="flex h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-300 hover:opacity-70"
                                                >
                                                    <span
                                                        class="box-border inline-block overflow-hidden w-initial h-initial bg-none opacity-100 border-0 m-0 p-0 relative max-w-full"
                                                    >
                                                        <img
                                                            class="rounded-full w-[38px] h-[38px]"
                                                            src="https://picsum.photos/id/54/800/820"
                                                        />
                                                    </span>
                                                </a>

                                                <div
                                                    class="pt-[3px] lg:pt-[6px]"
                                                >
                                                    <div
                                                        class="flex flex-row-reverse"
                                                    >
                                                        <p
                                                            class="line-clamp-3 max-w-[272px] text-[12px] leading-[1.4] lg:max-w-[420px] lg:tracking-[0.03em]"
                                                        >
                                                            <span
                                                                class="lg:text-[14px]"
                                                            >
                                                                {{
                                                                    postsByUser
                                                                }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="flex items-end gap-[10px] lg:mt-[5px] flex-row-reverse"
                                                    >
                                                        <div
                                                            class="relative max-w-[262px] flex-1 rounded-b-[6px] bg-gray-500 px-[15px] py-2 lg:max-w-[362px] lg:border lg:border-gray-300 lg:bg-gray-50 rounded-tl-[6px]"
                                                        >
                                                            <span
                                                                class="absolute top-0 speech-bubble-right right-[-4px] lg:pc-speech-bubble-right"
                                                            ></span>
                                                            <p
                                                                class="whitespace-pre-wrap text-[15px] leading-[1.4] text-black lg:text-[13px] lg:leading-[1.6] lg:tracking-[0.03em] lg:text-black-400"
                                                            >
                                                                これはコメントです。
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="flex w-full gap-4 lg:gap-[15px]"
                                            >
                                                <a
                                                    href="/"
                                                    class="flex h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-300 hover:opacity-70"
                                                >
                                                    <span
                                                        class="box-border inline-block overflow-hidden w-initial h-initial bg-none opacity-100 border-0 m-0 p-0 relative max-w-full"
                                                    >
                                                        <img
                                                            class="rounded-full w-[38px] h-[38px]"
                                                            src="https://picsum.photos/id/54/800/820"
                                                        />
                                                    </span>
                                                </a>
                                                <div
                                                    class="pt-[3px] lg:pt-[6px]"
                                                >
                                                    <div class="flex">
                                                        <p
                                                            class="line-clamp-3 max-w-[272px] text-[12px] leading-[1.4] lg:max-w-[420px] lg:tracking-[0.03em]"
                                                        >
                                                            <span
                                                                class="lg:text-[14px]"
                                                                >名無し3</span
                                                            >
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="flex items-end gap-[10px] lg:mt-[5px]"
                                                    >
                                                        <div
                                                            class="relative max-w-[262px] flex-1 rounded-b-[6px] bg-gray-500 px-[15px] py-2 lg:max-w-[362px] lg:border lg:border-gray-300 lg:bg-gray-50 rounded-tr-[6px]"
                                                        >
                                                            <p
                                                                class="whitespace-pre-wrap text-[15px] leading-[1.4] text-black lg:text-[13px] lg:leading-[1.6] lg:tracking-[0.03em] lg:text-black-400"
                                                            >
                                                                素晴らしい!!
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- サブセクション -->
                <section
                    class="pt-[18px] lg:overflow-hidden lg:pt-0 lg:grid-in-[sub]"
                >
                    <div
                        class="flex flex-col lg:flex-col-reverse lg:rounded-[3px] lg:border lg:border-gray-300 lg:bg-white lg:pb-[6px] lg:pl-[23px] lg:pt-7"
                    >
                        <!-- 着用アイテム表示欄 -->
                        <section>
                            <div class="hidden pt-6 lg:block">
                                <div class="bg-gray-300 h-[1px]"></div>
                            </div>

                            <div class="lg:pt-6">
                                <div
                                    class="flex flex-col gap-[17px] lg:gap-5 lg:bg-white lg:pb-[23px]"
                                >
                                    <h2
                                        class="pl-4 pt-[6px] text-[14px] font-bold leading-none lg:pl-0 lg:pt-0 lg:text-[16px] lg:tracking-wide"
                                    >
                                        着用アイテム
                                    </h2>
                                    <ul
                                        class="flex flex-wrap px-[6px] lg:flex-col lg:gap-6 lg:px-0"
                                    >
                                        <li
                                            class="group w-1/3 px-[10px] lg:flex lg:w-full lg:flex-col lg:gap-6 lg:px-0"
                                            v-if="outfit.tops"
                                        >
                                            <div class="lg:flex">
                                                <a
                                                    class="relative flex justify-center border border-gray-300 lg:h-[120px] lg:w-[100px] lg:shrink-0 lg:overflow-hidden lg:rounded-[3px] lg:border-none lg:hover:opacity-70"
                                                    href=""
                                                >
                                                    <span class="item_style">
                                                        <img
                                                            :src="tops.file"
                                                            class="item_image"
                                                        />
                                                    </span>
                                                </a>
                                                <div
                                                    class="min-w-0 lg:grow lg:pl-[18px] lg:pr-[23px]"
                                                >
                                                    <p
                                                        class="truncate pt-1 text-[10px] leading-[1.4] lg:mt-[-3px] lg:pt-0 lg:text-[15px] lg:font-bold lg:leading-[1.6] lg:tracking-wide"
                                                    >
                                                        <span
                                                            class="hidden lg:inline"
                                                        >
                                                            トップス
                                                        </span>
                                                    </p>
                                                    <p
                                                        class="truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                                                    >
                                                        <a
                                                            href=""
                                                            class="hidden xl:inline xl:text-blue-500 xl:hover:underline"
                                                        >
                                                            {{ tops_category }}
                                                            ({{ tops_color }})
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="hidden lg:block lg:group-last:hidden"
                                            >
                                                <div
                                                    class="bg-gray-300 h-[1px]"
                                                ></div>
                                            </div>
                                        </li>
                                        <li
                                            class="group w-1/3 px-[10px] lg:flex lg:w-full lg:flex-col lg:gap-6 lg:px-0"
                                            v-if="outfit.outer"
                                        >
                                            <div class="lg:flex">
                                                <a
                                                    class="relative flex justify-center border border-gray-300 lg:h-[120px] lg:w-[100px] lg:shrink-0 lg:overflow-hidden lg:rounded-[3px] lg:border-none lg:hover:opacity-70"
                                                    href=""
                                                >
                                                    <span class="item_style">
                                                        <img
                                                            :src="outer.file"
                                                            class="item_image"
                                                        />
                                                    </span>
                                                </a>
                                                <div
                                                    class="min-w-0 lg:grow lg:pl-[18px] lg:pr-[23px]"
                                                >
                                                    <p
                                                        class="truncate pt-1 text-[10px] leading-[1.4] lg:mt-[-3px] lg:pt-0 lg:text-[15px] lg:font-bold lg:leading-[1.6] lg:tracking-wide"
                                                    >
                                                        <span
                                                            class="hidden lg:inline"
                                                        >
                                                            アウター
                                                        </span>
                                                    </p>
                                                    <p
                                                        class="truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                                                    >
                                                        <a
                                                            href=""
                                                            class="hidden xl:inline xl:text-blue-500 xl:hover:underline"
                                                        >
                                                            {{ outer_category }}
                                                            ({{ outer_color }})
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="hidden lg:block lg:group-last:hidden"
                                            >
                                                <div
                                                    class="bg-gray-300 h-[1px]"
                                                ></div>
                                            </div>
                                        </li>
                                        <li
                                            class="group w-1/3 px-[10px] lg:flex lg:w-full lg:flex-col lg:gap-6 lg:px-0"
                                            v-if="outfit.bottoms"
                                        >
                                            <div class="lg:flex">
                                                <a
                                                    class="relative flex justify-center border border-gray-300 lg:h-[120px] lg:w-[100px] lg:shrink-0 lg:overflow-hidden lg:rounded-[3px] lg:border-none lg:hover:opacity-70"
                                                    href=""
                                                >
                                                    <span class="item_style">
                                                        <img
                                                            :src="bottoms.file"
                                                            class="item_image"
                                                        />
                                                    </span>
                                                </a>
                                                <div
                                                    class="min-w-0 lg:grow lg:pl-[18px] lg:pr-[23px]"
                                                >
                                                    <p
                                                        class="truncate pt-1 text-[10px] leading-[1.4] lg:mt-[-3px] lg:pt-0 lg:text-[15px] lg:font-bold lg:leading-[1.6] lg:tracking-wide"
                                                    >
                                                        <span
                                                            class="hidden lg:inline"
                                                        >
                                                            ボトムス
                                                        </span>
                                                    </p>
                                                    <p
                                                        class="truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                                                    >
                                                        <a
                                                            href=""
                                                            class="hidden xl:inline xl:text-blue-500 xl:hover:underline"
                                                        >
                                                            {{
                                                                bottoms_category
                                                            }}
                                                            ({{
                                                                bottoms_color
                                                            }})
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="hidden lg:block lg:group-last:hidden"
                                            >
                                                <div
                                                    class="bg-gray-300 h-[1px]"
                                                ></div>
                                            </div>
                                        </li>
                                        <li
                                            class="group w-1/3 px-[10px] lg:flex lg:w-full lg:flex-col lg:gap-6 lg:px-0"
                                            v-if="outfit.shoes"
                                        >
                                            <div class="lg:flex">
                                                <a
                                                    class="relative flex justify-center border border-gray-300 lg:h-[120px] lg:w-[100px] lg:shrink-0 lg:overflow-hidden lg:rounded-[3px] lg:border-none lg:hover:opacity-70"
                                                    href=""
                                                >
                                                    <span class="item_style">
                                                        <img
                                                            :src="shoes.file"
                                                            class="item_image"
                                                        />
                                                    </span>
                                                </a>
                                                <div
                                                    class="min-w-0 lg:grow lg:pl-[18px] lg:pr-[23px]"
                                                >
                                                    <p
                                                        class="truncate pt-1 text-[10px] leading-[1.4] lg:mt-[-3px] lg:pt-0 lg:text-[15px] lg:font-bold lg:leading-[1.6] lg:tracking-wide"
                                                    >
                                                        <span
                                                            class="hidden lg:inline"
                                                        >
                                                            シューズ
                                                        </span>
                                                    </p>
                                                    <p
                                                        class="truncate text-[10px] leading-[1.4] xl:pt-[3px] xl:text-[12px]"
                                                    >
                                                        <a
                                                            href=""
                                                            class="hidden xl:inline xl:text-blue-500 xl:hover:underline"
                                                        >
                                                            {{ shoes_category }}
                                                            ({{ shoes_color }})
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="hidden lg:block lg:group-last:hidden"
                                            >
                                                <div
                                                    class="bg-gray-300 h-[1px]"
                                                ></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- コーディネートの紹介や着用日等の表示 -->
                        <section class="lg:pr-[23px]">
                            <h1
                                class="hidden text-[16px] font-bold leading-[1.5] lg:block"
                            >
                                {{ postsByUser }}さんのコーディネート
                            </h1>
                            <div class="px-4 pt-[38px] lg:px-0 lg:pt-4">
                                <div>
                                    <p
                                        class="overflow-y-hidden whitespace-pre-line text-[14px] leading-[1.8] lg:leading-[1.6] h-[224px]"
                                    >
                                        {{ outfit.description }}
                                    </p>
                                </div>
                                <div class="pt-1 lg:pt-5">
                                    <p
                                        class="flex items-center justify-between leading-[1.8] text-gray-500 lg:text-[12px] lg:leading-none lg:tracking-widest lg:text-gray-600"
                                    >
                                        <span class="pr-[5px] lg:text-[13px]">
                                            着用日:
                                            <span>
                                                {{ outfit.outfit_date }}
                                            </span>
                                        </span>

                                        <span class="pr-[5px] lg:text-[13px]">
                                            シーズン:
                                            <span>{{ season }}</span>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </section>

                        <div class="pb-16 md:hidden"></div>
                    </div>

                    <!-- <div
                        class="absolute flex border bottom-0 w-full max-h-[200px] bg-white overflow-auto"
                    >
                        <EmoticonHappyOutline
                            class="pl-3 pt-[10px]"
                            :size="30"
                        />
                        <textarea
                            ref="textarea"
                            :onInput="textareaInput"
                            v-model="comment"
                            placeholder="コメントする"
                            rows="1"
                            class="w-full border-0 mt-4 mb-2 text-sm z-50 focus:ring-0 text-gray-600 text-[18px] outline-none"
                        ></textarea>
                        <button
                            v-if="comment"
                            class="text-blue-600 font-extrabold pr-4 min-w-[70px]"
                        >
                            送信
                        </button>
                    </div> -->
                </section>
                <!-- ここまでサブセクション -->
            </section>
            <!-- ここまでメインセクション -->
        </div>
    </div>
    <ShowOutfitOptionsOverlay
        v-if="deleteType"
        :deleteType="deleteType"
        :id="id"
        @deleteSelected="
            $emit('deleteSelected', {
                deleteType: $event.deleteType,
                id: $event.id,
                outfit: outfit,
            });
            deleteType = null;
            id = null;
        "
        @close="
            deleteType = null;
            id = null;
        "
    />
</template>
