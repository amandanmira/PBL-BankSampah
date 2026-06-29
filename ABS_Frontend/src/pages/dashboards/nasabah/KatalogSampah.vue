<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";

checkRole("nasabah");

const axios = inject('axios');

const gudangList = ref([]);
const loading = ref(false);

const searchQuery = ref("");
const selectedGudangFilter = ref(null);

const currentPage = ref(1);
const itemsPerPage = 6;

// Fetch Gudang
const fetchGudang = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/nasabah/list-gudang');
    gudangList.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch gudang:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchGudang();
});

// All Sampah (Flattened from gudangList)
const allSampah = computed(() => {
  const items = [];
  gudangList.value.forEach(gudang => {
    if (gudang.sampah && Array.isArray(gudang.sampah)) {
      gudang.sampah.forEach(s => {
        if (parseFloat(s.stok) > 0) {
          items.push({
            ...s,
            gudang_id: gudang.gudang_id,
            gudang_alamat: gudang.alamat,
            // Extract specific properties for easier access
            nama: s.item_sampah?.nama || 'Tanpa Nama',
            harga_beli: s.item_sampah?.harga_beli || 0,
            foto: s.item_sampah?.foto || null
          });
        }
      });
    }
  });
  return items;
});

// Filtered Sampah
const filteredSampah = computed(() => {
  let result = allSampah.value;

  if (selectedGudangFilter.value !== null) {
    result = result.filter(s => Number(s.gudang_id) === Number(selectedGudangFilter.value));
  }

  if (searchQuery.value.trim() !== "") {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(s => s.nama.toLowerCase().includes(q));
  }

  return result;
});

// Paginated
const totalPages = computed(() => {
  return Math.ceil(filteredSampah.value.length / itemsPerPage) || 1;
});

const paginatedSampah = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredSampah.value.slice(start, start + itemsPerPage);
});

const visiblePages = computed(() => {
  const range = [];
  const delta = 2; // Number of pages to show around current page
  const left = currentPage.value - delta;
  const right = currentPage.value + delta + 1;
  
  for (let i = 1; i <= totalPages.value; i++) {
    if (i === 1 || i === totalPages.value || (i >= left && i < right)) {
      range.push(i);
    } else if (i === left - 1 || i === right) {
      range.push('...');
    }
  }
  
  const uniqueRange = [];
  for (let i = 0; i < range.length; i++) {
    if (range[i] !== '...' || range[i - 1] !== '...') {
      uniqueRange.push(range[i]);
    }
  }
  return uniqueRange;
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const getDescription = (name) => {
  const lowercaseName = name?.toLowerCase() || '';
  if (lowercaseName.includes('botol plastik')) {
    return 'Botol plastik bekas minuman (PET), bersih dan tidak pecah';
  }
  if (lowercaseName.includes('kardus')) {
    return 'Kardus bekas dalam kondisi kering dan tidak basah';
  }
  if (lowercaseName.includes('botol kaca')) {
    return 'Botol kaca bekas dalam kondisi bersih dan utuh';
  }
  if (lowercaseName.includes('kaleng') || lowercaseName.includes('aluminium')) {
    return 'Kaleng minuman bekas (aluminium) dalam kondisi bersih';
  }
  if (lowercaseName.includes('kertas') || lowercaseName.includes('hvs') || lowercaseName.includes('koran') || lowercaseName.includes('majalah')) {
    return 'Kertas bekas (HVS, koran, majalah) dalam kondisi kering';
  }
  if (lowercaseName.includes('besi') || lowercaseName.includes('logam')) {
    return 'Besi atau logam bekas dalam berbagai bentuk';
  }
  return 'Barang bekas berkualitas layak daur ulang.';
};

// Reset pagination when filters change
const changeFilter = (gudangId) => {
  selectedGudangFilter.value = gudangId;
  currentPage.value = 1;
};

const onSearchInput = () => {
  currentPage.value = 1;
};
</script>

<template>
  <DashboardLayout title="Katalog Sampah">
    <div class="space-y-8 pb-10">
      
      <!-- Search Bar -->
      <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-2 flex items-center gap-3">
        <Icon icon="material-symbols:search" class="w-6 h-6 text-stone-400 ml-4" />
        <input 
          v-model="searchQuery" 
          @input="onSearchInput"
          type="text" 
          placeholder="Cari jenis sampah..." 
          class="w-full bg-transparent border-none focus:outline-none py-3 text-sm font-medium text-stone-700"
        />
      </div>

      <!-- Main Content Area -->
      <div>
        <h2 class="text-xl font-black text-stone-800 mb-6 tracking-wide">Daftar Sampah Tersedia</h2>

        <!-- Gudang Filter Pills -->
        <div class="flex flex-wrap gap-3 mb-8">
          <button 
            @click="changeFilter(null)"
            :class="[
              'px-6 py-2.5 rounded-full text-xs font-black transition-all shadow-sm border',
              selectedGudangFilter === null 
                ? 'bg-[#4A7043] text-white border-[#4A7043]' 
                : 'bg-stone-100 text-stone-500 border-stone-200 hover:bg-stone-200'
            ]"
          >
            Semua Gudang
          </button>
          
          <button 
            v-for="gudang in gudangList" 
            :key="gudang.gudang_id"
            @click="changeFilter(gudang.gudang_id)"
            :class="[
              'px-6 py-2.5 rounded-full text-xs font-black transition-all shadow-sm border flex items-center gap-2',
              selectedGudangFilter === gudang.gudang_id 
                ? 'bg-[#4A7043] text-white border-[#4A7043]' 
                : 'bg-stone-100 text-stone-500 border-stone-200 hover:bg-stone-200'
            ]"
          >
            Gudang {{ gudang.gudang_id }}
            <span :class="['font-medium text-[10px] opacity-70 max-w-[150px] truncate', selectedGudangFilter === gudang.gudang_id ? 'text-white' : 'text-stone-400']">
              ({{ gudang.alamat }})
            </span>
          </button>
        </div>

        <!-- Catalog Grid -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-stone-400">
          <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 mb-4 text-[#4A7043]" />
          <p class="font-bold text-sm">Memuat katalog sampah...</p>
        </div>
        
        <div v-else-if="paginatedSampah.length > 0">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="item in paginatedSampah" 
              :key="item.sampah_id"
              class="bg-white rounded-3xl overflow-hidden border border-stone-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
            >
              <!-- Image Container -->
              <div class="relative h-56 w-full bg-stone-50 overflow-hidden">
                <img 
                  v-if="item.foto" 
                  :src="`${axios.defaults.baseURL}/storage/${item.foto}`" 
                  :alt="item.nama"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-stone-300">
                  <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12 mb-2" />
                  <span class="text-[10px] font-bold uppercase tracking-widest">No Image</span>
                </div>
                
                <!-- Tag Gudang -->
                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5 border border-white/50">
                  <Icon icon="material-symbols:location-on" class="w-3.5 h-3.5 text-[#4A7043]" />
                  <span class="text-[10px] font-black text-[#4A7043]">Gudang {{ item.gudang_id }}</span>
                </div>
              </div>

              <!-- Card Body (Mockup Style) -->
              <div class="p-6">
                <!-- Title Row -->
                <div class="flex items-center justify-between gap-3 mb-2">
                  <h3 class="text-lg md:text-xl font-bold text-stone-800 truncate">{{ item.nama }}</h3>
                  <div class="w-7 h-7 rounded-full bg-[#E8F0E6] text-[#4A7043] flex items-center justify-center shrink-0">
                    <Icon icon="material-symbols:package-2-outline" class="w-4.5 h-4.5" />
                  </div>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-stone-400 font-medium leading-relaxed mb-6">
                  {{ getDescription(item.nama) }}
                </p>

                <!-- Divider line -->
                <div class="border-t border-stone-100/80 my-4"></div>

                <!-- Price Section -->
                <div>
                  <p class="text-[10px] md:text-xs text-stone-400 font-bold uppercase tracking-wider">Harga per kg</p>
                  <h4 class="text-xl md:text-2xl font-black text-[#4A7043] mt-1">{{ formatCurrency(item.harga_beli) }}</h4>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination (Mockup Style) -->
          <div v-if="totalPages > 1" class="mt-12 flex items-center justify-center gap-3">
            <button 
              @click="currentPage > 1 && currentPage--"
              :disabled="currentPage === 1"
              class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-stone-400 hover:text-stone-800 disabled:opacity-20 transition-colors cursor-pointer"
            >
              <Icon icon="material-symbols:chevron-left" class="w-6 h-6" />
            </button>
            <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-center">
              <template v-for="(p, index) in visiblePages" :key="index">
                <span v-if="p === '...'" class="px-1.5 text-stone-400 font-bold select-none">...</span>
                <button 
                  v-else
                  @click="currentPage = p"
                  :class="[
                    'w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-xs sm:text-sm font-bold transition-all rounded-full cursor-pointer',
                    currentPage === p 
                      ? 'bg-stone-700 text-white shadow-md' 
                      : 'text-stone-500 hover:text-stone-800 hover:bg-stone-100/50'
                  ]"
                >
                  {{ p }}
                </button>
              </template>
            </div>
            <button 
              @click="currentPage < totalPages && currentPage++"
              :disabled="currentPage === totalPages"
              class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-stone-400 hover:text-stone-800 disabled:opacity-20 transition-colors cursor-pointer"
            >
              <Icon icon="material-symbols:chevron-right" class="w-6 h-6" />
            </button>
          </div>
        </div>
        
        <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-stone-100 border-dashed text-stone-400">
          <Icon icon="material-symbols:search-off" class="w-16 h-16 mb-4 text-stone-300" />
          <p class="font-black text-lg text-stone-500 mb-2">Tidak ada sampah ditemukan</p>
          <p class="text-xs font-bold">Coba ubah kata kunci pencarian atau filter gudang.</p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
