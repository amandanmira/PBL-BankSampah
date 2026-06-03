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
  <div class="bg-white rounded-[1.8rem] p-5 md:p-7 shadow-sm border border-stone-100/50 flex items-center justify-between transition-all hover:shadow-md">
    <div class="flex flex-col">
      <p class="text-stone-400 text-xs md:text-sm font-semibold mb-1">{{ title }}</p>
      <h3 class="text-xl md:text-3xl font-black text-stone-800 leading-tight">{{ value }}</h3>
      <p v-if="subValue" class="text-[#4A7043] text-[10px] font-bold mt-1">{{ subValue }}</p>
    </div>
    <div :class="['p-3.5 rounded-2xl shrink-0 flex items-center justify-center shadow-sm', iconClass]">
      <Icon :icon="icon" class="w-6 h-6 md:w-7 md:h-7" />
    </div>
  </div>
</template>
