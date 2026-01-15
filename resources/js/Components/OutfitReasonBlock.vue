<script setup>
defineProps({
    title: { type: String, required: true },
    reasons: { type: Array, default: () => [] },
});
</script>

<template>
    <div
        v-if="reasons.length"
        class="mt-2 w-full text-xs text-gray-600 space-y-1"
    >
        <span class="text-[11px] text-gray-500 font-semibold">
            {{ title }}
        </span>

        <div
            v-for="(reason, idx) in reasons"
            :key="idx"
            class="flex items-start"
        >
            <span class="mr-1">・</span>
            <span class="line-clamp-2">
                {{ reason }}
            </span>
        </div>
    </div>
</template>
