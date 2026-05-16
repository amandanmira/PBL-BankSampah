<script setup>
import { ref, computed } from 'vue';

const categories = ['Kardus & Kertas', 'Plastik', 'Logam', 'Botol Kaca', 'Lain', 'Lihat Semua'];
const activeCategory = ref('Lihat Semua');

// Sample data for trash items, will be replaced with API fetch later
const trashItems = ref([
  { id: 1, name: 'Kardus Coklat', desc: 'Kardus bekas kemasan barang.', price: 2500, category: 'Kardus & Kertas' },
  { id: 2, name: 'Kertas Putih', desc: 'Kertas HVS, buku, atau dokumen.', price: 1500, category: 'Kardus & Kertas' },
  { id: 3, name: 'Kaleng Besi', desc: 'Kaleng bekas minuman atau makanan.', price: 4000, category: 'Logam' },
  { id: 4, name: 'Plastik Campur', desc: 'Berbagai jenis botol dan gelas plastik.', price: 3000, category: 'Plastik' },
]);

const filteredTrash = computed(() => {
  if (activeCategory.value === 'Lihat Semua') {
    // Show a sample of 4 items for "Lihat Semua" as in the image
    return trashItems.value.slice(0, 4);
  }
  return trashItems.value.filter(item => item.category === activeCategory.value);
});
</script>

<template>
  <section id="pricing" class="py-16 md:py-24 bg-[#FAFFF9]">
    <div class="max-w-6xl mx-auto px-6 md:px-12">

      <!-- Headers -->
      <div class="text-center mb-10 text-[#555555]">
        <h2 class="text-2xl md:text-3xl lg:text-[32px] font-extrabold mb-5 tracking-tight">
          Berapa Nilai Sampahmu Hari Ini?
        </h2>
        <p class="text-[15px] md:text-base font-medium max-w-2xl mx-auto leading-relaxed">
          Kami memberikan nilai tukar yang kompetitif dan transparan. Berikut adalah cuplikan harga untuk kategori
          utama.
        </p>
      </div>

      <!-- Categories Filter -->
      <div class="flex flex-wrap justify-center gap-x-8 gap-y-4 mb-14">
        <button v-for="category in ['Kardus & Kertas', 'Plastik', 'Logam', 'Botol Kaca', 'Lain', 'Lihat Semua']" :key="category" @click="activeCategory = category" :class="[
          'text-[15px] transition-colors pb-1',
          activeCategory === category
            ? 'text-[#4A7043] font-bold border-b-2 border-[#4A7043]'
            : 'text-[#777777] font-semibold hover:text-[#555555]'
        ]">
          {{ category }}
        </button>
      </div>

      <!-- Trash Grid -->
      <div class="flex flex-wrap justify-center items-stretch gap-6 xl:gap-8 mb-16 gsap-stagger-parent">
        <div v-for="item in filteredTrash" :key="item.id"
          class="gsap-stagger-item w-full sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col items-center text-center shadow-sm hover:shadow-md transition-shadow duration-300">
          <!-- Image Section (Grey Background) -->
          <div class="bg-[#DDDDDD] w-full aspect-[4/5] flex justify-center items-center overflow-hidden p-0 m-0">
             <img src="/test-trash.jpeg" :alt="item.name"
               class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
          </div>

          <!-- Content Section (White Background) -->
          <div class="px-6 pb-8 pt-6 flex flex-col justify-between flex-1 w-full bg-white">
            <div>
              <h3 class="text-[#555555] text-lg font-bold mb-3 leading-snug">{{ item.name }}</h3>
              <p class="text-[#555555] text-[13.5px] leading-relaxed mb-6">{{ item.desc }}</p>
            </div>

            <div class="w-full mt-auto">
              <!-- Divider Line -->
              <div class="border-t-[1.5px] border-[#4A7043] w-12 mx-auto mb-4"></div>
              <!-- Price -->
              <p class="text-[#4A7043] font-extrabold text-[19px]">Rp {{ item.price.toLocaleString('id-ID') }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="text-center mt-8">
        <p class="text-[#4A7043] font-bold text-sm md:text-[15px] tracking-wide">
          Daftar harga jenis sampah lainnya dapat dilihat setelah kamu mendaftar.
        </p>
      </div>

    </div>
  </section>
</template>

<style scoped></style>