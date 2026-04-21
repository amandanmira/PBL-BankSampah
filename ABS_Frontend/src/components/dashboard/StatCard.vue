<script setup>
import { Card, CardContent } from "@/components/ui/card";
import { Icon } from "@iconify/vue";

defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, required: true },
  subValue: { type: String, default: "" },
  iconClass: { type: String, default: "text-[#4A7043] bg-[#E8F0E6]" },
});
</script>

<template>
  <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-stone-50 flex items-start justify-between transition-all hover:shadow-md">
    <div class="flex flex-col">
      <div :class="['p-3 rounded-2xl w-fit mb-4', iconClass]">
        <Icon :icon="icon" class="w-6 h-6" />
      </div>
      <h3 class="text-4xl font-black text-stone-800 leading-none mb-2">{{ value }}</h3>
      <p class="text-stone-400 text-sm font-semibold">{{ title }}</p>
      <p v-if="subValue" class="text-[#4A7043] text-[10px] font-bold mt-1">{{ subValue }}</p>
    </div>
  </div>
</template>
