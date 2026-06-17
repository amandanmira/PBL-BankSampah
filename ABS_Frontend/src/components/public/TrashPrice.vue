<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Icon } from '@iconify/vue';

const categories = ref(['Lihat Semua']);
const activeCategory = ref('Lihat Semua');
// Saya set default ke pengepul agar Anda langsung bisa melihat diskonnya!
const roleView = ref('pengepul'); 

const trashItems = ref([]);
const isLoading = ref(true);

const fetchTrashData = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/kategori-sampah');
    const data = response.data;
    
    let allItems = [];
    data.forEach(category => {
      if (!categories.value.includes(category.nama)) {
         categories.value.push(category.nama);
      }
      
      const items = category.item_sampah || category.itemSampah;
      if (items) {
        items.forEach(item => {
          
          // ==========================================
          // PARSER DISKON ANTI-GAGAL
          // ==========================================
          let rawDiskon = parseFloat(item.diskon) || 0;
          // Mengatasi jika di DB tersimpan "80" bukan "0.8"
          let diskonDesimal = rawDiskon > 1 ? rawDiskon / 100 : rawDiskon;

          allItems.push({
            id: item.item_id,
            name: item.nama,
            harga_beli: Number(item.harga_beli) || 0,
            harga_jual: Number(item.harga_jual) || 0,
            diskon: diskonDesimal,
            category: category.nama,
            image: item.foto ? (item.foto.startsWith('http') ? item.foto : `http://localhost:8000/storage/${item.foto}`) : null
          });
        });
      }
    });
    
    trashItems.value = allItems;
  } catch (error) {
    console.error('Error fetching trash data:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchTrashData();
});

const filteredTrash = computed(() => {
  if (activeCategory.value === 'Lihat Semua') {
    return trashItems.value.slice(0, 8);
  }
  return trashItems.value.filter(item => item.category === activeCategory.value).slice(0, 4);
});
</script>

<template>
  <section id="pricing" class="py-16 md:py-24 bg-[#FAFFF9]">
    <div class="max-w-6xl mx-auto px-6 md:px-12">

      <div class="text-center mb-6 text-[#555555]">
        <h2 class="text-2xl md:text-3xl lg:text-[32px] font-extrabold mb-5 tracking-tight">
          {{ roleView === 'nasabah' ? 'Berapa Nilai Sampahmu Hari Ini?' : 'Katalog Material Daur Ulang' }}
        </h2>
        <p class="text-[15px] md:text-base font-medium max-w-2xl mx-auto leading-relaxed">
          {{ roleView === 'nasabah' ? 'Kami memberikan nilai tukar yang kompetitif dan transparan. Berikut adalah cuplikan harga untuk kategori utama.' : 'Dapatkan pasokan material terpilah berkualitas dengan harga terbaik untuk kebutuhan industri Anda.' }}
        </p>
      </div>

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

      <div class="flex flex-row md:flex-col gap-x-4 md:gap-x-0 items-start">
        
        <div class="w-[35%] md:w-full flex-shrink-0 flex flex-col md:flex-row overflow-y-auto md:overflow-x-auto whitespace-nowrap md:flex-wrap justify-start md:justify-center gap-y-3 md:gap-y-4 md:gap-x-8 mb-0 md:mb-10 px-0 md:px-2 pb-2 scrollbar-hide max-h-[350px] md:max-h-none">
          <template v-if="isLoading">
            <div v-for="i in 6" :key="i" class="h-5 md:h-6 w-20 md:w-24 bg-gray-200 animate-pulse rounded flex-shrink-0 mb-1 md:mb-0"></div>
          </template>
          <template v-else>
            <button v-for="category in categories" :key="category" @click="activeCategory = category" :class="[
              'text-left md:text-center text-[13px] md:text-[15px] transition-colors pb-1 flex-shrink-0',
              activeCategory === category
                ? 'text-[#4A7043] font-bold border-l-[3px] md:border-l-0 md:border-b-2 border-[#4A7043] pl-2 md:pl-0'
                : 'text-[#777777] font-semibold hover:text-[#555555] pl-3 md:pl-0'
            ]">
              {{ category }}
            </button>
          </template>
        </div>

        <div class="w-[65%] md:w-full flex-shrink-0 flex flex-row md:flex-wrap overflow-x-auto md:overflow-visible justify-start md:justify-center items-stretch gap-4 md:gap-6 xl:gap-8 mb-8 pb-4 md:pb-0 scrollbar-hide md:gsap-stagger-parent">
          
          <template v-if="isLoading">
            <div v-for="i in 4" :key="`skeleton-${i}`" class="min-w-[160px] md:min-w-0 md:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col shadow-sm border border-gray-100 rounded-lg overflow-hidden animate-pulse">
              <div class="bg-gray-200 w-full aspect-[4/3] md:aspect-[4/5]"></div>
              <div class="p-4 md:p-5 flex-1">
                <div class="h-4 w-3/4 bg-gray-300 rounded mb-4"></div>
                <div class="h-6 w-1/2 bg-gray-300 rounded mt-auto"></div>
              </div>
            </div>
          </template>
          
          <template v-else>
            <div v-for="item in filteredTrash" :key="item.id"
              class="gsap-stagger-item relative min-w-[200px] md:min-w-0 md:w-[calc(25%-18px)] xl:w-[calc(25%-24px)] bg-white flex flex-col shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 rounded-xl overflow-hidden group">
              
              <div class="relative bg-[#DDDDDD] w-full aspect-[4/3] md:aspect-[4/4] flex justify-center items-center overflow-hidden">
                 
                 <div v-if="item.diskon > 0"
                      class="absolute top-0 right-0 bg-[#E00000] text-white text-[12px] md:text-sm font-black px-4 py-2 rounded-bl-[20px] z-20 tracking-wider shadow-md">
                   {{ Math.round(item.diskon * 100) }}% OFF
                 </div>

                 <img v-if="item.image" :src="item.image" :alt="item.name"
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                 
                 <div v-else class="text-gray-400">
                    <Icon icon="material-symbols:image-outline" class="w-10 h-10" />
                 </div>
              </div>

              <div class="px-4 md:px-5 pb-5 pt-4 flex flex-col justify-between flex-1 w-full bg-white text-left z-10">
                
                <div>
                  <h3 class="text-[#222222] text-[15px] md:text-lg font-bold mb-4 leading-snug truncate">{{ item.name }}</h3>
                </div>

                <div class="w-full mt-auto">
                  
                  <template v-if="roleView === 'pengepul'">
                    <div class="flex flex-col">
                      <span v-if="item.diskon > 0" class="text-xs text-gray-400 line-through mb-0.5 font-medium">
                        Rp {{ item.harga_jual.toLocaleString('id-ID') }}
                      </span>
                      
                      <div class="flex items-baseline gap-1">
                        <p class="text-[#B45309] font-extrabold text-[16px] md:text-[20px]">
                          Rp {{ Math.round(item.harga_jual - (item.harga_jual * item.diskon)).toLocaleString('id-ID') }}
                        </p>
                        <span class="text-[10px] md:text-xs text-gray-500 font-medium">/kg</span>
                      </div>
                    </div>
                  </template>

                  <template v-else>
                    <div class="text-center">
                      <div class="border-t-[1.5px] border-[#4A7043] w-8 md:w-12 mx-auto mb-3"></div>
                      <p class="text-[#4A7043] font-extrabold text-[15px] md:text-[19px]">
                        Rp {{ item.harga_beli.toLocaleString('id-ID') }}
                        <span class="text-[10px] md:text-xs font-normal text-gray-500">/kg</span>
                      </p>
                    </div>
                  </template>

                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

    </div>
  </section>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>