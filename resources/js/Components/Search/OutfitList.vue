<script setup>
const props = defineProps({
    outfits: Array,
    isLoading: Boolean,
});
const emit = defineEmits(['openOutfitOverlay']);
</script>

<template>
    <div id="outfit_list">
        <div
            v-if="isLoading"
            class="flex justify-center items-center w-full h-[calc(100vh-215px)]"
        >
            <svg class="animate-spin h-6 w-6 text-gray-500" viewBox="0 0 24 24">
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                    fill="none"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                />
            </svg>
            <p class="text-lg font-bold text-gray-500 ml-2">読み込み中...</p>
        </div>
        <div
            v-else-if="outfits && outfits.length === 0"
            class="flex justify-center items-center w-full h-[calc(100vh-225px)]"
        >
            <p
                class="text-gray-600 text-base font-semibold text-center md:text-2xl md:font-bold"
            >
                該当するコーディネートが見つかりませんでした。
            </p>
        </div>
        <div
            v-else
            id="outfit_list"
            class="grid grid-cols-3 md:grid-cols-4 gap-[1px] md:gap-[18px] pb-20"
        >
            <div v-for="outfit in outfits" :key="outfit.id" class="w-full">
                <div
                    class="float-left border-[1px] border-[#ddd] border-solid rounded-[3px] bg-white pb-1"
                >
                    <p
                        class="w-full h-auto overflow-hidden bg-[#f6f7f8]"
                        @click="emit('openOutfitOverlay', outfit)"
                    >
                        <img
                            :src="outfit.file"
                            class="lg:w-[203px] lg:h-[304px] md:w-[156px] md:h-[234px] cursor-pointer aspect-[1/1.3]"
                        />
                    </p>
                    <div
                        id="user_profile"
                        class="border-t-0 pt-[9px] pr-[10px] pb-[9px] pl-[10px]"
                    >
                        <div
                            id="profile_image"
                            class="float-left w-[22px] md:w-[40px]"
                        >
                            <img
                                :src="outfit.user.file"
                                class="rounded-full w-[22px] h-[22px] md:w-[40px] md:h-[40px]"
                            />
                        </div>
                        <div id="username" class="w-auto float-right pt-[2px]">
                            <p class="text-[10px] md:text-[13.5px] font-bold">
                                <span
                                    v-if="outfit.user && outfit.user.name"
                                    class="float-left max-w-full whitespace-nowrap overflow-hidden text-ellipsis"
                                >
                                    {{ outfit.user.name }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
