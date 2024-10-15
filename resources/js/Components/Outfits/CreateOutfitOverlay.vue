<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import axios from 'axios';
import SelectItemsOverlay from './SelectItemsOverlay.vue';

const authUser = useAuthStore().user.name;
const emit = defineEmits(['close']);
const form = reactive({
    file: null,
    description: null,
    outfit_date: '',
    season: null,
    tops: null,
    outer: null,
    bottoms: null,
    shoes: null,
});
const seasons = ref([]);
// コーディネートに使用したアイテムのカテゴリー
const itemTypes = {
    1: { key: 'outer', imgKey: 'outerImage' },
    2: { key: 'tops', imgKey: 'topsImage' },
    3: { key: 'bottoms', imgKey: 'bottomsImage' },
    4: { key: 'shoes', imgKey: 'shoesImage' },
};

let isValidFile = ref(null);
let fileDisplay = ref('');
let textarea = ref('');
let error = ref({
    file: null,
    description: null,
    outfit_date: '',
    season: '',
    tops: '',
    outer: '',
    bottoms: '',
    shoes: '',
});
let showItemSelectionModal = ref(false);
let selectedItemType = ref(null);
let isOpen = ref(false);

const createOutfit = async () => {
    Object.keys(error.value).forEach((key) => (error.value[key] = null));
    form.season = form.season === '' ? null : form.season;
    try {
        const response = await axios.post('/api/outfit', form, {
            forceFormData: true,
            preserveScroll: true,
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.status === 200) {
            closeOverlay();
            window.dispatchEvent(new Event('outfit-created'));
        }
    } catch (errors) {
        if (errors.response && errors.response.data.errors) {
            Object.assign(error.value, errors.response.data.errors);
        }
    }
};

const getUploadedImage = (e) => {
    form.file = e.target.files[0];
    const extension = form.file.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得

    isValidFile.value = ['png', 'jpg', 'jpeg'].includes(extension);
    if (!isValidFile.value) return;

    fileDisplay.value = URL.createObjectURL(form.file);
    setTimeout(() => {
        document
            .getElementById('TextAreaSection')
            .scrollIntoView({ behavior: 'smooth' });
    }, 300);
};

const getSeason = async () => {
    try {
        const response = await axios.get('/api/enums');
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

const openModal = (itemType) => {
    // コーディネートに使用したアイテムのカテゴリーを設定
    selectedItemType.value = itemType;
    // アイテム選択モーダルを表示
    showItemSelectionModal.value = true;
};

// コーディネートに使用したアイテムを選択する
const handleItemSelected = (selectedItem) => {
    const itemType = itemTypes[selectedItemType.value];
    if (itemType) {
        form[itemType.key] = selectedItem ? selectedItem.id : null;
        form[itemType.imgKey] = selectedItem ? selectedItem.file : null;
    }
    // モーダルを閉じる
    showItemSelectionModal.value = false;
};

const closeOverlay = () => {
    form.file = null;
    form.description = null;
    form.outfit_date = '';
    form.season = '';
    form.tops = null;
    form.outer = null;
    form.bottoms = null;
    form.shoes = null;
    fileDisplay.value = '';
    emit('close');
};

const toggleAccordion = () => {
    isOpen.value = !isOpen.value;
};

onMounted(() => {
    getSeason();
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
                <div class="text-lg font-extrabold">新規投稿</div>
                <button
                    class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
                    @click="createOutfit()"
                >
                    投稿
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
                        class="h-full w-full object-contain p-4 max-h-[75vh]"
                        :src="fileDisplay"
                    />
                </div>

                <div
                    id="TextAreaSection"
                    class="max-w-[720px] w-full relative overflow-y-auto"
                >
                    <div class="flex items-center justify-between p-3">
                        <div class="flex items-center">
                            <img
                                class="rounded-full w-[38px] h-[38px]"
                                src="https://picsum.photos/id/50/300/320"
                            />
                            <div class="ml-4 font-extrabold text-[15px]">
                                {{ authUser }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="error && error.description"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.description[0] }}
                    </div>
                    <div class="flex w-full max-h-[150px] bg-white border-b">
                        <textarea
                            ref="textarea"
                            v-model="form.description"
                            placeholder="何か書く(コーディネートのポイント等)"
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 focus:ring-0 text-gray-600 text-[18px] outline-none"
                        ></textarea>
                    </div>

                    <div
                        v-if="error && error.outfit_date"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.outfit_date[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            着用日
                        </div>
                        <VueDatePicker
                            v-model="form.outfit_date"
                            teleport-center
                            locale="ja"
                            format="yyyy-MM-dd"
                            model-type="yyyy-MM-dd"
                            week-start="0"
                            :enable-time-picker="false"
                            auto-apply
                            style="width: auto"
                        />
                    </div>

                    <!-- 季節選択 -->
                    <div
                        v-if="error && error.season"
                        class="text-red-500 text-center p-2 font-extrabold"
                    >
                        {{ error.season[0] }}
                    </div>
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            シーズン
                        </div>
                        <select
                            class="text-lg text-right font-extrabold text-gray-500 outline-none"
                            v-model="form.season"
                        >
                            <option :value="null">選択してください</option>
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
                        class="flex items-center justify-between border-b p-3 cursor-pointer"
                        @click="toggleAccordion()"
                    >
                        <div class="text-lg font-extrabold text-gray-500">
                            着用アイテム
                        </div>
                        <button class="flex items-center space-x-3">
                            <svg
                                class="w-3 transition-all duration-200 transform"
                                :class="{
                                    'rotate-180': isOpen,
                                    'rotate-0': !isOpen,
                                }"
                                fill="none"
                                stroke="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 16 10"
                            >
                                <path
                                    d="M15 1.2l-7 7-7-7"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                    <div
                        class="transition-all duration-400 overflow-y-auto"
                        :class="{
                            'max-h-full': isOpen,
                            'max-h-0': !isOpen,
                        }"
                    >
                        <div class="min-h-fit p-2 grid grid-cols-2 gap-4">
                            <!-- アウター選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(1)"
                            >
                                <div
                                    v-if="!form.outer"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    アウター
                                </div>
                                <img
                                    v-if="form.outer"
                                    class="w-full h-full object-contain"
                                    :src="form.outerImage"
                                />
                            </div>

                            <!-- トップス選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(2)"
                            >
                                <div
                                    v-if="!form.tops"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    トップス
                                </div>
                                <img
                                    v-if="form.tops"
                                    class="w-full h-full object-contain"
                                    :src="form.topsImage"
                                />
                            </div>

                            <!-- ボトムス選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(3)"
                            >
                                <div
                                    v-if="!form.bottoms"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    ボトムス
                                </div>
                                <img
                                    v-if="form.bottoms"
                                    class="w-full h-full object-contain"
                                    :src="form.bottomsImage"
                                />
                            </div>

                            <!-- シューズ選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(4)"
                            >
                                <div
                                    v-if="!form.shoes"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    シューズ
                                </div>
                                <img
                                    v-if="form.shoes"
                                    class="w-full h-full object-contain"
                                    :src="form.shoesImage"
                                />
                            </div>
                        </div>
                    </div>

                    <SelectItemsOverlay
                        v-if="showItemSelectionModal"
                        :itemType="selectedItemType"
                        @onItemSelected="handleItemSelected"
                        @close="showItemSelectionModal = false"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
