<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from '@/utils';
import Swal from 'sweetalert2';

checkRole('admin');

const axios = inject('axios');
const kategoriList = ref([]);
const loading = ref(false);
const expandedCategoryId = ref(null);

// Modal States
const isModalOpen = ref(false);
const modalMode = ref('add'); // 'add' or 'edit'
const currentStep = ref(1); // 1: Category Name, 2: Item List
const currentCategory = ref({
  id: null,
  nama: '',
  items: [
    { nama: '', harga_beli: 0, harga_jual: 0, diskon: 0, foto: null, fotoPreview: null }
  ]
});

const toggleAccordion = (id) => {
  expandedCategoryId.value = expandedCategoryId.value === id ? null : id;
};

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(number);
};

const fetchKategori = async () => {
  loading.value = true;
  try {
    const token = sessionStorage.getItem("token");
    const res = await axios.get("/api/admin/kategori-sampah", {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    kategoriList.value = res.data;
  } catch (err) {
    console.error("Failed to fetch categories:", err);
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  modalMode.value = 'add';
  currentStep.value = 1;
  currentCategory.value = {
    id: null,
    nama: '',
    items: [{ nama: '', harga_beli: 0, harga_jual: 0, diskon: 0, foto: null, fotoPreview: null }]
  };
  isModalOpen.value = true;
};

const openEditModal = (kategori) => {
  modalMode.value = 'edit';
  currentStep.value = 1;
  currentCategory.value = {
    id: kategori.kategori_id,
    nama: kategori.nama,
    items: kategori.item_sampah.map(item => ({
      item_id: item.item_id,
      nama: item.nama,
      harga_beli: item.harga_beli,
      harga_jual: item.harga_jual,
      diskon: item.diskon * 100, // DB stores 0.10, UI shows 10
      foto: null,
      fotoPreview: item.foto ? `${axios.defaults.baseURL}/storage/${item.foto}` : null,
      active: item.active
    }))
  };
  if (currentCategory.value.items.length === 0) {
    currentCategory.value.items.push({ nama: '', harga_beli: 0, harga_jual: 0, diskon: 0, foto: null, fotoPreview: null });
  }
  isModalOpen.value = true;
};

const nextStep = () => {
  if (currentCategory.value.nama.trim() === '') {
    Swal.fire('Error', 'Nama kategori harus diisi', 'error');
    return;
  }
  currentStep.value = 2;
};

const prevStep = () => {
  currentStep.value = 1;
};

const addItem = () => {
  currentCategory.value.items.push({
    nama: '',
    harga_beli: 0,
    harga_jual: 0,
    diskon: 0,
    foto: null,
    fotoPreview: null
  });
};

const removeItem = (index) => {
  currentCategory.value.items.splice(index, 1);
};

const handleFileUpload = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    currentCategory.value.items[index].foto = file;
    currentCategory.value.items[index].fotoPreview = URL.createObjectURL(file);
  }
};

const saveKategori = async () => {
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('nama', currentCategory.value.nama);
    
    currentCategory.value.items.forEach((item, index) => {
      if (item.item_id) formData.append(`items[${index}][item_id]`, item.item_id);
      formData.append(`items[${index}][nama]`, item.nama);
      formData.append(`items[${index}][harga_beli]`, item.harga_beli);
      formData.append(`items[${index}][harga_jual]`, item.harga_jual);
      formData.append(`items[${index}][diskon]`, item.diskon);
      if (item.foto) {
        formData.append(`items[${index}][foto]`, item.foto);
      }
    });

    const token = sessionStorage.getItem("token");
    if (modalMode.value === 'add') {
      await axios.post("/api/admin/kategori-sampah", formData, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}`
        }
      });
      Swal.fire('Berhasil', 'Kategori sampah berhasil ditambahkan', 'success');
    } else {
      // Laravel PUT with FormData hack
      formData.append('_method', 'PUT');
      await axios.post(`/api/admin/kategori-sampah/${currentCategory.value.id}`, formData, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}`
        }
      });
      Swal.fire('Berhasil', 'Kategori sampah berhasil diperbarui', 'success');
    }
    
    isModalOpen.value = false;
    fetchKategori();
  } catch (err) {
    console.error("Failed to save category:", err);
    Swal.fire('Error', err.response?.data?.message || 'Gagal menyimpan data', 'error');
  } finally {
    loading.value = false;
  }
};

const toggleStatus = async (kategori) => {
  const newStatus = kategori.active === 1 ? 0 : 1;
  const action = newStatus === 1 ? 'mengaktifkan' : 'menonaktifkan';
  
  const result = await Swal.fire({
    title: 'Apakah anda yakin?',
    text: `Anda akan ${action} kategori ini dan semua item di dalamnya.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#4A7043',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Lanjutkan!'
  });

  if (result.isConfirmed) {
    try {
      const token = sessionStorage.getItem("token");
      await axios.put(`/api/admin/kategori-sampah/${kategori.kategori_id}/status`, { active: newStatus }, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      Swal.fire('Berhasil', `Kategori berhasil di${newStatus === 1 ? 'aktifkan' : 'nonaktifkan'}`, 'success');
      fetchKategori();
    } catch (err) {
      Swal.fire('Error', 'Gagal mengubah status', 'error');
    }
  }
};

onMounted(() => {
  fetchKategori();
});
</script>

<template>
  <DashboardLayout title="Kelola Sampah">
    <div class="space-y-6">
      <!-- Header Actions -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h2 class="text-2xl font-bold text-[#4A7043]">Kategori Sampah</h2>
          <p class="text-sm text-gray-500">Kelola kategori dan item sampah berdasarkan jenisnya</p>
        </div>
        <button 
          @click="openAddModal"
          class="bg-[#4A7043] hover:bg-[#3D5C37] text-white px-6 py-3 rounded-xl flex items-center gap-2 shadow-lg transition-all duration-300 transform hover:scale-105"
        >
          <Icon icon="material-symbols:add-box-outline" class="w-6 h-6" />
          <span class="font-bold">Tambah Kategori Sampah</span>
        </button>
      </div>

      <!-- Categories List -->
      <div v-if="loading && kategoriList.length === 0" class="flex justify-center py-20">
        <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 text-[#4A7043]" />
      </div>

      <div v-else class="space-y-4">
        <div 
          v-for="kategori in kategoriList" 
          :key="kategori.kategori_id"
          class="overflow-hidden"
        >
          <!-- Category Row -->
          <div 
            :class="[
              'rounded-2xl p-6 flex items-center justify-between transition-all duration-300 border-2',
              kategori.active === 1 
                ? 'bg-[#4A7043] text-white border-[#4A7043]' 
                : 'bg-gray-400 text-white border-gray-400'
            ]"
          >
            <div class="flex items-center gap-6 flex-1 cursor-pointer" @click="toggleAccordion(kategori.kategori_id)">
              <Icon 
                :icon="expandedCategoryId === kategori.kategori_id ? 'material-symbols:keyboard-arrow-up' : 'material-symbols:keyboard-arrow-down'" 
                class="w-8 h-8 transition-transform duration-300"
              />
              <div>
                <h3 class="text-xl font-bold tracking-wide">{{ kategori.nama }}</h3>
                <p class="text-sm opacity-80">{{ kategori.item_sampah.length }} item sampah</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <span v-if="kategori.active === 0" class="bg-red-500 text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mr-4">Nonaktif</span>
              
              <button 
                @click="openEditModal(kategori)"
                class="bg-white text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-xl flex items-center gap-2 font-bold text-sm shadow-md transition-all active:scale-95"
              >
                <Icon icon="material-symbols:edit-outline" class="w-5 h-5" />
                Edit
              </button>

              <button 
                @click="toggleStatus(kategori)"
                :class="[
                  'px-5 py-2.5 rounded-xl flex items-center gap-2 font-bold text-sm shadow-md transition-all active:scale-95',
                  kategori.active === 1 
                    ? 'bg-red-500 hover:bg-red-600 text-white' 
                    : 'bg-[#4A7043] hover:bg-[#3D5C37] text-white'
                ]"
              >
                <Icon :icon="kategori.active === 1 ? 'material-symbols:power-settings-new' : 'material-symbols:check-circle-outline'" class="w-5 h-5" />
                {{ kategori.active === 1 ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </div>
          </div>

          <!-- Items Accordion Content -->
          <transition
            enter-active-class="transition-all duration-500 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="max-h-0 opacity-0 transform -translate-y-4"
            enter-to-class="max-h-[2000px] opacity-100 transform translate-y-0"
            leave-from-class="max-h-[2000px] opacity-100 transform translate-y-0"
            leave-to-class="max-h-0 opacity-0 transform -translate-y-4"
          >
            <div v-if="expandedCategoryId === kategori.kategori_id" class="mt-2 p-6 bg-white rounded-2xl shadow-inner border border-gray-100">
              <div v-if="kategori.item_sampah.length === 0" class="text-center py-10 text-gray-400 italic">
                Belum ada item di kategori ini.
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                  v-for="item in kategori.item_sampah" 
                  :key="item.item_id"
                  class="bg-white rounded-2xl border-2 border-gray-100 p-4 shadow-sm hover:shadow-md transition-all duration-300 group"
                >
                  <div class="relative aspect-video rounded-xl overflow-hidden mb-4 bg-gray-100">
                    <img 
                      v-if="item.foto" 
                      :src="`${axios.defaults.baseURL}/storage/${item.foto}`" 
                      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                      alt="Item Foto"
                    />
                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                      <Icon icon="material-symbols:image-outline" class="w-12 h-12" />
                      <span class="text-xs font-bold uppercase tracking-widest mt-2">No Photo</span>
                    </div>
                  </div>
                  
                  <h4 class="text-lg font-bold text-gray-800 mb-4">{{ item.nama }}</h4>
                  
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-500">Harga Beli:</span>
                      <span class="font-bold text-[#4A7043]">{{ formatRupiah(item.harga_beli) }}/kg</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-500">Harga Jual:</span>
                      <span class="font-bold text-blue-600">{{ formatRupiah(item.harga_jual) }}/kg</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-500">Diskon:</span>
                      <span class="font-bold text-orange-500">{{ item.diskon * 100 }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- Multi-Step Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isModalOpen = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-[2.5rem] shadow-2xl flex flex-col">
        <!-- Modal Header -->
        <div class="bg-[#4A7043] p-8 text-white">
          <h3 class="text-2xl font-bold">{{ modalMode === 'add' ? 'Tambah Kategori Sampah Baru' : 'Edit Kategori Sampah' }}</h3>
          <p class="text-sm opacity-80 mt-1">
            Langkah {{ currentStep }}: {{ currentStep === 1 ? 'Detail Kategori' : `Tambahkan item sampah (${currentCategory.items.length} item)` }}
          </p>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
          <!-- Step 1: Category Name -->
          <div v-if="currentStep === 1" class="space-y-6 max-w-2xl mx-auto py-10">
            <div class="space-y-3">
              <label class="text-sm font-bold text-gray-700 uppercase tracking-widest ml-1">Nama Kategori *</label>
              <div class="relative group">
                <input 
                  v-model="currentCategory.nama"
                  type="text" 
                  placeholder="Contoh: Plastik, Kertas, Logam"
                  class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-5 text-lg font-semibold focus:outline-none focus:border-[#4A7043] focus:bg-white transition-all shadow-sm"
                />
                <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[#4A7043] transition-colors">
                  <Icon icon="material-symbols:edit-note" class="w-7 h-7" />
                </div>
              </div>
              <p class="text-xs text-gray-400 mt-2 italic">Pastikan nama kategori unik dan deskriptif.</p>
            </div>
          </div>

          <!-- Step 2: Items List -->
          <div v-if="currentStep === 2" class="space-y-8">
            <div class="flex justify-between items-center mb-4">
              <h4 class="text-xl font-bold text-gray-800">Daftar Item Sampah ({{ currentCategory.items.length }})</h4>
              <button 
                @click="addItem"
                class="bg-[#4CAF50]/10 text-[#4CAF50] hover:bg-[#4CAF50] hover:text-white px-4 py-2 rounded-xl flex items-center gap-2 font-bold transition-all"
              >
                <Icon icon="material-symbols:add" class="w-5 h-5" />
                Tambah Item Lagi
              </button>
            </div>

            <div class="grid grid-cols-1 gap-8">
              <div 
                v-for="(item, index) in currentCategory.items" 
                :key="index"
                class="relative p-8 rounded-3xl border-2 border-gray-100 bg-gray-50/30 space-y-6"
              >
                <!-- Badge & Remove -->
                <div class="flex justify-between items-center">
                  <span class="bg-[#4A7043] text-white px-4 py-1 rounded-lg text-xs font-bold">Item #{{ index + 1 }}</span>
                  <button 
                    v-if="currentCategory.items.length > 1"
                    @click="removeItem(index)"
                    class="text-red-500 hover:text-red-700 font-bold text-sm flex items-center gap-1"
                  >
                    <Icon icon="material-symbols:delete-forever-outline" class="w-5 h-5" />
                    Hapus Item
                  </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- Name & Prices -->
                  <div class="space-y-4">
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Item Sampah *</label>
                      <input 
                        v-model="item.nama"
                        placeholder="Contoh: Plastik PET, Kardus Tebal"
                        class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                      />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Beli (Rp/kg) *</label>
                        <input 
                          v-model="item.harga_beli"
                          type="number"
                          class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                        />
                      </div>
                      <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Jual (Rp/kg) *</label>
                        <input 
                          v-model="item.harga_jual"
                          type="number"
                          class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                        />
                      </div>
                    </div>

                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Diskon (%)</label>
                      <input 
                        v-model="item.diskon"
                        type="number"
                        placeholder="0"
                        class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                      />
                      <p class="text-[10px] text-gray-400 italic mt-1">Kosongkan atau isi 0 jika tidak ada diskon</p>
                    </div>
                  </div>

                  <!-- Photo Upload -->
                  <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Foto Item</label>
                    <div 
                      @click="$refs[`fileInput${index}`][0].click()"
                      class="relative aspect-video rounded-2xl border-2 border-dashed border-gray-200 hover:border-[#4A7043] transition-all cursor-pointer overflow-hidden flex flex-col items-center justify-center bg-white group"
                    >
                      <img v-if="item.fotoPreview" :src="item.fotoPreview" class="w-full h-full object-cover" />
                      <div v-else class="flex flex-col items-center text-gray-400 group-hover:text-[#4A7043]">
                        <Icon icon="material-symbols:cloud-upload-outline" class="w-10 h-10 mb-2" />
                        <span class="text-xs font-bold uppercase tracking-widest">Upload Foto</span>
                      </div>
                      <input 
                        :ref="`fileInput${index}`"
                        type="file" 
                        class="hidden" 
                        accept="image/*"
                        @change="(e) => handleFileUpload(e, index)"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-8 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
          <button 
            @click="isModalOpen = false"
            class="text-gray-500 hover:text-gray-700 font-bold"
          >
            Batal
          </button>
          
          <div class="flex items-center gap-4">
            <button 
              v-if="currentStep === 2"
              @click="prevStep"
              class="px-8 py-3.5 rounded-2xl border-2 border-gray-200 text-gray-600 font-bold flex items-center gap-2 hover:bg-gray-100 transition-all active:scale-95"
            >
              <Icon icon="material-symbols:arrow-back" class="w-5 h-5" />
              Kembali
            </button>
            
            <button 
              v-if="currentStep === 1"
              @click="nextStep"
              class="bg-[#4A7043] text-white px-10 py-3.5 rounded-2xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all active:scale-95"
            >
              Lanjutkan ke Item
            </button>
            
            <button 
              v-if="currentStep === 2"
              @click="saveKategori"
              :disabled="loading"
              class="bg-[#4A7043] text-white px-10 py-3.5 rounded-2xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50"
            >
              <Icon v-if="loading" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
              {{ modalMode === 'add' ? 'Tambah Kategori Sampah' : 'Simpan Perubahan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(74, 112, 67, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(74, 112, 67, 0.2);
}
</style>