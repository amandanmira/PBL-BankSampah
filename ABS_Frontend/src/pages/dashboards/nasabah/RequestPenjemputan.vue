<script setup>
import { ref, onMounted, computed, inject, watch } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { useRouter } from "vue-router";

// Security check
checkRole("nasabah");

const router = useRouter();
const axios = inject('axios');

// State
const activeTab = ref("Jemput Sampah");
const addressType = ref("alamat_profil"); // alamat_profil or alamat_baru
const gudangList = ref([]);
const logoFile = ref(null);
const preview = ref(null);
const loading = ref(false);
const selectedItems = ref([]); // Array of selected item names
const selectedItemsId = ref([]); // Array of selected item names

// Pagination for Sampah
const currentPage = ref(1);
const itemsPerPage = 5;

// Dropdown State
const isWeightDropdownOpen = ref(false);

const form = ref({
  deskripsi: "",
  alamat: "",
  gudang_id: "",
  estimasi_berat: "1-5 kg"
});

const user = JSON.parse(sessionStorage.getItem('user') || "{}");

// Selected Gudang Info
const selectedGudang = computed(() => {
  return gudangList.value.find(g => g.gudang_id === form.value.gudang_id) || null;
});

// Paginated Sampah
const paginatedSampah = computed(() => {
  if (!selectedGudang.value || !selectedGudang.value.sampah) return [];
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return selectedGudang.value.sampah.slice(start, end);
});

const totalPages = computed(() => {
  if (!selectedGudang.value || !selectedGudang.value.sampah) return 0;
  return Math.ceil(selectedGudang.value.sampah.length / itemsPerPage);
});

// Clear selected items and reset page when gudang changes
watch(() => form.value.gudang_id, () => {
  selectedItems.value = [];
  currentPage.value = 1;
});

// Logic
const fetchGudang = async () => {
  try {
    const res = await axios.get('/api/nasabah/list-gudang');
    gudangList.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch gudang:", err);
  }
};

const handleFile = (e) => {
  const file = e.target.files[0];
  if (file) {
    logoFile.value = file;
    preview.value = URL.createObjectURL(file);
  }
};

const toggleItem = (itemName, id) => {
  const index = selectedItems.value.indexOf(itemName);
  if (index > -1) {
    selectedItems.value.splice(index, 1);
    selectedItemsId.value.splice(index, 1);
  } else {
    selectedItems.value.push(itemName);
    selectedItemsId.value.push(id);
  }
};

const submitRequest = async () => {
  if (!form.value.gudang_id) {
    alert("Silakan pilih gudang tujuan.");
    return;
  }
  if (!form.value.alamat && addressType.value === 'alamat_baru') {
    alert("Silakan isi alamat lengkap.");
    return;
  }
  if (!logoFile.value) {
    alert("Silakan upload foto sampah.");
    return;
  }
  if (selectedItems.value.length === 0) {
    alert("Silakan pilih minimal satu jenis sampah.");
    return;
  }

  try {
    loading.value = true;
    const formData = new FormData();
    
    // Combine weight, selected types, and description
    const typesStr = selectedItems.value.join(", ");
    const combinedDesc = `${form.value.estimasi_berat}|${typesStr}|${form.value.deskripsi}`;
    
    formData.append("deskripsi", form.value.deskripsi);
    formData.append("alamat", addressType.value === 'alamat_profil' ? (user.alamat || '-') : form.value.alamat);
    formData.append("foto", logoFile.value);
    formData.append("estimasi_berat", form.value.estimasi_berat);
    formData.append("nasabah_id", user.nasabah_id);
    formData.append("gudang_id", form.value.gudang_id);

    let i = 0;
    for (const s of selectedItemsId.value) {
      formData.append(`detail[${i}][sampah_id]`, s);

      i++;
    }

    // console.log(selectedItemsId.value);
    // console.log(Object.fromEntries(formData));

    await axios.post("/api/nasabah/request-penjemputan", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    alert("Request Penjemputan Berhasil Terkirim!");
    router.push('/dashboard-nasabah');
  } catch (err) {
    console.error("Failed to submit request:", err);
    alert("Gagal mengirim request. Pastikan semua data terisi.");
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

onMounted(() => {
  fetchGudang();
  if (user.alamat) {
    form.value.alamat = user.alamat;
  }
});
</script>

<template>
  <DashboardLayout title="Request Penjemputan">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10">
      
      <!-- Top Filters -->
      <div class="flex gap-4 p-2 bg-white rounded-[2rem] shadow-sm border border-stone-100">
        <button 
          @click="activeTab = 'Jemput Sampah'"
          :class="[
            'flex-1 flex items-center justify-center gap-3 py-4 rounded-[1.5rem] text-sm font-black transition-all',
            activeTab === 'Jemput Sampah' ? 'bg-[#4A7043] text-white shadow-lg' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
          Jemput Sampah
        </button>
        <button 
          @click="activeTab = 'Setor Manual'"
          :class="[
            'flex-1 flex items-center justify-center gap-3 py-4 rounded-[1.5rem] text-sm font-black transition-all',
            activeTab === 'Setor Manual' ? 'bg-[#4A7043] text-white shadow-lg' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          <Icon icon="material-symbols:storefront-outline" class="w-6 h-6" />
          Setor Manual
        </button>
      </div>

      <!-- Main Form Section -->
      <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100 space-y-8">
        
        <!-- Gudang Selection -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="space-y-3">
            <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
              <Icon icon="material-symbols:home-work-outline" class="w-4 h-4" />
              Pilih Gudang Tujuan <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <select 
                v-model="form.gudang_id"
                class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-4 px-5 text-sm font-bold appearance-none focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all pr-12"
              >
                <option value="" disabled>Pilih Gudang Cabang</option>
                <option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id">
                  {{ gudang.alamat }}
                </option>
              </select>
              <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-6 h-6 text-stone-400 pointer-events-none" />
            </div>
          </div>

          <!-- Gudang Info Card -->
          <div v-if="selectedGudang" class="bg-stone-50 rounded-2xl p-5 border border-stone-100 flex flex-col justify-center">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">ID Gudang</p>
                <p class="font-black text-stone-800">GDG-{{ String(selectedGudang.gudang_id).padStart(3, '0') }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Alamat Gudang</p>
                <p class="font-black text-stone-800 leading-tight">{{ selectedGudang.alamat }}</p>
              </div>
            </div>
          </div>
          <div v-else class="bg-stone-50/50 rounded-2xl p-5 border border-dashed border-stone-200 flex items-center justify-center text-stone-400">
            <p class="text-xs font-bold italic">Pilih gudang untuk melihat detail informasi</p>
          </div>
        </div>

        <!-- Address Selection -->
        <div class="space-y-6">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
            <Icon icon="material-symbols:location-on-outline" class="w-4 h-4" />
            Pilih Alamat Penjemputan <span class="text-red-500">*</span>
          </label>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button 
              @click="addressType = 'alamat_profil'"
              :class="[
                'flex flex-col items-center justify-center p-6 rounded-[2rem] border-2 transition-all gap-2',
                addressType === 'alamat_profil' ? 'bg-[#E8F0E6] border-[#4A7043] text-[#4A7043]' : 'bg-white border-stone-100 text-stone-400 hover:border-stone-200'
              ]"
            >
              <Icon icon="material-symbols:home-outline" class="w-8 h-8" />
              <div class="text-center">
                <p class="font-black text-sm">Alamat di Profil</p>
                <p class="text-[10px] font-bold opacity-60">Gunakan alamat tersimpan</p>
              </div>
            </button>
            <button 
              @click="addressType = 'alamat_baru'"
              :class="[
                'flex flex-col items-center justify-center p-6 rounded-[2rem] border-2 transition-all gap-2',
                addressType === 'alamat_baru' ? 'bg-[#E8F0E6] border-[#4A7043] text-[#4A7043]' : 'bg-white border-stone-100 text-stone-400 hover:border-stone-200'
              ]"
            >
              <Icon icon="material-symbols:add-location-outline" class="w-8 h-8" />
              <div class="text-center">
                <p class="font-black text-sm">Alamat Baru</p>
                <p class="text-[10px] font-bold opacity-60">Input alamat berbeda</p>
              </div>
            </button>
          </div>

          <!-- New Address Input -->
          <div v-if="addressType === 'alamat_baru'" class="space-y-3 animate-in slide-in-from-top-4 duration-300">
            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest ml-1">Alamat Lengkap <span class="text-red-500">*</span></label>
            <textarea 
              v-model="form.alamat"
              placeholder="Masukkan alamat lengkap untuk penjemputan..." 
              class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-4 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all min-h-[120px]"
            ></textarea>
            <div class="flex items-center gap-2 text-stone-400 px-1">
              <Icon icon="material-symbols:lightbulb-outline" class="w-3.5 h-3.5 text-orange-400" />
              <p class="text-[10px] font-bold italic">Pastikan alamat lengkap dan jelas agar petugas mudah menemukan lokasi</p>
            </div>
          </div>
          <div v-else class="bg-stone-50 rounded-2xl p-5 border border-stone-100">
             <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Alamat Profil Saat Ini:</p>
             <p class="text-sm font-bold text-stone-600">{{ user.alamat || 'Belum diatur di profil' }}</p>
          </div>
        </div>

        <!-- Photo Upload (Updated with Icon) -->
        <div class="space-y-3">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
            <Icon icon="material-symbols:photo-camera-outline" class="w-4 h-4" />
            Foto Sampah yang Sudah Ditata <span class="text-red-500">*</span>
          </label>
          <div 
            @click="$refs.fileInput.click()"
            class="group relative border-2 border-dashed border-stone-200 rounded-[2.5rem] p-12 flex flex-col items-center justify-center gap-6 cursor-pointer hover:border-[#4A7043] hover:bg-stone-50 transition-all overflow-hidden"
          >
            <input 
              type="file" 
              ref="fileInput" 
              class="hidden" 
              @change="handleFile" 
              accept="image/*"
            />
            
            <template v-if="!preview">
              <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                <Icon icon="material-symbols:upload" class="w-10 h-10 text-stone-400 group-hover:text-[#4A7043]" />
              </div>
              <div class="text-center">
                <p class="text-xl font-black text-stone-800">Upload Foto Sampah</p>
                <p class="text-xs font-bold text-stone-400 mt-1">Klik untuk memilih foto (maks 4MB)</p>
              </div>
            </template>
            <template v-else>
              <img :src="preview" class="absolute inset-0 w-full h-full object-cover opacity-20" />
              <div class="relative z-10 w-24 h-24 rounded-3xl overflow-hidden border-4 border-white shadow-xl">
                <img :src="preview" class="w-full h-full object-cover" />
              </div>
              <p class="relative z-10 font-black text-[#4A7043] text-sm bg-white/80 px-4 py-1.5 rounded-full backdrop-blur-sm">Ganti Foto</p>
            </template>
          </div>
        </div>

        <!-- Description Section -->
        <div class="space-y-3">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
            <Icon icon="material-symbols:description-outline" class="w-4 h-4" />
            Deskripsi <span class="text-red-500">*</span>
          </label>
          <textarea 
            v-model="form.deskripsi"
            placeholder="Contoh: Mayoritas sampah berupa botol plastik air mineral dan beberapa tumpuk kardus mie instan..." 
            class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-4 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all min-h-[120px]"
          ></textarea>
        </div>

        <!-- Estimation Weight (Custom Styled Dropdown - Lengthened) -->
        <div class="space-y-3">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
            <Icon icon="material-symbols:monitor-weight-outline" class="w-4 h-4" />
            Estimasi Berat <span class="text-red-500">*</span>
          </label>
          <div class="relative w-full">
            <button 
              @click="isWeightDropdownOpen = !isWeightDropdownOpen"
              class="w-full bg-white border border-stone-200 rounded-[1.5rem] py-4 px-6 text-sm font-black flex justify-between items-center shadow-sm hover:border-stone-300 transition-all"
            >
              {{ form.estimasi_berat }}
              <Icon icon="material-symbols:keyboard-arrow-down" :class="['w-6 h-6 text-stone-400 transition-transform duration-300', isWeightDropdownOpen ? 'rotate-180' : '']" />
            </button>
            
            <div v-if="isWeightDropdownOpen" class="absolute z-20 w-full mt-2 bg-white border border-stone-200 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-100">
              <div 
                v-for="opt in ['1-5 kg', '6-10 kg', '11-20 kg', 'Lebih dari 20 kg']" 
                :key="opt"
                @click="form.estimasi_berat = opt; isWeightDropdownOpen = false"
                :class="[
                  'px-6 py-4 text-sm font-bold cursor-pointer transition-colors',
                  form.estimasi_berat === opt ? 'bg-[#2D4363] text-white' : 'hover:bg-stone-50 text-stone-600'
                ]"
              >
                {{ opt }}
              </div>
            </div>
          </div>
          <p class="text-[10px] font-bold text-stone-400 italic ml-1">Berat akhir akan ditentukan setelah penimbangan oleh petugas</p>
        </div>

        <!-- Jenis Sampah Section (with Pagination) -->
        <div class="space-y-4">
          <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
            <Icon icon="material-symbols:recycling" class="w-4 h-4" />
            Jenis Sampah <span class="text-red-500">*</span>
            <span class="text-[10px] text-stone-400 font-bold lowercase normal-case ml-2">(Bisa pilih lebih dari 1)</span>
          </label>

          <!-- Container Box -->
          <div class="bg-stone-50/50 rounded-[2.5rem] p-6 border border-stone-100 min-h-[400px] flex flex-col">
            <div v-if="selectedGudang && paginatedSampah.length > 0" class="flex-1 space-y-3">
              <div 
                v-for="s in paginatedSampah" 
                :key="s.sampah_id"
@click="toggleItem(s.item_sampah.nama, s.sampah_id)"
                :class="[
                  'p-5 rounded-2xl border-2 transition-all cursor-pointer flex justify-between items-center group bg-white shadow-sm',
                  selectedItems.includes(s.item_sampah.nama) ? 'border-[#4A7043] ring-1 ring-[#4A7043]' : 'border-transparent hover:border-stone-200'
                ]"
              >
                <div class="flex-1">
                  <h5 :class="['font-black transition-colors', selectedItems.includes(s.item_sampah.nama) ? 'text-[#4A7043]' : 'text-stone-800']">
                    {{ s.item_sampah.nama }}
                  </h5>
                  <p class="text-[10px] font-bold text-stone-400 mt-1">Sedia di gudang ini (Stok: {{ s.stok }} unit)</p>
                </div>
                <div class="text-right">
                  <p class="font-black text-[#4A7043]">{{ formatCurrency(s.item_sampah.harga_beli) }}/kg</p>
                </div>
              </div>
            </div>
            <div v-else class="flex-1 flex flex-col items-center justify-center text-center px-6">
              <Icon icon="material-symbols:inventory-2-outline" class="w-12 h-12 text-stone-300 mb-3" />
              <p class="text-stone-400 font-bold text-sm italic">
                {{ form.gudang_id ? 'Maaf, gudang ini belum memiliki daftar jenis sampah.' : 'Silakan pilih gudang tujuan terlebih dahulu untuk melihat jenis sampah.' }}
              </p>
            </div>

            <!-- Pagination Controls -->
            <div v-if="totalPages > 1" class="mt-8 flex items-center justify-center gap-4">
              <button 
                @click="currentPage > 1 && currentPage--"
                :disabled="currentPage === 1"
                class="p-3 rounded-xl bg-white border border-stone-200 text-stone-400 disabled:opacity-30 hover:text-[#4A7043] transition-colors shadow-sm"
              >
                <Icon icon="material-symbols:chevron-left" class="w-6 h-6" />
              </button>
              <div class="flex gap-2">
                <button 
                  v-for="p in totalPages" 
                  :key="p"
                  @click="currentPage = p"
                  :class="[
                    'w-10 h-10 rounded-xl font-black text-xs transition-all',
                    currentPage === p ? 'bg-[#4A7043] text-white shadow-lg' : 'bg-white border border-stone-200 text-stone-400 hover:border-stone-300'
                  ]"
                >
                  {{ p }}
                </button>
              </div>
              <button 
                @click="currentPage < totalPages && currentPage++"
                :disabled="currentPage === totalPages"
                class="p-3 rounded-xl bg-white border border-stone-200 text-stone-400 disabled:opacity-30 hover:text-[#4A7043] transition-colors shadow-sm"
              >
                <Icon icon="material-symbols:chevron-right" class="w-6 h-6" />
              </button>
            </div>
          </div>

          <!-- Selection Summary -->
          <div v-if="selectedItems.length > 0" class="bg-green-50 rounded-2xl p-4 border border-green-100 flex items-start gap-3">
            <div class="p-1.5 bg-white rounded-lg text-[#4A7043] shadow-sm">
              <Icon icon="material-symbols:check-circle-outline" class="w-4 h-4" />
            </div>
            <p class="text-xs font-bold text-green-800">
              <span class="text-green-600 font-black uppercase tracking-widest mr-2 text-[10px]">Dipilih:</span>
              {{ selectedItems.join(", ") }}
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <button 
          @click="submitRequest"
          :disabled="loading"
          class="w-full py-5 rounded-[1.5rem] bg-[#4A7043] text-white font-black text-lg shadow-xl shadow-green-900/10 hover:scale-[1.01] active:scale-[0.98] transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:hover:scale-100"
        >
          <template v-if="!loading">
            Request Penjemputan
            <Icon icon="material-symbols:send-outline" class="w-6 h-6" />
          </template>
          <template v-else>
            <Icon icon="line-md:loading-twotone-loop" class="w-6 h-6" />
            Mengirim Request...
          </template>
        </button>

      </div>
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

select {
  background-image: none;
}
</style>
