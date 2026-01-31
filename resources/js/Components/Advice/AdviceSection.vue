<script setup>
import { defineProps, defineEmits, onMounted, ref, computed, watch } from 'vue';
import { useCategoryData } from '../../src/composables/useCategoryData';
import AdviceTpoSelector from './AdviceTpoSelector.vue';
import AdviceStatus from './AdviceStatus.vue';
import AdviceText from './AdviceText.vue';
import ItemRegistrationGuide from './ItemRegistrationGuide.vue';
import OutfitSuggestion from './OutfitSuggestion.vue';

const { initEnums, getMainCategoryName } = useCategoryData();

const props = defineProps({
    advice: { type: Object, default: null },
    selectedTpo: { type: String, required: true },
    isAdviceLoading: { type: Boolean, required: true },
});

const emit = defineEmits(['change-tpo', 'go-to-items']);

const showOutfitSuggestion = ref(false);

const isGeneralAdvice = computed(() => {
    return props.advice?.mode === 'general_advice';
});

const formattedOutfitSuggestions = computed(() => {
    if (!props.advice?.outfit_suggestion) return [];

    return Object.entries(props.advice.outfit_suggestion).map(
        ([mainCategoryKey, part]) => ({
            categoryLabel: getMainCategoryName(mainCategoryKey),
            part,
        }),
    );
});

watch(
    () => props.selectedTpo,
    () => {
        showOutfitSuggestion.value = false;
    },
);

onMounted(() => {
    initEnums();
});
</script>

<template>
    <div class="mt-6 border rounded-lg p-4 bg-white shadow-sm">
        <h3 class="text-lg font-bold mb-3">服装アドバイス</h3>

        <!-- TPO切替 -->
        <AdviceTpoSelector
            :selected-tpo="selectedTpo"
            @change="$emit('change-tpo', $event)"
        />

        <!-- アドバイス読み込みUI -->
        <AdviceStatus
            :isLoading="isAdviceLoading"
            :error="advice?.error"
            :message="advice?.message"
        />

        <!-- アドバイス表示部 -->
        <template v-if="!isAdviceLoading && advice && !advice.error">
            <AdviceText :advice="advice" :isGeneralAdvice="isGeneralAdvice" />

            <ItemRegistrationGuide
                v-if="isGeneralAdvice"
                @go-to-items="$emit('go-to-items')"
            />

            <OutfitSuggestion
                :items="formattedOutfitSuggestions"
                :isGeneralAdvice="isGeneralAdvice"
                v-model:open="showOutfitSuggestion"
            />
        </template>
    </div>
</template>
