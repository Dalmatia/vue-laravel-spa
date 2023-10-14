<script setup>
import ShowItemOptionsOverlay from './ShowItemOptionsOverlay.vue';
import { getEnumStore } from '../stores/enum.js';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

// const user = usePage().props.auth.user;
const selection = getEnumStore();

defineEmits(['closeOverlay', 'deleteSelected']);

const currentItem = defineProps({
    item: Object,
});

const fetchMainCategory = (main_category) => {
    return selection.getMainCategoryName(main_category);
};

const fetchSubCategory = (sub_category) => {
    return selection.getSubCategoryName(sub_category);
};

const fetchColor = (color) => {
    return selection.getColor(color);
};

const fetchSeason = (season) => {
    return selection.getSeason(season);
};
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <button
            class="absolute right-3 cursor-pointer"
            @click="$emit('closeOverlay')"
        >
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl"
        >
            <div
                class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300"
            >
                <ArrowLeft
                    class="cursor-pointer"
                    :size="30"
                    fillColor="#000000"
                    @click="$emit('closeOverlay')"
                />
                <div class="text-lg font-extrabold">アイテム詳細</div>
                <button>
                    <DotsHorizontal class="cursor-pointer" :size="27" />
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <div
                    class="flex items-center bg-gray-100 w-full h-full overflow-hidden"
                >
                    <img
                        class="h-full min-w-[200px] p-4 mx-auto"
                        :src="currentItem.item.file"
                    />
                </div>

                <!-- メインカテゴリー表示 -->
                <div id="TextAreaSection" class="max-w-[720px] w-full relative">
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            メインカテゴリー
                        </div>
                        <option>
                            {{
                                fetchMainCategory(
                                    currentItem.item.main_category
                                )
                            }}
                        </option>
                    </div>

                    <!-- サブカテゴリー表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            サブカテゴリー
                        </div>
                        <option>
                            {{
                                fetchSubCategory(currentItem.item.sub_category)
                            }}
                        </option>
                    </div>

                    <!-- カラー選択 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            カラー
                        </div>
                        <option>
                            {{ fetchColor(currentItem.item.color) }}
                        </option>
                    </div>

                    <!-- 季節表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <option>
                            {{ fetchSeason(currentItem.item.season) }}
                        </option>
                    </div>

                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            ref="textarea"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 text-gray-600 text-[18px] outline-none resize-none"
                            v-model="currentItem.item.memo"
                            readonly
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
