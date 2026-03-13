<script setup>
import Heart from 'vue-material-design-icons/Heart.vue';

const props = defineProps({
    outfit: Object,
});
</script>

<template>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- 本体 -->
        <div class="flex">
            <!-- コーデ画像 -->
            <div class="w-1/2 md:w-3/5">
                <img
                    :src="outfit.file"
                    class="w-full h-full object-contain bg-gray-100"
                    loading="lazy"
                    alt="コーディネート画像"
                />
            </div>

            <!-- 使用アイテム欄 -->
            <div class="w-1/2 md:w-2/5 p-4">
                <div class="font-bold text-sm mb-3">使用アイテム</div>
                <div
                    v-if="outfit.items && outfit.items.length"
                    class="flex flex-col gap-3"
                >
                    <div
                        v-for="item in outfit.items"
                        :key="item.id"
                        class="flex items-center gap-3"
                    >
                        <img
                            :src="item.file"
                            class="w-14 h-14 object-cover rounded"
                        />

                        <span class="text-sm text-gray-700 font-medium">
                            {{ item.sub_category_name }}
                        </span>
                    </div>
                </div>

                <div v-else class="text-xs text-gray-400 italic">
                    アイテム未登録
                </div>
            </div>
        </div>

        <!-- フッター -->
        <div
            class="flex items-center justify-between border-t px-3 py-2 text-xs text-gray-600"
        >
            <div class="flex items-center gap-2">
                <img
                    :src="outfit.user.file"
                    class="w-6 h-6 rounded-full object-cover"
                />
                <span>{{ outfit.user.name }}</span>
            </div>

            <div class="flex items-center gap-1 cursor-pointer">
                <Heart
                    :size="16"
                    :class="outfit.is_liked ? 'text-red-500' : 'text-gray-400'"
                />
                {{ outfit.likes_count }}
            </div>

            <div>
                {{ outfit.outfit_date }}
            </div>
        </div>
    </div>
</template>
