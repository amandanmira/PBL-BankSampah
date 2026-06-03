<script setup>
import { ref } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import TabRiwayatSampah from "@/components/manager/TabRiwayatSampah.vue";
import TabRiwayatPenarikan from "@/components/manager/TabRiwayatPenarikan.vue";

const activeTab = ref('Riwayat Sampah');
</script>

<template>
  <DashboardLayout title="Audit Data">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Header Description -->
      <div>
        <p class="text-sm font-medium text-stone-500">
          {{ activeTab === 'Riwayat Sampah' ? 'Riwayat sampah dan transaksi untuk audit' : 'Riwayat penarikan saldo nasabah untuk audit' }}
        </p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-4">
        <button
          @click="activeTab = 'Riwayat Sampah'"
          :class="[
            'px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm border border-transparent cursor-pointer',
            activeTab === 'Riwayat Sampah' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-50 border-stone-200'
          ]"
        >
          Riwayat Sampah
        </button>
        <button
          @click="activeTab = 'Riwayat Penarikan'"
          :class="[
            'px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm border border-transparent cursor-pointer',
            activeTab === 'Riwayat Penarikan' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-50 border-stone-200'
          ]"
        >
          Riwayat Penarikan
        </button>
      </div>

      <!-- Tab Content -->
      <TabRiwayatSampah v-if="activeTab === 'Riwayat Sampah'" />
      <TabRiwayatPenarikan v-else />

    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation: fadeIn 0.7s ease-out both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
