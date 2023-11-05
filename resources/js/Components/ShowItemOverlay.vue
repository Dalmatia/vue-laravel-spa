<script setup>
import { onMounted, toRefs, ref } from 'vue';
import { useAuthStore } from '../stores/auth.js';
import { getEnumStore } from '../stores/enum.js';

import ShowItemOptionsOverlay from './ShowItemOptionsOverlay.vue';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

const deleteType = ref(null);
const id = ref(null);

const authStore = useAuthStore();
// ユーザー情報の取得
const fetchUserData = async () => {
    try {
        await authStore.fetchUserData();
    } catch (error) {
        if (error.response && error.response.status === 401) {
            handleUnauthorized();
        }
    }
};

const props = defineProps({ item: Object });
const { item } = toRefs(props);

// 選択したメイン・サブカテゴリー、カラー、季節の情報の取得
const selection = getEnumStore();

defineEmits(['closeOverlay', 'deleteSelected']);

// 選択したメイン・サブカテゴリー、カラー、季節の表示
const main_category = ref(null);
const sub_category = ref(null);
const color = ref(null);
const season = ref(null);

onMounted(() => {
    fetchUserData();
    main_category.value = selection.getMainCategoryName(
        item.value.main_category
    );
    sub_category.value = selection.getSubCategoryName(item.value.sub_category);
    color.value = selection.getColor(item.value.color);
    season.value = selection.getSeason(item.value.season);
});
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
                <button
                    v-if="authStore.user.id === item.user_id"
                    @click="
                        deleteType = 'Item';
                        id = item.id;
                    "
                >
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
                        :src="item.file"
                    />
                </div>

                <!-- メインカテゴリー表示 -->
                <div id="TextAreaSection" class="max-w-[720px] w-full relative">
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            メインカテゴリー
                        </div>
                        <option>
                            {{ main_category }}
                        </option>
                    </div>

                    <!-- サブカテゴリー表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            サブカテゴリー
                        </div>
                        <option>
                            {{ sub_category }}
                        </option>
                    </div>

                    <!-- カラー選択 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            カラー
                        </div>
                        <option>
                            {{ color }}
                        </option>
                    </div>

                    <!-- 季節表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <option>
                            {{ season }}
                        </option>
                    </div>

                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            ref="textarea"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 text-gray-600 text-[18px] outline-none resize-none"
                            v-model="item.memo"
                            readonly
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ShowItemOptionsOverlay
        v-if="deleteType"
        :deleteType="deleteType"
        :id="id"
        @deleteSelected="
            $emit('deleteSelected', {
                deleteType: $event.deleteType,
                id: $event.id,
                item: item,
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
