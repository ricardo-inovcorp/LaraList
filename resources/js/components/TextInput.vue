<template>
  <input
    class="border-gray-300 dark:border-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-gray-900 dark:text-white h-10 bg-white dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-400"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    ref="input"
  />
</template>

<script setup>
import { onMounted, ref } from 'vue';

defineProps({
  modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
  if (input.value.hasAttribute('autofocus')) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value.focus() });
</script> 