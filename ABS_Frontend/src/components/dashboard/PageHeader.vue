<script setup>
import { Icon } from "@iconify/vue";

defineProps({
  title: { type: String, required: true },
});

const emit = defineEmits(['toggle-sidebar']);
</script>

<template>
  <header class="bg-[#4A7043] text-white p-4 lg:p-6 shadow-md flex items-center gap-4">
    <button 
      @click="emit('toggle-sidebar')" 
      class="p-2 hover:bg-white/10 rounded-lg transition-colors"
    >
      <Icon icon="material-symbols:menu" class="w-7 h-7" />
    </button>
    <h1 class="text-xl lg:text-2xl font-bold tracking-wide">{{ title }}</h1>
  </header>
</template>
