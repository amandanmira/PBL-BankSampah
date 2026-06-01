<script setup>
import { ref, defineProps } from 'vue';
import { Icon } from "@iconify/vue";

const props = defineProps({
  isLoadingCharts: {
    type: Boolean,
    default: true
  },
  distribusiSaatIni: {
    type: Array,
    required: true
  },
  detailSampah: {
    type: Array,
    required: true
  }
});

const isDistribusiModalOpen = ref(false);
</script>

<template>
  <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col min-h-[380px]">
    <template v-if="isLoadingCharts">
      <div class="flex justify-between items-center mb-6">
        <div class="h-6 w-40 bg-stone-200 rounded animate-pulse"></div>
      </div>
      <div class="space-y-6 flex-1 flex flex-col justify-center">
        <div v-for="i in 4" :key="`dist-skel-${i}`" class="space-y-2">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full bg-stone-200 animate-pulse"></div>
              <div class="h-4 w-16 bg-stone-200 rounded animate-pulse"></div>
            </div>
            <div class="h-4 w-12 bg-stone-200 rounded animate-pulse"></div>
          </div>
          <div class="w-full bg-stone-100 rounded-full h-2"></div>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-stone-800">Distribusi Saat Ini</h3>
        <button @click="isDistribusiModalOpen = true"
          class="text-xs flex items-center gap-1 font-bold transition-all cursor-pointer px-2.5 py-1.5 rounded-lg"
          :class="isDistribusiModalOpen ? 'bg-[#4A7043] text-white shadow-sm' : 'text-[#4A7043] hover:text-[#3D5A35] hover:bg-stone-50'">
          Lihat Detail <Icon icon="material-symbols:arrow-outward" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div class="space-y-6 flex-1 flex flex-col justify-center">
        <div v-for="(item, index) in distribusiSaatIni" :key="index" class="space-y-2 group">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full" :class="item.dotClass"></div>
              <span class="text-sm font-bold text-stone-600">{{ item.name }}</span>
            </div>
            <span class="text-sm font-bold text-stone-800">{{ item.value }}</span>
          </div>
          <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
            <div class="h-2 rounded-full transition-all duration-1000 group-hover:opacity-80" :class="item.colorClass" :style="{ width: `${item.percentage}%` }"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal Detail Distribusi Sampah (Teleported) -->
    <Teleport to="body">
      <div v-if="isDistribusiModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 transition-opacity duration-300">
        <!-- Backdrop Blur -->
        <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-sm" @click="isDistribusiModalOpen = false"></div>

        <div class="bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-stone-200 max-w-3xl w-full overflow-hidden transform transition-all duration-300 scale-100 flex flex-col max-h-[85vh] relative z-10">
          <!-- Modal Header -->
          <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
            <div>
              <h3 class="text-xl font-bold text-stone-800">Detail Distribusi Sampah</h3>
              <p class="text-xs text-stone-500 mt-1">Daftar rincian item sampah, lokasi gudang penyimpanan, dan stok masing-masing kategori</p>
            </div>
            <button @click="isDistribusiModalOpen = false" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors cursor-pointer">
              <Icon icon="material-symbols:close-rounded" class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-stone-50/20">
            <div v-for="category in distribusiSaatIni" :key="category.id" class="space-y-3">
              <!-- Category Header -->
              <div class="flex justify-between items-center bg-stone-100/70 p-3.5 rounded-xl border border-stone-200/50">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :class="category.dotClass"></div>
                  <h4 class="text-sm font-extrabold text-stone-700 uppercase tracking-wider">{{ category.name }}</h4>
                </div>
                <span class="text-xs font-extrabold text-stone-800 bg-white px-2.5 py-1 rounded-md border border-stone-200">Total: {{ category.value }}</span>
              </div>
              
              <!-- Items List -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-2">
                <div v-for="(item, idx) in detailSampah.filter(i => i.kategori_id === category.id)" :key="idx"
                  class="p-4 rounded-xl border border-stone-100 bg-white flex flex-col justify-between gap-3 hover:bg-stone-50 transition-all duration-200 shadow-sm">
                  <div class="space-y-1">
                    <h5 class="text-xs font-bold text-stone-800">{{ item.nama_item }}</h5>
                    <div class="text-[10px] text-stone-500 flex items-center gap-1">
                      <Icon icon="material-symbols:warehouse-outline" class="w-3.5 h-3.5 text-stone-400" />
                      <span>{{ item.nama_gudang }}</span>
                    </div>
                  </div>
                  <div class="flex justify-between items-end border-t border-stone-100 pt-2">
                    <div class="text-[10px] text-stone-400">
                      <div>Beli: <span class="font-semibold text-stone-600">Rp{{ item.harga_beli.toLocaleString('id-ID') }}/kg</span></div>
                      <div>Jual: <span class="font-semibold text-stone-600">Rp{{ item.harga_jual.toLocaleString('id-ID') }}/kg</span></div>
                    </div>
                    <span class="text-xs font-bold text-[#4A7043] bg-emerald-50 px-2 py-0.5 rounded-md">{{ item.stok.toLocaleString('id-ID') }} kg</span>
                  </div>
                </div>
                <!-- Empty Item Handler -->
                <div v-if="detailSampah.filter(i => i.kategori_id === category.id).length === 0" 
                  class="col-span-full py-4 text-center text-xs text-stone-400 italic">
                  Belum ada rincian item untuk kategori ini.
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 flex justify-end">
            <button @click="isDistribusiModalOpen = false" class="px-5 py-2.5 text-sm font-bold text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-xl transition-all cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
