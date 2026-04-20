<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: { type: String, default: "" },
  variant: { type: String, default: "default" },
  size: { type: String, default: "default" },
  asChild: { type: Boolean, default: false },
});

const variants = {
  default: "bg-[#4A7043] text-stone-50 hover:bg-[#3d5e37]",
  destructive: "bg-red-500 text-white hover:bg-red-600",
  outline: "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
  secondary: "bg-secondary text-secondary-foreground hover:bg-secondary/80",
  ghost: "hover:bg-accent hover:text-accent-foreground",
  link: "text-primary underline-offset-4 hover:underline",
};

const sizes = {
  default: "h-10 px-4 py-2",
  sm: "h-9 rounded-md px-3",
  lg: "h-11 rounded-md px-8",
  icon: "h-10 w-10",
};
</script>

<template>
  <button
    :class="
      cn(
        'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
        variants[props.variant],
        sizes[props.size],
        props.class
      )
    "
  >
    <slot />
  </button>
</template>
