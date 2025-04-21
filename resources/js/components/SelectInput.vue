<template>
  <select
    class="border-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-white h-10 bg-gray-700"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    ref="input"
  >
    <slot></slot>
  </select>
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