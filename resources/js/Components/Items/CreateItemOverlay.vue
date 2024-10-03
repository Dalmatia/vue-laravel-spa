<script setup>
import { ref, reactive, onMounted, watch } from 'vue';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';

// const user = usePage().props.auth.user;

const emit = defineEmits(['close']);

const form = reactive({
    file: null,
    main_category: '',
    sub_category: '',
    color: '',
    season: '',
    memo: null,
});

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

// クローゼットアイテム登録
const createItemFunc = async () => {
    error.value.file = null;
    error.value.main_category = '';
    error.value.sub_category = '';
    error.value.color = '';
    error.value.season = '';
    error.value.memo = null;

    try {
        const response = await axios.post('/api/items', form, {
            forceFormData: true,
            preserveScroll: true,
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.status === 200) {
            closeOverlay();
            window.dispatchEvent(new Event('item-created'));
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

// ファイルアップロード
const getUploadedImage = (e) => {
    form.file = e.target.files[0];
    let extention = form.file.name.substring(
        form.file.name.lastIndexOf('.') + 1
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

const closeOverlay = () => {
    form.file = null;
    form.main_category = '';
    form.sub_category = '';
    form.color = '';
    form.season = '';
    form.memo = null;
    fileDisplay.value = '';
    emit('close');
};

// 各選択項目の値取得
onMounted(() => {
    getEnums();
});

watch(form, (newValue) => {
    if (newValue.main_category !== form.main_category) {
        axios
            .get('/api/enums')
            .then((response) => {
                subCategories.value = response.data.subCategories;
            })
            .catch((error) => {
                console.error('Enum データの取得に失敗しました', error);
            });
    }
    if (newValue.sub_category !== form.sub_category) {
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
        <button class="absolute right-3 cursor-pointer" @click="closeOverlay()">
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
                    @click="closeOverlay()"
                />
                <div class="text-lg font-extrabold">アイテム登録</div>
                <button
                    @click="createItemFunc()"
                    class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
                >
                    登録
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <div
                    class="flex items-center bg-gray-100 w-full h-full overflow-hidden"
                >
                    <div
                        v-if="!fileDisplay"
                        class="flex flex-col items-center mx-auto"
                    >
                        <label
                            for="file"
                            class="hover:bg-blue-700 bg-blue-500 rounded-lg p-2.5 text-white font-extrabold cursor-pointer"
                        >
                            写真を選択する
                        </label>
                        <input
                            id="file"
                            class="hidden"
                            type="file"
                            @input="getUploadedImage($event)"
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
                        v-if="fileDisplay && isValidFile === true"
                        class="h-full w-full object-contain mx-auto p-4"
                        :src="fileDisplay"
                    />
                </div>

                <!-- メインカテゴリー選択 -->
                <div id="TextAreaSection" class="max-w-[720px] w-full relative">
                    <div
                        v-if="error && error.main_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.main_category[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            カテゴリー選択
                        </div>
                        <select v-model="form.main_category">
                            <option value="" disabled>選択してください</option>
                            <option
                                v-for="(label, value) in mainCategories"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- サブカテゴリー選択 -->
                    <div
                        v-if="error && error.sub_category"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.sub_category[0] }}
                    </div>
                    <div
                        v-if="form.main_category"
                        class="flex items-center justify-between border-b p-3"
                    >
                        <div class="text-lg font-extrabold text-gray-500">
                            サブカテゴリー
                        </div>
                        <select v-model="form.sub_category">
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
                        <select v-model="form.color">
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

                    <!-- 季節選択 -->
                    <div
                        v-if="error && error.season"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.season[0] }}
                    </div>
                    <div
                        v-if="form.sub_category"
                        class="flex items-center justify-between border-b p-3"
                    >
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <select v-model="form.season">
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
                            v-model="form.memo"
                            placeholder="アイテムの特徴(ポイントなど)"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 focus:ring-0 text-gray-600 text-[18px] outline-none"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
