<script setup>
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue';

const props = defineProps({
    isMobile: Boolean,
    color: Object,
    getColorClass: Function,
    getColorStyle: Function,
});

const emit = defineEmits(['open-color-modal']);
</script>

<template>
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
                    isMobile ? 'w-full border-b pb-2 text-right' : 'w-full',
                ]"
                @click="emit('open-color-modal')"
            >
                <div
                    v-if="color"
                    :class="[
                        'flex items-center gap-2',
                        isMobile ? 'justify-end' : '',
                    ]"
                >
                    <span>選択中:</span>
                    <div
                        class="w-5 h-5 rounded-full border"
                        :style="getColorStyle(color)"
                        :class="getColorClass(color)"
                    />
                    <span>{{ color.name }}</span>
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
</template>
