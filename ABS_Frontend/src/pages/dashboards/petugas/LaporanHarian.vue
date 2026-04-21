<script setup>
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
</script>

<template>
  <DashboardLayout title="Laporan Harian">
    <div class="bg-white rounded-[2.5rem] p-12 shadow-sm border border-stone-100 flex flex-col items-center justify-center text-center">
      <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center mb-6">
        <Icon icon="material-symbols:description-outline" class="w-10 h-10 text-stone-300" />
      </div>
      <h2 class="text-2xl font-black text-stone-800 mb-2">Laporan Harian</h2>
      <p class="text-stone-400 font-medium max-w-xs">Halaman ini sedang dalam tahap pengembangan. Segera Anda dapat melihat laporan harian di sini.</p>
    </div>
  </DashboardLayout>
</template>
