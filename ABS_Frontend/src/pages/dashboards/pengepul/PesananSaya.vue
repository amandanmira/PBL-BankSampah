<template>
  <DashboardLayout title="Pesanan Saya">
    <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-700 p-4 rounded-r-lg mb-6">
      <p class="font-bold">*Statis</p>
      <p class="text-sm">Halaman ini sedang dalam pengembangan.</p>
    </div>
    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
      <h2 class="text-2xl font-bold text-gray-800">Riwayat & Status Pesanan</h2>
      <p class="text-gray-500 mt-2">Segera hadir: Pantau status pesanan sampah Anda di sini.</p>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { checkRole } from '@/utils'

checkRole('pengepul')
</script>
