<script setup>
import { ref, reactive, onMounted } from 'vue';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Calendar from 'vue-material-design-icons/Calendar.vue';
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';

// const user = usePage().props.auth.user;

const emit = defineEmits(['close']);

const form = reactive({
    outfit: null,
    description: null,
    outfit_date: '',
    season: '',
    tops: '',
    outer: '',
    bottoms: '',
    shoes: '',
});

const seasons = ref([]);

let isValidFile = ref(null);
let fileDisplay = ref('');
let textarea = ref('');
let error = ref({
    outfit: null,
    description: null,
    outfit_date: '',
    season: '',
    tops: '',
    outer: '',
    bottoms: '',
    shoes: '',
});

const createOutfit = () => {
    error.value.outfit = null;
    error.value.description = null;
    error.value.season = null;
    error.value.tops = null;
    error.value.outer = null;
    error.value.bottoms = null;
    error.value.shoes = null;

    axios.post('/api/outfit', form, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            errors && errors.outfit ? (error.value.outfit = errors.outfit) : '';
            errors && errors.description
                ? (error.value.description = errors.description)
                : '';
            errors && errors.season ? (error.value.season = errors.season) : '';
            errors && errors.tops ? (error.value.tops = errors.tops) : '';
            errors && errors.outer ? (error.value.outer = errors.outer) : '';
            errors && errors.bottoms
                ? (error.value.bottoms = errors.bottoms)
                : '';
            errors && errors.shoes ? (error.value.shoes = errors.shoes) : '';
        },
        onSuccess: () => {
            closeOverlay();
        },
    });
};

const getUploadedImage = (e) => {
    form.outfit = e.target.files[0];
    let extention = form.outfit.name.substring(
        form.outfit.name.lastIndexOf('.') + 1
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

const getSeason = async () => {
    try {
        const response = await axios.get('/api/enums');
        seasons.value = response.data.seasons;
    } catch (error) {
        console.error('Enum データの取得に失敗しました', error);
    }
};

const closeOverlay = () => {
    form.outfit = null;
    form.description = null;
    form.outfit_date = '';
    form.season = '';
    form.tops = '';
    form.outer = '';
    form.bottoms = '';
    form.shoes = '';
    fileDisplay.value = '';
    emit('close');
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
                            v-if="error && error.outfit"
                            class="text-red-500 text-center p-2 font-extrabold"
                        >
                            {{ error.outfit[0] }}
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
                        class="h-full min-w-[200px] p-4 mx-auto"
                        :src="fileDisplay"
                    />
                </div>

                <div id="TextAreaSection" class="max-w-[720px] w-full relative">
                    <div class="flex items-center justify-between p-3">
                        <div class="flex items-center">
                            <img
                                class="rounded-full w-[38px] h-[38px]"
                                src="https://picsum.photos/id/50/300/320"
                            />
                            <div class="ml-4 font-extrabold text-[15px]">
                                名無しさん
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="error && error.description"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.description }}
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

                    <!-- 以下の部分不要 -->
                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            着用日
                        </div>
                        <Calendar :size="27" />
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

                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">
                            着用アイテム
                        </div>
                        <ChevronDown :size="27" />
                    </div>

                    <!-- <div class="text-gray-500 mt-3 p-3 text-sm">
                        Your reel will be shared with your followers in their
                        feeds and can be seen on your profile. It may also
                        appear in places such as Reels, where anyone can see it.
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>
