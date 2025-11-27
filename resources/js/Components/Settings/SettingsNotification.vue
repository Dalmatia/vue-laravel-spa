<script setup>
import { ref, onMounted } from 'vue';
import ToggleRow from '../ToggleRow.vue';
import SkeletonRow from '../SkeletonRow.vue';
import axios from 'axios';
import Toast from '@/components/Toast.vue';

const toastRef = ref(null);
// モック設定データ
const isLoading = ref(true);
const settings = ref({});

onMounted(async () => {
    try {
        const res = await axios.get('/api/settings');
        settings.value = res.data;
    } finally {
        isLoading.value = false;
    }
});

// 設定更新
const updateSetting = async (key) => {
    try {
        await axios.put('/api/settings', { [key]: settings.value[key] });
    } catch (e) {
        console.error(e);
        toastRef.value.show('更新に失敗しました');
    }
};
</script>

<template>
    <Toast ref="toastRef" />

    <section class="border rounded-lg p-4 bg-white shadow-sm">
        <h2 class="text-lg font-semibold mb-4">通知設定</h2>

        <template v-if="isLoading">
            <SkeletonRow /><SkeletonRow /><SkeletonRow />
        </template>

        <template v-else>
            <h3 class="text-sm font-medium text-gray-600 mb-2">メール通知</h3>
            <p class="text-xs text-gray-500 mb-3">
                パスワード再設定など重要なお知らせをメールで受け取ります。
            </p>
            <div class="pl-1 mb-4">
                <ToggleRow
                    label="パスワード再設定メール"
                    v-model="settings.email_password_reset"
                    @change="updateSetting('email_password_reset')"
                />
            </div>

            <h3 class="text-sm font-medium text-gray-600 mb-2">アプリ内通知</h3>
            <p class="text-xs text-gray-500 mb-3">
                いいね、コメント、フォローなどアプリ上で通知を受け取ります。
            </p>
            <div class="space-y-3 pl-1">
                <ToggleRow
                    label="いいね！通知"
                    v-model="settings.inapp_like"
                    @change="updateSetting('inapp_like')"
                />
                <ToggleRow
                    label="コメント通知"
                    v-model="settings.inapp_comment"
                    @change="updateSetting('inapp_comment')"
                />
                <ToggleRow
                    label="フォロー通知"
                    v-model="settings.inapp_follow"
                    @change="updateSetting('inapp_follow')"
                />
            </div>
        </template>
    </section>
</template>
