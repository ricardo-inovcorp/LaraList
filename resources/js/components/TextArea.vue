<template>
  <textarea
    class="border-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-white min-h-20 bg-gray-700"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    ref="input"
  ></textarea>
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