<script setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps({
  src: { type: String, default: "" },
  alt: { type: String, default: "" },
  fallback: { type: String, default: "" },
  size: { type: String, default: "md" },
  class: { type: String, default: "" },
});

const sizeClasses = {
  sm: "w-8 h-8 text-xs",
  md: "w-10 h-10 text-sm",
  lg: "w-12 h-12 text-base",
  xl: "w-16 h-16 text-xl",
};
</script>

<template>
  <div
    :class="
      cn(
        'relative flex shrink-0 overflow-hidden rounded-full border border-stone-200 bg-stone-100 items-center justify-center font-bold text-stone-600',
        sizeClasses[props.size],
        props.class
      )
    "
  >
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      class="aspect-square h-full w-full object-cover"
      @error="(e) => e.target.style.display = 'none'"
    />
    <span v-else>{{ fallback || alt?.charAt(0) || 'U' }}</span>
  </div>
</template>
