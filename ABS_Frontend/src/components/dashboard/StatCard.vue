<script setup>
import { Card, CardContent } from "@/components/ui/card";
import { Icon } from "@iconify/vue";

defineProps({
  title: { type: String, required: true },
  value: { type: String, required: true },
  icon: { type: String, required: true },
  iconClass: { type: String, default: "text-[#4A7043] bg-[#E8F0E6]" },
});
</script>

<template>
  <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-100 flex items-center justify-between">
    <div>
      <p class="text-stone-500 text-sm font-medium mb-1">{{ title }}</p>
      <h3 class="text-2xl font-bold text-stone-800">{{ value }}</h3>
    </div>
    <div :class="['p-3 rounded-2xl', iconClass]">
      <Icon :icon="icon" class="w-6 h-6" />
    </div>
  </div>
</template>
