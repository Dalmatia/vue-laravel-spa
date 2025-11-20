<script setup>
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

const props = defineProps({
    filters: Object,
    mainCategories: Array,
    subCategories: Array,
    seasons: Array,
    openFilter: Boolean,
    isMobile: Boolean,
    selectColor: Function,
    getColorClass: Function,
    getColorStyle: Function,
});
const emit = defineEmits(['clearFilters', 'filterByCategory']);
</script>

<template>
    <div
        v-if="openFilter"
        :class="[
            isMobile
                ? 'fixed top-14 pt-1 z-40 overflow-hidden'
                : 'absolute top-20',
            'left-0 w-full bg-white',
        ]"
    >
        <form class="w-full p-4">
            <!-- メインカテゴリー -->
            <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
                <label
                    for="mainCategory"
                    :class="
                        isMobile
                            ? 'block mb-1 text-base'
                            : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
                    "
                >
                    メインカテゴリー
                </label>
                <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
                    <select
                        id="mainCategory"
                        v-model="filters.mainCategory"
                        :class="
                            isMobile
                                ? 'w-full border-b pb-2 text-right'
                                : 'w-full'
                        "
                    >
                        <option value="">
                            {{
                                isMobile ? '指定なし' : 'メインカテゴリーを選択'
                            }}
                        </option>
                        <option
                            v-for="mainCategory in mainCategories"
                            :key="mainCategory.id"
                            :value="mainCategory.id"
                        >
                            {{ mainCategory.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- サブカテゴリー -->
            <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
                <label
                    for="subCategory"
                    :class="
                        isMobile
                            ? 'block mb-1 text-base'
                            : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
                    "
                >
                    サブカテゴリー
                </label>
                <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
                    <select
                        id="subCategory"
                        v-model="filters.subCategory"
                        :class="
                            isMobile
                                ? 'w-full border-b pb-2 text-right'
                                : 'w-full'
                        "
                    >
                        <option value="">
                            {{ isMobile ? '指定なし' : 'サブカテゴリーを選択' }}
                        </option>
                        <option
                            v-for="subCategory in subCategories"
                            :key="subCategory.id"
                            :value="subCategory.id"
                        >
                            {{ subCategory.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- カラー -->
            <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
                <label
                    for="color"
                    :class="
                        isMobile
                            ? 'block mb-1 text-base'
                            : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
                    "
                >
                    カラー
                </label>
                <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
                    <button
                        id="color"
                        type="button"
                        :class="[
                            isMobile
                                ? 'w-full border-b pb-2 text-right'
                                : 'w-full',
                        ]"
                        @click="selectColor(filters.color)"
                    >
                        <div
                            v-if="filters.color"
                            :class="[
                                'flex items-center gap-2',
                                isMobile ? 'justify-end' : '',
                            ]"
                        >
                            <span>選択中のカラー:</span>
                            <div
                                class="w-5 h-5 rounded-full border"
                                :style="getColorStyle(filters.color)"
                                :class="getColorClass(filters.color)"
                            ></div>
                            <span class="text-sm">
                                {{ filters.color.name }}
                            </span>
                            <ChevronRight />
                        </div>
                        <div
                            v-else
                            :class="[
                                'flex items-center gap-2',
                                isMobile ? 'justify-end' : '',
                            ]"
                        >
                            カラーを選択
                            <ChevronRight />
                        </div>
                    </button>
                </div>
            </div>

            <!-- シーズン -->
            <div :class="isMobile ? 'mb-4' : 'flex border-y border-[#f2f2f2]'">
                <label
                    for="season"
                    :class="
                        isMobile
                            ? 'block mb-1 text-base'
                            : 'w-[300px] bg-[#f2f2f2] px-[30px] py-5 font-bold text-right border-b-white'
                    "
                >
                    シーズン
                </label>
                <div :class="isMobile ? '' : 'flex-1 py-5 pl-10 pr-5'">
                    <select
                        id="season"
                        v-model="filters.season"
                        :class="
                            isMobile
                                ? 'w-full border-b pb-2 text-right'
                                : 'w-full'
                        "
                    >
                        <option value="">
                            {{ isMobile ? '指定なし' : 'シーズンを選択' }}
                        </option>
                        <option
                            v-for="season in seasons"
                            :key="season.id"
                            :value="season.id"
                        >
                            {{ season.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- 共通のアクション -->
            <div
                :class="[
                    isMobile
                        ? 'flex flex-col items-center space-y-3 px-5 mt-6'
                        : 'flex flex-row justify-center items-center gap-x-4 mt-6 max-w-[750px] mx-auto mb-7',
                ]"
            >
                <button
                    type="button"
                    :class="[
                        'text-[#999] border border-[#ccc] rounded-sm text-sm font-bold h-[45px] bg-white',
                        isMobile ? 'w-full' : 'w-[210px]',
                    ]"
                    @click="emit('clearFilters')"
                >
                    指定した条件をクリア
                </button>
                <button
                    type="button"
                    :class="[
                        'bg-black text-white font-bold rounded-sm text-sm h-[45px]',
                        isMobile ? 'w-full' : 'w-[210px]',
                    ]"
                    @click.prevent="emit('filterByCategory', filters)"
                >
                    この条件で絞り込む
                </button>
            </div>
        </form>
    </div>
</template>
