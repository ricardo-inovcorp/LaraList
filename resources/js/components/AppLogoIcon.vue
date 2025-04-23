<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img src="/storage/logo/logo laralist.png" alt="LaraList Icon" :class="className" v-bind="$attrs" />
</template>
