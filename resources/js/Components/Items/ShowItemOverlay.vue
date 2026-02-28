<script setup>
import { onMounted, ref, onUnmounted } from 'vue';
import { useAuthStore } from '../../stores/auth.js';
import { specialColors } from '../../src/specialColors';

import ShowItemOptionsOverlay from './ShowItemOptionsOverlay.vue';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

const authStore = useAuthStore();
const { getColorClass, getColorStyle } = specialColors();
const props = defineProps({ item: Object });
const item = ref(props.item);
const main_category = ref(null);
const sub_category = ref(null);
const color = ref(null);
const season = ref(null);
const deleteType = ref(null);
const id = ref(null);
// 選択したメイン・サブカテゴリー、カラー、季節の情報の取得
const mainCategories = ref([]);
const subCategories = ref([]);
const colors = ref([]);
const seasons = ref([]);

defineEmits(['closeOverlay', 'deleteSelected']);

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

// 登録・更新時のアイテム情報取得
const fetchItemData = async () => {
    try {
        const response = await axios.get(`/api/items/${item.value.id}`);

        // EditItemOverlay.vueのitemUpdateメソッドで変更された場合、データを反映
        const updatedItem = response.data;
        item.value = updatedItem;
        fetchSelectData();
    } catch (error) {
        console.error(error);
    }
};

// メインカテゴリーなどの情報取得
const fetchSelectData = async () => {
    try {
        const response = await axios.get('/api/enums');
        mainCategories.value = response.data.mainCategories;
        main_category.value = mainCategories.value.find(
            (m) => m.id === item.value.main_category
        );
        subCategories.value = response.data.subCategories;
        sub_category.value = subCategories.value.find(
            (s) => s.id === item.value.sub_category
        );
        colors.value = response.data.colors;
        color.value = colors.value.find((c) => c.id === item.value.color);
        seasons.value = response.data.seasons;
        season.value = seasons.value.find((s) => s.id === item.value.season);
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

onMounted(async () => {
    await Promise.all([fetchUserData(), fetchItemData(), fetchSelectData()]);
    // EditItemOverlay.vueのitemUpdateメソッドで定義したイベントの購読
    window.addEventListener('item-updated', fetchItemData);
});

onUnmounted(() => {
    window.removeEventListener('item-updated', fetchItemData);
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
        @click.self="$emit('closeOverlay')"
    >
        <button
            class="absolute right-3 cursor-pointer"
            @click="$emit('closeOverlay')"
        >
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] md:h-auto lg:h-[85%] mx-auto mt-10 bg-white rounded-xl"
        >
            <!-- ヘッダー部分 -->
            <div
                class="relative flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300"
            >
                <ArrowLeft
                    class="cursor-pointer z-10"
                    :size="30"
                    fillColor="#000000"
                    @click="$emit('closeOverlay')"
                />

                <div
                    class="absolute left-1/2 transform -translate-x-1/2 text-lg font-extrabold"
                >
                    アイテム詳細
                </div>

                <button
                    v-if="authStore.user.id === item.user_id"
                    class="z-10"
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
                <!-- アイテム画像表示部分 -->
                <div
                    class="flex items-center bg-gray-100 w-full md:w-1/2 h-fit md:h-auto overflow-hidden"
                >
                    <img
                        class="h-full w-full object-contain mx-auto p-4"
                        :src="item.file"
                    />
                </div>

                <!-- メインカテゴリー表示 -->
                <div
                    id="TextAreaSection"
                    class="md:w-1/2 w-full p-4 overflow-y-auto"
                >
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            メインカテゴリー
                        </div>
                        <span v-if="main_category">
                            {{ main_category.name }}
                        </span>
                    </div>

                    <!-- サブカテゴリー表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            サブカテゴリー
                        </div>
                        <span v-if="sub_category">
                            {{ sub_category.name }}
                        </span>
                    </div>

                    <!-- カラー選択 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            カラー
                        </div>
                        <div class="flex items-center gap-2" v-if="color">
                            <div
                                class="w-6 h-6 rounded-full border border-gray-300"
                                :style="getColorStyle(color)"
                                :class="getColorClass(color)"
                            ></div>
                            <!-- ラベル表示 -->
                            <span>{{ color.name }}</span>
                        </div>
                    </div>

                    <!-- 季節表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <span v-if="season">
                            {{ season.name }}
                        </span>
                    </div>

                    <!-- メモ表示 -->
                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            id="item_memo"
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
