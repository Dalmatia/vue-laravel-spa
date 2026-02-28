<script setup>
const props = defineProps({
    outfit: Object,
    comments: Array,
    isLoading: Boolean,
});
const emit = defineEmits(['openOverlay']);
</script>

<template>
    <div class="hidden lg:block w-full">
        <div class="border-t border-gray-300 bg-white pt-5">
            <div class="px-[23px]">
                <h2 class="text-[16px] font-bold leading-[1] tracking-wide">
                    {{ outfit.user.name }} さんへのコメント
                </h2>
            </div>

            <div class="pb-[30px]">
                <template v-if="isLoading">
                    <div class="py-6 text-center text-gray-400">
                        読み込み中...
                    </div>
                </template>
                <template v-else>
                    <template v-if="comments.length > 0">
                        <div class="flex flex-col gap-4 pt-[17px] lg:pt-2">
                            <div
                                v-for="comment in comments.slice(0, 2)"
                                :key="comment.id"
                                :class="{
                                    'flex-row-reverse':
                                        comment.user_id === outfit.user_id,
                                }"
                            >
                                <div class="flex flex-col gap-4">
                                    <div
                                        class="flex w-full gap-4 lg:gap-[15px]"
                                        :class="{
                                            'flex-row-reverse':
                                                comment.user_id ===
                                                outfit.user_id,
                                        }"
                                    >
                                        <div
                                            class="flex h-10 w-10 shrink-0 overflow-hidden rounded-full border border-gray-300 hover:opacity-70"
                                        >
                                            <span
                                                class="box-border inline-block overflow-hidden w-initial h-initial bg-none opacity-100 border-0 m-0 p-0 max-w-full"
                                            >
                                                <img
                                                    class="rounded-full w-[38px] h-[38px]"
                                                    v-if="comment.user?.file"
                                                    :src="comment.user.file"
                                                />
                                            </span>
                                        </div>

                                        <div class="pt-[3px] lg:pt-[6px]">
                                            <div
                                                class="flex"
                                                :class="{
                                                    'flex-row-reverse':
                                                        comment.user_id ===
                                                        outfit.user_id,
                                                }"
                                            >
                                                <p
                                                    class="line-clamp-3 max-w-[272px] text-[12px] leading-[1.4] lg:max-w-[420px] lg:tracking-[0.03em] lg:text-[14px]"
                                                    v-if="comment.user?.name"
                                                >
                                                    {{ comment.user.name }}
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-end gap-[10px] lg:mt-[5px]"
                                                :class="{
                                                    'flex-row-reverse':
                                                        comment.user_id ===
                                                        outfit.user_id,
                                                }"
                                            >
                                                <div
                                                    class="max-w-[262px] flex-1 rounded-b-[6px] bg-gray-500 px-[15px] py-2 lg:max-w-[362px] lg:border lg:border-gray-300 lg:bg-gray-50 rounded-tl-[6px]"
                                                >
                                                    <span
                                                        class="absolute top-0 speech-bubble-right right-[-4px] lg:pc-speech-bubble-right"
                                                    ></span>
                                                    <p
                                                        class="whitespace-pre-wrap text-[15px] leading-[1.4] text-black lg:text-[13px] lg:leading-[1.6] lg:tracking-[0.03em] lg:text-black-400"
                                                    >
                                                        {{ comment.text }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="block text-[14px] leading-[1.8] text-blue-500 xl:pt-1 xl:leading-[1.6]"
                                @click="emit('openOverlay')"
                            >
                                ...もっと見る
                            </button>
                        </div>
                    </template>
                    <!-- コメントが存在しない場合の表示 -->
                    <template v-else>
                        <p class="mt-10 text-[14px] text-gray-600 text-center">
                            コメントはまだありません。
                        </p>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>
