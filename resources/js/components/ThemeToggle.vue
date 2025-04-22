<script setup>
import { ref, onMounted } from 'vue';
import { Sun, Moon, Monitor } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const themes = [
  { id: 'light', name: 'Claro', icon: Sun },
  { id: 'dark', name: 'Escuro', icon: Moon },
  { id: 'system', name: 'Sistema', icon: Monitor },
];

const { appearance, updateAppearance } = useAppearance();

// Seleciona um tema
const selectTheme = (themeId) => {
  updateAppearance(themeId);
};
</script>

<template>
  <div class="flex items-center space-x-1 rounded-lg border border-neutral-200 p-1 dark:border-neutral-800">
    <button
      v-for="theme in themes"
      :key="theme.id"
      @click="selectTheme(theme.id)"
      class="flex items-center justify-center rounded-md p-1.5 text-neutral-500 transition-colors hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800"
      :class="{ 'bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-50': appearance === theme.id }"
      :title="theme.name"
    >
      <component :is="theme.icon" class="h-4 w-4" />
    </button>
  </div>
</template> 