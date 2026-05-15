<script setup>
defineProps({
    to: Object,
    active: Boolean,
    mobile: Boolean,
});
</script>

<template>
    <router-link
        :to="to"
        :class="
            mobile
                ? [
                      'p-3 w-1/3 flex justify-center border-t',
                      {
                          'border-t border-t-gray-900': active,
                      },
                  ]
                : [
                      'p-[17px] w-1/3 flex justify-center items-center',
                      {
                          'text-[#8E8E8E]': !active,
                          'border-t border-t-gray-900 text-gray-900': active,
                      },
                  ]
        "
    >
        <slot />
    </router-link>
</template>
