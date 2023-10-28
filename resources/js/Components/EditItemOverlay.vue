<script setup>
import { defineEmits, defineProps, ref, onMounted, watch } from 'vue';
import axios from 'axios';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editItem: Object, required: true });
const editItem = props.editItem;

const mainCategories = ref([]);
const subCategories = ref([]);
const colors = ref([]);
const seasons = ref([]);

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

const localEditItem = ref(editItem);

// アイテムの編集機能
const itemEdit = async () => {
    error.value.file = null;
    error.value.main_category = '';
    error.value.sub_category = '';
    error.value.color = '';
    error.value.season = '';
    error.value.memo = null;

    try {
        const formData = new FormData();
        formData.append('file', localEditItem.value.file);
        formData.append('main_category', localEditItem.value.main_category);
        formData.append('sub_category', localEditItem.value.sub_category);
        formData.append('color', localEditItem.value.color);
        formData.append('season', localEditItem.value.season);
        formData.append('memo', localEditItem.value.memo);

        const response = await axios.post(
            `/api/items/${localEditItem.value.id}`,
            formData,
            {
                forceFormData: true,
                preserveScroll: true,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );
        console.log(response);
        if (response.status === 200) {
            emit('closeOverlay');
        }
    } catch (errors) {
        console.error('エラーが発生しました:', errors);

        if (errors.response) {
            const responseErrors = errors.response.data.errors;

            if (responseErrors) {
                error.value.file = responseErrors.file;
                error.value.main_category = responseErrors.main_category;
                error.value.sub_category = responseErrors.sub_category;
                error.value.color = responseErrors.color;
                error.value.season = responseErrors.season;
                error.value.memo = responseErrors.memo;
            }
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
    localEditItem.value.file = e.target.files[0];
    let extention = localEditItem.value.file.name.substring(
        localEditItem.value.file.name.lastIndexOf('.') + 1
    );

    console.log(extention);
    if (extention == 'png' || extention == 'jpg' || extention == 'jpeg') {
        isValidFile.value = true;
    } else {
        isValidFile.value = false;
        return;
    }

    fileDisplay.value = URL.createObjectURL(e.target.files[0]);
    setTimeout(() => {
        document
            .getElementById('TextAreaSection')
            .scrollIntoView({ behavior: 'smooth' });
    }, 300);
};

// メインカテゴリーなどの情報取得
onMounted(async () => {
    try {
        const response = await axios.get('/api/enums');
        mainCategories.value = response.data.mainCategories;
        subCategories.value = response.data.subCategories;
        colors.value = response.data.colors;
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
});

watch(localEditItem, (newValue) => {
    if (newValue.main_category !== localEditItem.main_category) {
        axios
            .get('/api/enums')
            .then((response) => {
                subCategories.value = response.data.subCategories;
            })
            .catch((error) => {
                console.error('Enum データの取得に失敗しました', error);
            });
    }
    if (newValue.sub_category !== localEditItem.sub_category) {
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
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl"
        >
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
                    @click="itemEdit()"
                >
                    更新
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <div
                    class="flex items-center bg-gray-100 w-full h-full overflow-hidden"
                    @click="selectNewImage"
                >
                    <div
                        v-if="!fileDisplay"
                        class="flex flex-col items-center mx-auto"
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
                        >
                            {{ error.file[0] }}
                        </div>
                        <div
                            v-if="!fileDisplay && isValidFile === false"
                            class="text-red-500 text-center p-2 font-extrabold"
                        >
                            ファイルが受け付けられませんでした。
                        </div>
                    </div>
                    <img
                        class="h-full min-w-[200px] p-4 mx-auto"
                        :src="fileDisplay || editItem.file"
                    />
                </div>

                <!-- メインカテゴリー表示 -->
                <div id="TextAreaSection" class="max-w-[720px] w-full relative">
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
                        <select v-model="localEditItem.main_category">
                            <option value="" selected>選択してください</option>
                            <option
                                v-for="(label, value) in mainCategories"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
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
                        <select v-model="localEditItem.sub_category">
                            <option value="" disabled>選択してください</option>
                            <option
                                v-for="(label, value) in subCategories"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
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
                        <select v-model="localEditItem.color">
                            <option value="" disabled>選択してください</option>
                            <option
                                v-for="(label, value) in colors"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- 季節表示 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <select v-model="localEditItem.season">
                            <option value="" disabled>選択してください</option>
                            <option
                                v-for="(label, value) in seasons"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="error && error.memo"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.memo }}
                    </div>
                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            ref="textarea"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 text-gray-600 text-[18px] outline-none resize-none"
                            v-model="localEditItem.memo"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
