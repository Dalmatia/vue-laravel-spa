<script setup>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import OutfitItemsAccordion from '@/Components/Outfit/Form/OutfitItemsAccordion.vue';

const props = defineProps({
    user: Object,
    form: Object,
    error: Object,
    seasons: Array,
    itemTypeEntries: Array,
    getItemByRole: Function,
    isOpen: Boolean,
    showItemSelectionModal: Boolean,
    selectedItemType: Number,
});

const emit = defineEmits([
    'toggleAccordion',
    'openModal',
    'itemSelected',
    'closeItemModal',
]);
</script>

<template>
    <div id="TextAreaSection" class="max-w-[720px] w-full overflow-y-auto">
        <!-- ユーザ名 -->
        <div class="flex items-center p-3">
            <img class="rounded-full w-[38px] h-[38px]" :src="user.file" />

            <div class="ml-4 font-extrabold text-[15px]">
                {{ user.name }}
            </div>
        </div>

        <!-- メモ -->
        <div v-if="error?.description" class="text-red-500 p-2 font-extrabold">
            {{ error.description[0] }}
        </div>

        <textarea
            ref="textarea"
            id="textarea"
            v-model="form.description"
            placeholder="何か書く(コーディネートのポイント等)"
            rows="10"
            class="w-full border-0 mt-2 mb-2 focus:ring-0 text-gray-600 text-[18px] outline-none"
        />

        <!-- 着用日 -->
        <div
            v-if="error?.outfit_date"
            class="text-red-500 text-center p-2 font-extrabold"
        >
            {{ error.outfit_date[0] }}
        </div>
        <div class="flex items-center justify-between border-b p-3">
            <div class="text-lg font-extrabold text-gray-500">着用日</div>
            <VueDatePicker
                v-model="form.outfit_date"
                uid="outfit_date"
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

        <!-- シーズン -->
        <div
            v-if="error?.season"
            class="text-red-500 text-center p-2 font-extrabold"
        >
            {{ error.season[0] }}
        </div>
        <div class="flex items-center justify-between border-b p-3">
            <div class="text-lg font-extrabold text-gray-500">シーズン</div>
            <select
                v-model="form.season"
                id="season"
                class="text-lg text-right font-extrabold text-gray-500 outline-none"
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

        <!-- 着用アイテム選択 -->
        <OutfitItemsAccordion
            :itemTypeEntries="itemTypeEntries"
            :getItemByRole="getItemByRole"
            :isOpen="isOpen"
            :showItemSelectionModal="showItemSelectionModal"
            :selectedItemType="selectedItemType"
            @toggleAccordion="emit('toggleAccordion')"
            @openModal="emit('openModal', $event)"
            @itemSelected="emit('itemSelected', $event)"
            @closeItemModal="emit('closeItemModal')"
        />
    </div>
</template>
