<script setup>
import { defineEmits, defineProps } from 'vue';
import { useAuthStore } from '../../../stores/auth';
import { useEditOutfitForm } from '../../../src/composables/outfit/useEditOutfitForm';
import { useModal } from '../../../src/composables/useModal';
import VueDatePicker from '@vuepic/vue-datepicker';

import Close from 'vue-material-design-icons/Close.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';

import ItemSelectionSection from '../ItemSelectionSection.vue';
import SelectItemsOverlay from '../SelectItemsOverlay.vue';
import FileUpdatePreview from '../../FileUpdatePreview.vue';

const emit = defineEmits(['closeOverlay']);
const props = defineProps({ editOutfit: Object, required: true });
const user = useAuthStore().user;

const {
    editForm,
    itemRefs,
    seasons,
    error,
    itemTypeArray,
    fileDisplay,
    isValidFile,
    getUploadedImage,
    outfitUpdate,
    handleItemSelected,
} = useEditOutfitForm(props, emit);

const {
    isOpen,
    showItemSelectionModal,
    selectedType: selectedItemType,
    openModal,
    toggleAccordion,
} = useModal();
isOpen.value = true;

const selectNewImage = () => {
    const fileInput = document.getElementById('file');
    if (fileInput) {
        fileInput.click();
    }
};
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
                <!-- コーディネート画像編集部分 -->
                <FileUpdatePreview
                    :file-display="fileDisplay"
                    :form-errors="error"
                    :is-valid-file="isValidFile"
                    :initial-image="editForm.file"
                    @file-selected="getUploadedImage($event)"
                />

                <div
                    id="TextAreaSection"
                    class="md:w-1/2 w-full overflow-y-auto"
                >
                    <div class="flex items-center justify-between p-3">
                        <div class="flex items-center">
                            <img
                                class="rounded-full w-[38px] h-[38px]"
                                :src="user.file"
                            />
                            <div class="ml-4 font-extrabold text-[15px]">
                                {{ user.name }}
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
                                v-for="season in seasons"
                                :key="season.id"
                                :value="season.id"
                            >
                                {{ season.name }}
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
                            <ItemSelectionSection
                                v-for="section in itemTypeArray"
                                :key="section.key"
                                :item="editForm[section.key]"
                                :image="
                                    editForm[section.imgKey] ||
                                    itemRefs[section.key]?.file
                                "
                                :label="section.label"
                                :onClick="() => openModal(section.type)"
                            />
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
