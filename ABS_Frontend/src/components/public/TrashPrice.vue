<script setup>
import { ref, computed } from 'vue';

const categories = ['Semua Jenis', 'Plastik', 'Besi', 'Kardus', 'Kertas', 'Lain-lain'];
const activeCategory = ref('Semua Jenis');

// Sample data for trash items, will be replaced with API fetch later
const trashItems = ref([
  { id: 1, name: 'Botol Plastik PET', desc: 'Botol air mineral, minuman ringan jernih.', price: 2500, category: 'Plastik' },
  { id: 2, name: 'Gelas Plastik', desc: 'Gelas bekas minuman boba (tanpa tutup).', price: 1500, category: 'Plastik' },
  { id: 3, name: 'Sedotan Plastik', desc: 'Sedotan plastik bening atau berwarna.', price: 500, category: 'Plastik' },
  { id: 4, name: 'Besi Tua Padat', desc: 'Potongan besi, baut, mur, atau perkakas.', price: 4000, category: 'Besi' },
  { id: 5, name: 'Kaleng Alumunium', desc: 'Kaleng bekas minuman ringan.', price: 8000, category: 'Besi' },
  { id: 6, name: 'Seng Bekas Atap', desc: 'Lembaran seng atap atau panci lipat.', price: 2500, category: 'Besi' },
  { id: 7, name: 'Kardus Tebal', desc: 'Kardus bekas elektronik kondisi kering.', price: 1500, category: 'Kardus' },
  { id: 8, name: 'Kardus Tipis', desc: 'Kardus bekas makanan instan.', price: 1000, category: 'Kardus' },
  { id: 9, name: 'Kertas HVS/Print', desc: 'Kertas dokumen, print-out, kemasan putih.', price: 2000, category: 'Kertas' },
  { id: 10, name: 'Koran Bekas', desc: 'Lembaran koran yang ditumpuk rapi kering.', price: 1200, category: 'Kertas' },
  { id: 11, name: 'Botol Kaca Utuh', desc: 'Botol sirup, kecap, jangan sampai pecah.', price: 500, category: 'Lain-lain' },
  { id: 12, name: 'Minyak Jelantah', desc: 'Minyak goreng sisa pakai warna gelap.', price: 3000, category: 'Lain-lain' },
]);

const filteredTrash = computed(() => {
  if (activeCategory.value === 'Semua Jenis') {
    // Show a sample of 8 items for "Semua Jenis" or select representative items
    return trashItems.value.slice(0, 8);
  }
  return trashItems.value.filter(item => item.category === activeCategory.value);
});
</script>

<template>
  <section class="py-16 md:py-24 bg-[#FAFFF9]">
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
        <button v-for="category in categories" :key="category" @click="activeCategory = category" :class="[
          'text-[15px] transition-colors pb-1',
          activeCategory === category
            ? 'text-[#4A7043] font-bold'
            : 'text-[#777777] font-semibold hover:text-[#555555]'
        ]">
          {{ category }}
        </button>
      </div>

      <!-- Trash Grid -->
      <div class="flex flex-wrap justify-center items-stretch gap-6 xl:gap-8 mb-16">
        <div v-for="item in filteredTrash" :key="item.id"
          class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col items-center text-center shadow-sm hover:shadow-md transition-shadow duration-300">
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