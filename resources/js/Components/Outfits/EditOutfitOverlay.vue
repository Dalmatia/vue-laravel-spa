<script setup>
import { defineEmits, defineProps, ref, onMounted, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

import SelectItemsOverlay from './SelectItemsOverlay.vue';

const authUser = useAuthStore().user.name;
const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editOutfit: Object, required: true });
const editForm = ref({ ...props.editOutfit });
const tops = ref({});
const outer = ref({});
const bottoms = ref({});
const shoes = ref({});
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
let isOpen = ref(true);

// 投稿時に選択したアイテムの表示
const fetchItem = async () => {
    try {
        const fetchItemData = async (itemId, item) => {
            const response = await axios.get(`/api/items/${itemId}`);
            const itemData = response.data;

            item.value = itemData;
        };

        const fetchPromises = [];

        if (editForm.value.tops) {
            fetchPromises.push(fetchItemData(editForm.value.tops, tops));
        }

        if (editForm.value.outer) {
            fetchPromises.push(fetchItemData(editForm.value.outer, outer));
        }

        if (editForm.value.bottoms) {
            fetchPromises.push(fetchItemData(editForm.value.bottoms, bottoms));
        }

        if (editForm.value.shoes) {
            fetchPromises.push(fetchItemData(editForm.value.shoes, shoes));
        }

        await Promise.all(fetchPromises);
    } catch (error) {
        console.error('データの取得に失敗しました:', error);
    }
};

// コーディネートの編集機能
const outfitUpdate = async () => {
    Object.keys(error.value).forEach((key) => (error.value[key] = null));
    editForm.season = editForm.season === '' ? null : editForm.season;

    const formData = new FormData();
    if (editForm.value.file) {
        formData.append('file', editForm.value.file);
    }
    formData.append('description', editForm.value.description);
    formData.append('outfit_date', editForm.value.outfit_date);
    formData.append('season', editForm.value.season);
    formData.append('tops', editForm.value.tops);
    formData.append('outer', editForm.value.outer);
    formData.append('bottoms', editForm.value.bottoms);
    formData.append('shoes', editForm.value.shoes);

    try {
        const response = await axios.post(
            `/api/outfit/${editForm.value.id}`,
            formData,
            console.log(formData),
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
            window.dispatchEvent(new Event('outfit-updated'));
        }
    } catch (errors) {
        if (errors.response && errors.response.data.errors) {
            Object.assign(error.value, errors.response.data.errors);
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
    const extension = editForm.value.file.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得

    isValidFile.value = ['png', 'jpg', 'jpeg'].includes(extension);
    if (!isValidFile.value) return;

    fileDisplay.value = URL.createObjectURL(editForm.value.file);
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
        editForm.value[itemType.key] = selectedItem ? selectedItem.id : null;
        editForm.value[itemType.imgKey] = selectedItem
            ? selectedItem.file
            : null;
    }
    // モーダルを閉じる
    showItemSelectionModal.value = false;
};

const toggleAccordion = () => {
    isOpen.value = !isOpen.value;
};

// 内容が変更されなかった時にeditFormを初期化
watch(
    () => props.editOutfit,
    (newEditOutfit) => {
        editForm.value = { ...newEditOutfit };
        fetchItem(); // アイテムの再取得
    },
    { immediate: true } // 初回マウント時にも実行
);

onMounted(async () => {
    await Promise.all([fetchItem(), getSeason()]);
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
                <div class="text-lg font-extrabold">コーディネート編集</div>
                <button
                    class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
                    @click="outfitUpdate()"
                >
                    更新
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <div
                    class="flex items-center bg-gray-100 w-full h-full overflow-hidden relative text-center"
                    @click="selectNewImage()"
                >
                    <div
                        v-if="!fileDisplay"
                        class="flex flex-col items-center mx-auto absolute top-[3%] left-[6%]"
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
                        class="h-full w-full object-contain p-4 max-h-[75vh]"
                        :src="fileDisplay || editForm.file"
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
                            v-model="editForm.description"
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
                            v-model="editForm.outfit_date"
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
                            v-model="editForm.season"
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
                        <!-- <ChevronDown :size="27" /> -->
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
                                <button
                                    v-if="editForm.outer === null"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    アウター
                                </button>
                                <img
                                    v-else-if="editForm.outerImage"
                                    class="w-full h-full object-contain"
                                    :src="editForm.outerImage"
                                />
                                <img
                                    v-else-if="outer.file"
                                    class="w-full h-full object-contain"
                                    :src="outer.file"
                                />
                            </div>

                            <!-- トップス選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(2)"
                            >
                                <div
                                    v-if="editForm.tops === null"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    トップス
                                </div>
                                <img
                                    v-else-if="editForm.topsImage"
                                    class="w-full h-full object-contain"
                                    :src="editForm.topsImage"
                                />
                                <img
                                    v-else-if="tops.file"
                                    class="w-full h-full object-contain"
                                    :src="tops.file"
                                />
                            </div>

                            <!-- ボトムス選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(3)"
                            >
                                <div
                                    v-if="editForm.bottoms === null"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    ボトムス
                                </div>
                                <img
                                    v-else-if="editForm.bottomsImage"
                                    class="w-full h-full object-contain"
                                    :src="editForm.bottomsImage"
                                />
                                <img
                                    v-else-if="bottoms.file"
                                    class="w-full h-full object-contain"
                                    :src="bottoms.file"
                                />
                            </div>

                            <!-- シューズ選択 -->
                            <div
                                class="container border border-gray-300 h-48 lg:h-56 w-full p-2"
                                @click="openModal(4)"
                            >
                                <div
                                    v-if="editForm.shoes === null"
                                    class="text-sm text-blue-500 hover:text-gray-900 font-extrabold"
                                >
                                    シューズ
                                </div>
                                <img
                                    v-else-if="editForm.shoesImage"
                                    class="w-full h-full object-contain"
                                    :src="editForm.shoesImage"
                                />
                                <img
                                    v-else-if="shoes.file"
                                    class="w-full h-full object-contain"
                                    :src="shoes.file"
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
