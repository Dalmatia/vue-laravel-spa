<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import OutfitCardSkeleton from '../Skeletons/OutfitCardSkeleton.vue';

const props = defineProps({
    isMobile: Boolean,
    isLoading: Boolean,
    outfits: Array,
    hasMore: Boolean,
    isFetchingMore: Boolean,
});

let observer;
let isRequesting = false;
const emit = defineEmits(['openOutfitOverlay', 'loadMore']);
const skeletonCount = computed(() => (props.isMobile ? 9 : 12));
const loadMoreTrigger = ref(null);
const loadedImages = ref({});

watch(
    () => props.outfits,
    () => {
        loadedImages.value = {};
    },
);

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (
                props.isMobile &&
                entries[0].isIntersecting &&
                props.hasMore &&
                !props.isFetchingMore &&
                !isRequesting
            ) {
                isRequesting = true;
                emit('loadMore');
            }
        },
        {
            root: null,
            rootMargin: '400px',
            threshold: 0,
        },
    );

    if (loadMoreTrigger.value) {
        observer.observe(loadMoreTrigger.value);
    }
});

watch(
    () => props.isFetchingMore,
    (val) => {
        if (!val) isRequesting = false;
    },
);

onUnmounted(() => {
    if (observer && loadMoreTrigger.value) {
        observer.disconnect();
    }
});

watch(loadMoreTrigger, (el) => {
    if (el) observer.observe(el);
});
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
                <!-- コーディネート一覧 -->
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
                            loading="lazy"
                            class="w-full h-full object-cover cursor-pointer transition-opacity duration-500"
                            :class="{ 'opacity-0': !loadedImages[outfit.id] }"
                            @load="loadedImages[outfit.id] = true"
                        />
                    </p>
                    <div
                        class="mt-auto px-[10px] py-[9px] flex items-center gap-2"
                    >
                        <img
                            :src="outfit.user.file"
                            loading="lazy"
                            class="rounded-full w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                        />
                        <p
                            class="text-[10px] md:text-[13.5px] font-bold truncate"
                        >
                            {{ outfit.user.name }}
                        </p>
                    </div>
                </div>

                <template v-if="isFetchingMore">
                    <OutfitCardSkeleton
                        v-for="n in isMobile ? 3 : 4"
                        :key="'more-' + n"
                    />
                    <div class="col-span-full flex justify-center py-4">
                        <div class="loader"></div>
                    </div>
                </template>

                <!-- 「さらに読み込む」ボタン追加 -->
                <div
                    v-if="hasMore && !isFetchingMore"
                    class="col-span-full flex justify-center py-6"
                >
                    <button
                        class="px-4 py-2 border rounded bg-white hover:bg-gray-100"
                        @click="emit('loadMore')"
                    >
                        さらに読み込む
                    </button>
                </div>

                <div
                    v-if="!hasMore && !isFetchingMore && outfits.length > 0"
                    class="col-span-full text-center py-6 text-gray-500 text-sm md:text-base"
                >
                    これ以上コーディネート投稿はありません
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
        <div ref="loadMoreTrigger" class="h-10"></div>
    </div>
</template>

<style scoped>
.loader {
    width: 24px;
    height: 24px;
    border: 3px solid #ddd;
    border-top: 3px solid #555;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
