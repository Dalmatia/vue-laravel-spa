<script setup>
import { computed } from 'vue';
import OutfitCardSkeleton from '../Skeletons/OutfitCardSkeleton.vue';

const props = defineProps({
    isMobile: Boolean,
    outfits: Array,
    isLoading: Boolean,
});

const emit = defineEmits(['openOutfitOverlay']);

const skeletonCount = computed(() => (props.isMobile ? 9 : 12));
</script>

<template>
    <div id="outfit_list">
        <div
            class="grid grid-cols-3 md:grid-cols-4 gap-2 md:gap-4 lg:gap-6 pb-20"
        >
            <template v-if="isLoading">
                <OutfitCardSkeleton v-for="n in skeletonCount" :key="n" />
            </template>

            <template v-else-if="outfits && outfits.length > 0">
                <div
                    v-for="outfit in outfits"
                    :key="outfit.id"
                    class="flex flex-col h-full border rounded bg-white shadow-sm"
                >
                    <p
                        class="w-full overflow-hidden bg-[#f6f7f8] aspect-[1/1.3]"
                        @click="emit('openOutfitOverlay', outfit)"
                    >
                        <img
                            :src="outfit.file"
                            class="w-full h-full object-cover cursor-pointer"
                        />
                    </p>
                    <div
                        class="mt-auto px-[10px] py-[9px] flex items-center gap-2"
                    >
                        <img
                            :src="outfit.user.file"
                            class="rounded-full w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                        />
                        <p
                            class="text-[10px] md:text-[13.5px] font-bold truncate"
                        >
                            {{ outfit.user.name }}
                        </p>
                    </div>
                </div>
            </template>

            <template v-else>
                <div
                    class="col-span-full flex justify-center items-center min-h-[60vh]"
                >
                    <p
                        class="text-gray-600 text-base font-semibold text-center md:text-2xl md:font-bold"
                    >
                        該当するコーディネートが見つかりませんでした。
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>
