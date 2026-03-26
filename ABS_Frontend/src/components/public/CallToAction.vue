<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
  <section class="py-16 md:py-24 bg-[#FAFFF9]">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">

      <!-- CTA Container -->
      <div
        class="bg-[#DDDDDD] rounded-2xl md:rounded-3xl p-8 md:p-14 lg:px-20 flex flex-col md:flex-row items-center justify-between gap-10 shadow-sm">

        <!-- Text -->
        <h2
          class="text-[#555555] text-2xl md:text-3xl lg:text-[36px] font-extrabold leading-snug text-center md:text-left flex-1 tracking-tight">
          Jadilah Bagian dari Solusi
          <br />
          <span>Lingkungan</span>
        </h2>

        <!-- Action Button -->
        <div class="flex-shrink-0">
          <RouterLink to="/choose-role"
            class="inline-block bg-[#4A7043] text-white font-bold text-[15.5px] px-8 py-3.5 md:px-10 md:py-4 rounded-md hover:bg-[#3d5d36] transition-colors shadow-sm whitespace-nowrap">
            Gabung Sekarang
          </RouterLink>
        </div>

      </div>

    </div>
  </section>
</template>

<style scoped></style>