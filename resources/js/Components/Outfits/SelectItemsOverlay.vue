<script setup>
import { onMounted, ref } from 'vue';

import Close from 'vue-material-design-icons/Close.vue';

const emit = defineEmits(['close', 'onItemSelected']);
const props = defineProps({ itemType: Number });

const items = ref([]);
const isLoading = ref(true);
const hasError = ref(false);

// 登録アイテムの表示
const fetchItems = async () => {
    try {
        const response = await axios.get('/api/items');
        items.value = response.data.items.filter(
            (item) => item.main_category === props.itemType
        );
    } catch (error) {
        console.error(error);
        hasError.value = true;
    } finally {
        setTimeout(() => {
            isLoading.value = false;
        }, 300);
    }
};

const selectItem = (item) => {
    // 選択されたアイテムのIDを親コンポーネントに伝える
    emit('onItemSelected', item);
};

// 「選択しない」ボタンがクリックされた場合にnullを送信
const selectNone = () => {
    emit('onItemSelected', null);
    emit('close'); // モーダルを閉じる
};

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <div
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <button class="absolute right-3" @click="emit('close')">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>
        <div
            class="max-w-6xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl p-5"
        >
            <div
                class="w-full h-full overflow-auto rounded-xl flex justify-center items-center"
            >
                <div v-if="isLoading" class="flex flex-col items-center">
                    <svg
                        class="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-gray-900"
                        fill="none"
                        viewBox="0 0 24 24"
                    ></svg>
                    <span class="text-lg font-bold mt-2">読み込み中...</span>
                </div>

                <div
                    v-else-if="hasError"
                    class="text-center text-lg text-red-500 font-bold"
                >
                    アイテムの取得に失敗しました。再度お試しください。
                </div>

                <!-- アイテムがない場合のメッセージ -->
                <div
                    v-else-if="items.length === 0"
                    class="text-center text-lg font-bold"
                >
                    アイテムが登録されていません！
                </div>

                <!-- アイテム一覧などを表示 -->
                <div
                    v-else
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-5 w-full"
                >
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex flex-col items-center justify-center cursor-pointer relative"
                    >
                        <img
                            v-if="item.file"
                            :src="item.file"
                            class="aspect-square mx-auto z-0 object-cover cursor-pointer"
                            @click="selectItem(item)"
                        />
                        <!-- 選択ボタンを押したら、選択されたアイテムを親コンポーネントに伝える -->
                        <button
                            class="mt-1 text-blue-500 hover:text-gray-900 font-extrabold"
                            @click="selectItem(item)"
                        >
                            選択
                        </button>
                    </div>

                    <!-- 「選択しない」ボタン -->
                    <div
                        class="flex flex-col items-center justify-center cursor-pointer relative"
                    >
                        <div
                            class="container border border-gray-400 h-44 lg:h-[270px] w-full p-2 flex items-center justify-center"
                            @click="selectNone()"
                        >
                            <button
                                class="mt-1 text-blue-500 hover:text-gray-900 font-extrabold"
                            >
                                選択しない
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
