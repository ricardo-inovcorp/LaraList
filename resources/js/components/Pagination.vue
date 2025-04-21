<script setup>
defineProps({
  links: Array,
});
</script>

<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links" :key="key">
        <div
          v-if="link.url === null"
          class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
          v-html="link.label"
        />
        <Link
          v-else
          class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-100 focus:outline-none"
          :class="{ 'bg-blue-500 text-white': link.active }"
          :href="link.url"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    Link,
  },
};
</script> 