<script setup>
import { defineEmits, defineProps, ref, onMounted, watch } from 'vue';
import axios from 'axios';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

import SelectColor from '@/pages/SelectColor.vue';
import { specialColors } from '../../src/specialColors';

const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editItem: Object, required: true });
const editForm = ref({ ...props.editItem });

const mainCategories = ref([]);
const subCategories = ref([]);
const colors = ref([]);
const seasons = ref([]);
const openModal = ref(false);
const selectedColor = ref(null);
const { getColorClass, getColorStyle } = specialColors();

let isValidFile = ref(null);
let fileDisplay = ref('');
let error = ref({
    file: null,
    main_category: '',
    sub_category: '',
    color: '',
    season: '',
    memo: null,
});

// エラー処理を共通化
const setErrors = (responseErrors) => {
    error.value.file = responseErrors.file || null;
    error.value.main_category = responseErrors.main_category || '';
    error.value.sub_category = responseErrors.sub_category || '';
    error.value.color = responseErrors.color || '';
    error.value.season = responseErrors.season || '';
    error.value.memo = responseErrors.memo || null;
};

// アイテムの編集機能
const itemUpdate = async () => {
    error.value = {
        file: null,
        main_category: '',
        sub_category: '',
        color: '',
        season: '',
        memo: null,
    };

    const formData = new FormData();
    if (editForm.value.file instanceof File) {
        formData.append('file', editForm.value.file);
    }
    Object.entries(editForm.value).forEach(([key, value]) => {
        if (key !== 'file') formData.append(key, value ?? '');
    });

    try {
        const response = await axios.post(
            `/api/items/${editForm.value.id}`,
            formData,
            {
                forceFormData: true,
                preserveScroll: true,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );
        if (response.status === 200) {
            emit('closeOverlay');
            window.dispatchEvent(new Event('item-updated'));
        }
    } catch (errors) {
        console.error('エラーが発生しました:', errors);
        if (errors.response) {
            setErrors(errors.response.data.errors);
        }
    }
};

const selectNewImage = () => {
    const fileInput = document.getElementById('file');
    if (fileInput) {
        fileInput.click();
    }
};

// ファイルアップロード
const getUploadedImage = (e) => {
    editForm.value.file = e.target.files[0];
    const validTypes = ['image/png', 'image/jpeg', 'image/webp'];
    if (!validTypes.includes(editForm.value.file.type)) {
        isValidFile.value = false;
        return;
    }

    fileDisplay.value = URL.createObjectURL(e.target.files[0]);
    const img = new Image();
    img.onload = () => {
        document
            .getElementById('TextAreaSection')
            .scrollIntoView({ behavior: 'smooth' });
    };
    img.src = fileDisplay.value;
};

// メインカテゴリーなどの情報取得
const fetchEnums = async () => {
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
    if (selectedColor.value?.id === color?.value) {
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

// 内容が変更されなかった時にeditFormを初期化
watch(
    () => props.editItem,
    (newEditItem) => {
        editForm.value = { ...newEditItem };
    },
    { immediate: true } // 初回マウント時にも実行
);

watch(
    () => editForm.value.sub_category,
    (newValue) => {
        if (newValue === '') {
            editForm.value.sub_category = null;
        }
    }
);

watch(
    () => editForm.value.season,
    (newValue) => {
        if (newValue === '') {
            editForm.value.season = null;
        }
    }
);

onMounted(async () => {
    await fetchEnums();
    if (props.editItem.color) {
        editForm.value.color = props.editItem.color;
        selectedColor.value = colors.value.find(
            (c) => c.id === props.editItem.color
        );
    }
});

watch(editForm, (newValue) => {
    if (newValue.main_category !== editForm.value.main_category) {
        axios
            .get('/api/enums')
            .then((response) => {
                subCategories.value = response.data.subCategories;
            })
            .catch((error) => {
                console.error('Enum データの取得に失敗しました', error);
            });
    }
    if (newValue.sub_category !== editForm.value.sub_category) {
        axios
            .get('/api/enums')
            .then((response) => {
                seasons.value = response.data.seasons;
            })
            .catch((error) => {
                console.error('Enum データの取得に失敗しました', error);
            });
    }
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
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
                    @click="itemUpdate()"
                >
                    更新
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <!-- アイテム画像編集部分 -->
                <div
                    class="flex items-center bg-gray-100 w-full md:w-1/2 h-full md:h-auto overflow-hidden"
                    @click="selectNewImage"
                    style="position: relative; text-align: center"
                >
                    <div
                        v-if="!fileDisplay"
                        class="flex flex-col items-center mx-auto"
                        style="position: absolute; top: 3%; left: 6%"
                    >
                        <input
                            id="file"
                            class="hidden"
                            type="file"
                            @change="getUploadedImage($event)"
                        />
                        <div
                            v-if="error && error.file"
                            class="text-red-500 text-center p-2 font-extrabold"
                            style="display: inline-block"
                        >
                            {{ error.file[0] }}
                        </div>
                        <div
                            v-if="!fileDisplay && isValidFile === false"
                            class="text-red-500 text-center p-2 font-extrabold"
                            style="display: inline-block"
                        >
                            ファイルが受け付けられませんでした。
                        </div>
                    </div>
                    <img
                        class="h-full w-full object-contain mx-auto p-4"
                        :src="fileDisplay || editForm.file"
                    />
                </div>

                <div
                    id="TextAreaSection"
                    class="md:w-1/2 w-full p-4 overflow-y-auto"
                >
                    <!-- メインカテゴリー表示 -->
                    <div
                        v-if="error && error.main_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.main_category[0] }}
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
                        v-if="error && error.sub_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.sub_category[0] }}
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
                        v-if="error && error.color"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.color[0] }}
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
                        v-if="error && error.memo"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.memo }}
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
