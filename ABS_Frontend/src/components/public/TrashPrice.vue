<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const categories = ref(['Lihat Semua']);
const activeCategory = ref('Lihat Semua');
const roleView = ref('nasabah'); // 'nasabah' or 'pengepul'

const trashItems = ref([]);

const fetchTrashData = async () => {
  try {
    const response = await axios.get('/api/kategori-sampah');
    const data = response.data;
    
    let allItems = [];
    data.forEach(category => {
      categories.value.push(category.nama);
      const items = category.item_sampah || category.itemSampah;
      if (items) {
        items.forEach(item => {
          allItems.push({
            id: item.item_id,
            name: item.nama,
            harga_beli: item.harga_beli,
            harga_jual: item.harga_jual,
            category: category.nama,
            image: item.foto ? `http://localhost:8000/storage/${item.foto}` : null
          });
        });
      }
    });
    
    trashItems.value = allItems;
  } catch (error) {
    console.error('Error fetching trash data:', error);
  }
};

onMounted(() => {
  fetchTrashData();
});

const filteredTrash = computed(() => {
  if (activeCategory.value === 'Lihat Semua') {
    // Tampilkan teaser hingga 8 item secara global
    return trashItems.value.slice(0, 8);
  }
  // Batasi maksimal 4 item per kategori spesifik
  return trashItems.value.filter(item => item.category === activeCategory.value).slice(0, 4);
});
</script>

<template>
  <section id="pricing" class="py-16 md:py-24 bg-[#FAFFF9]">
    <div class="max-w-6xl mx-auto px-6 md:px-12">

      <!-- Headers -->
      <div class="text-center mb-6 text-[#555555]">
        <h2 class="text-2xl md:text-3xl lg:text-[32px] font-extrabold mb-5 tracking-tight">
          {{ roleView === 'nasabah' ? 'Berapa Nilai Sampahmu Hari Ini?' : 'Katalog Material Daur Ulang' }}
        </h2>
        <p class="text-[15px] md:text-base font-medium max-w-2xl mx-auto leading-relaxed">
          {{ roleView === 'nasabah' ? 'Kami memberikan nilai tukar yang kompetitif dan transparan. Berikut adalah cuplikan harga untuk kategori utama.' : 'Dapatkan pasokan material terpilah berkualitas dengan harga terbaik untuk kebutuhan industri Anda.' }}
        </p>
      </div>

      <!-- Toggle Switch (Small, Right Aligned) -->
      <div class="flex justify-end mb-4 px-2 md:px-0">
        <div class="inline-flex items-center border-[1.5px] border-[#999999] p-0.5 rounded-full bg-transparent shadow-sm">
          <button @click="roleView = 'nasabah'" :class="[
            'px-3 py-1 rounded-full text-[12px] font-bold transition-all duration-300',
            roleView === 'nasabah' ? 'bg-[#EAEAEA] text-[#555555]' : 'bg-transparent text-[#777777] hover:text-[#555555]'
          ]">
            Nasabah
          </button>
          <button @click="roleView = 'pengepul'" :class="[
            'px-3 py-1 rounded-full text-[12px] font-bold transition-all duration-300',
            roleView === 'pengepul' ? 'bg-[#EAEAEA] text-[#555555]' : 'bg-transparent text-[#777777] hover:text-[#555555]'
          ]">
            Pengepul
          </button>
        </div>
      </div>

      <!-- Main Layout: Sidebar on Mobile, Stacked on Desktop -->
      <div class="flex flex-row md:flex-col gap-x-4 md:gap-x-0 items-start">
        
        <!-- Categories Filter -->
        <div class="w-[35%] md:w-full flex-shrink-0 flex flex-col md:flex-row overflow-y-auto md:overflow-x-auto whitespace-nowrap md:flex-wrap justify-start md:justify-center gap-y-3 md:gap-y-4 md:gap-x-8 mb-0 md:mb-10 px-0 md:px-2 pb-2 scrollbar-hide max-h-[350px] md:max-h-none">
          <button v-for="category in categories" :key="category" @click="activeCategory = category" :class="[
            'text-left md:text-center text-[13px] md:text-[15px] transition-colors pb-1 flex-shrink-0',
            activeCategory === category
              ? 'text-[#4A7043] font-bold border-l-[3px] md:border-l-0 md:border-b-2 border-[#4A7043] pl-2 md:pl-0'
              : 'text-[#777777] font-semibold hover:text-[#555555] pl-3 md:pl-0'
          ]">
            {{ category }}
          </button>
        </div>

        <!-- Trash Grid -->
        <div class="w-[65%] md:w-full flex-shrink-0 flex flex-row md:flex-wrap overflow-x-auto md:overflow-visible justify-start md:justify-center items-stretch gap-4 md:gap-6 xl:gap-8 mb-8 pb-4 md:pb-0 scrollbar-hide md:gsap-stagger-parent">
          <div v-for="item in filteredTrash" :key="item.id"
            class="gsap-stagger-item min-w-[160px] md:min-w-0 md:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col items-center text-center shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 rounded-sm overflow-hidden">
            <!-- Image Section (Grey Background) -->
            <div class="bg-[#DDDDDD] w-full aspect-[4/5] flex justify-center items-center overflow-hidden p-0 m-0">
               <img v-if="item.image" :src="item.image" :alt="item.name"
                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
            </div>
          </template>
          <template v-else>
            <div v-for="item in filteredTrash" :key="item.id"
              class="gsap-stagger-item min-w-[160px] md:min-w-0 md:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col items-center text-center shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 rounded-sm overflow-hidden">
              <!-- Image Section (Grey Background) -->
              <div class="bg-[#DDDDDD] w-full aspect-[4/5] flex justify-center items-center overflow-hidden p-0 m-0">
                 <img v-if="item.image" :src="item.image" :alt="item.name"
                   class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
              </div>

              <!-- Content Section (White Background) -->
              <div class="px-4 md:px-6 pb-6 pt-4 md:pt-6 flex flex-col justify-between flex-1 w-full bg-white">
                <div>
                  <h3 class="text-[#555555] text-[15px] md:text-lg font-bold mb-3 leading-snug">{{ item.name }}</h3>
                </div>

            <!-- Content Section (White Background) -->
            <div class="px-4 md:px-6 pb-6 pt-4 md:pt-6 flex flex-col justify-between flex-1 w-full bg-white">
              <div>
                <h3 class="text-[#555555] text-[15px] md:text-lg font-bold mb-3 leading-snug">{{ item.name }}</h3>
              </div>

              <div class="w-full mt-auto">
                <!-- Divider Line -->
                <div class="border-t-[1.5px] border-[#4A7043] w-8 md:w-12 mx-auto mb-3"></div>
                <!-- Price -->
                <p class="text-[#4A7043] font-extrabold text-[15px] md:text-[19px]">Rp {{ (roleView === 'nasabah' ? item.harga_beli : item.harga_jual).toLocaleString('id-ID') }}</p>
              </div>
            </div>
          </template>
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

<style scoped>
/* Sembunyikan scrollbar untuk pengalaman swipe yang bersih di HP */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
</style>