<script setup>
import { defineEmits, defineProps, ref, onMounted } from 'vue';
import axios from 'axios';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

import { useEditItemForm } from '../../../src/composables/editItemForm';
import { useCategoryOptions } from '../../../src/composables/categoryOptions';
import { specialColors } from '../../../src/specialColors';
import SelectColor from '@/pages/SelectColor.vue';
import FileUpdatePreview from '../../FileUpdatePreview.vue';

const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editItem: Object, required: true });
const colors = ref([]);
const seasons = ref([]);
const openModal = ref(false);
const selectedColor = ref(null);
const {
    editForm,
    formErrors,
    isValidFile,
    fileDisplay,
    updateItem,
    getUploadedImage,
} = useEditItemForm(props.editItem, () => emit('closeOverlay'));

const { mainCategories, subCategories, fetchAllCategories } =
    useCategoryOptions(() => editForm.value.main_category);

const { getColorClass, getColorStyle } = specialColors();

// メインカテゴリーなどの情報取得
const fetchEnums = async () => {
    try {
        const response = await axios.get('/api/enums');
        colors.value = response.data.colors;
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

const selectColor = (color) => {
    if (selectedColor.value?.id === color?.id) {
        // 同じ色をもう一度選ぶ → 選択解除
        selectedColor.value = null;
        editForm.value.color = null;
    } else {
        // 新しい色を選択
        selectedColor.value = color;
        editForm.value.color = color?.id || null;
    }
    openModal.value = false;
};

onMounted(async () => {
    await fetchEnums();
    if (props.editItem.color) {
        editForm.value.color = props.editItem.color;
        selectedColor.value = colors.value.find(
            (c) => c.id === props.editItem.color
        );
    }
    await fetchAllCategories();
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
        @click.self="emit('closeOverlay')"
    >
        <button
            class="absolute right-3 cursor-pointer"
            @click="emit('closeOverlay')"
        >
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-6xl h-[calc(100%-100px)] md:h-auto lg:h-[85%] mx-auto mt-10 bg-white rounded-xl"
        >
            <!-- ヘッダー部分 -->
            <div
                class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300"
            >
                <ArrowLeft
                    class="cursor-pointer"
                    :size="30"
                    fillColor="#000000"
                    @click="emit('closeOverlay')"
                />
                <div class="text-lg font-extrabold">アイテム編集</div>
                <button
                    class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
                    @click="updateItem()"
                >
                    更新
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <!-- アイテム画像編集部分 -->
                <FileUpdatePreview
                    :file-display="fileDisplay"
                    :form-errors="formErrors"
                    :is-valid-file="isValidFile"
                    :initial-image="editForm.file"
                    @file-selected="getUploadedImage($event)"
                />

                <div
                    id="TextAreaSection"
                    class="md:w-1/2 w-full p-4 overflow-y-auto"
                >
                    <!-- メインカテゴリー表示 -->
                    <div
                        v-if="formErrors.main_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ formErrors.main_category[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            メインカテゴリー
                        </div>
                        <select
                            id="main_category"
                            v-model="editForm.main_category"
                            required
                        >
                            <option value="" disabled>選択してください</option>
                            <option
                                v-for="mainCategory in mainCategories"
                                :key="mainCategory.id"
                                :value="mainCategory.id"
                            >
                                {{ mainCategory.name }}
                            </option>
                        </select>
                    </div>

                    <!-- サブカテゴリー表示 -->
                    <div
                        v-if="formErrors.sub_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ formErrors.sub_category[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            サブカテゴリー
                        </div>
                        <select
                            id="sub_category"
                            v-model="editForm.sub_category"
                        >
                            <option :value="null">指定なし</option>
                            <option
                                v-for="subCategory in subCategories"
                                :key="subCategory.id"
                                :value="subCategory.id"
                            >
                                {{ subCategory.name }}
                            </option>
                        </select>
                    </div>

                    <!-- カラー選択 -->
                    <div
                        v-if="formErrors.color"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ formErrors.color[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            カラー選択
                        </div>
                        <button
                            aria-label="カラーを選択"
                            type="button"
                            class="text-base leading-normal text-right"
                            @click="openModal = true"
                        >
                            <div
                                v-if="selectedColor"
                                class="flex justify-end items-center gap-2"
                            >
                                <span> 選択中のカラー: </span>
                                <div
                                    class="w-5 h-5 rounded-full border"
                                    :style="getColorStyle(selectedColor)"
                                    :class="getColorClass(selectedColor)"
                                ></div>
                                <span class="text-sm">
                                    {{ selectedColor.name }}
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

                    <!-- 季節表示 -->
                    <div
                        v-if="formErrors.season"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ formErrors.season[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <select id="season" v-model="editForm.season">
                            <option :value="null">指定なし</option>
                            <option
                                v-for="season in seasons"
                                :key="season.id"
                                :value="season.id"
                            >
                                {{ season.name }}
                            </option>
                        </select>
                    </div>

                    <!-- メモ表示部分 -->
                    <div
                        v-if="formErrors.memo"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ formErrors.memo }}
                    </div>
                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            id="memo"
                            ref="textarea"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 text-gray-600 text-[18px] outline-none resize-none"
                            v-model="editForm.memo"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <SelectColor
        v-if="openModal"
        :colors="colors"
        :selectedColor="selectedColor?.id"
        @color-selected="selectColor($event)"
        @close="openModal = false"
    />
</template>
