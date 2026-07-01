<script setup>
import { ref, onMounted, inject, computed, watch } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from '@/utils';
import Swal from 'sweetalert2';

checkRole('admin');

const axios = inject('axios');
const kategoriList = ref([]);
const loading = ref(false);
const expandedCategoryId = ref(null);
const viewMode = ref('accordion'); // 'accordion' or 'table'
const searchQuery = ref('');

// ==========================================
// MODAL STATE KATEGORI (Multi-Step)
// ==========================================
const isModalOpen = ref(false);
const modalMode = ref('add'); // 'add' or 'edit'
const currentStep = ref(1); 
const currentCategory = ref({
  id: null,
  nama: '',
  items: [
    { nama: '', harga_beli: 0, harga_jual: 0, diskon: 0, foto: null, fotoPreview: null }
  ]
});

// ==========================================
// MODAL STATE SINGLE ITEM (Edit 1 Item)
// ==========================================
const isItemModalOpen = ref(false);
const currentItem = ref({
  item_id: null,
  nama: '',
  harga_beli: 0,
  harga_jual: 0,
  diskon: 0,
  active: 1,
  foto: null,
  fotoPreview: null
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

// --- FUNGSI MODAL KATEGORI ---
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
      diskon: item.diskon * 100,
      foto: null,
      fotoPreview: item.foto ? (item.foto.startsWith('http') ? item.foto : `${axios.defaults.baseURL}/storage/${item.foto}`) : null,
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

const prevStep = () => currentStep.value = 1;

const addItem = () => {
  currentCategory.value.items.push({
    nama: '', harga_beli: 0, harga_jual: 0, diskon: 0, foto: null, fotoPreview: null
  });
};

const removeItem = (index) => currentCategory.value.items.splice(index, 1);

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
        headers: { 'Content-Type': 'multipart/form-data', 'Authorization': `Bearer ${token}` }
      });
      Swal.fire('Berhasil', 'Kategori sampah berhasil ditambahkan', 'success');
    } else {
      formData.append('_method', 'PUT');
      await axios.post(`/api/admin/kategori-sampah/${currentCategory.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data', 'Authorization': `Bearer ${token}` }
      });
      Swal.fire('Berhasil', 'Kategori sampah berhasil diperbarui', 'success');
    }
    
    isModalOpen.value = false;
    fetchKategori();
  } catch (err) {
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

// ==========================================
// FUNGSI SINGLE ITEM (Baru)
// ==========================================
const openEditItemModal = (item) => {
  currentItem.value = {
    item_id: item.item_id,
    nama: item.nama,
    harga_beli: item.harga_beli,
    harga_jual: item.harga_jual,
    diskon: item.diskon * 100,
    active: item.active !== undefined ? item.active : 1,
    foto: null,
    fotoPreview: item.foto ? (item.foto.startsWith('http') ? item.foto : `${axios.defaults.baseURL}/storage/${item.foto}`) : null,
  };
  isItemModalOpen.value = true;
};

const handleSingleItemPhoto = (event) => {
  const file = event.target.files[0];
  if (file) {
    currentItem.value.foto = file;
    currentItem.value.fotoPreview = URL.createObjectURL(file);
  }
};

const saveSingleItem = async () => {
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('_method', 'PUT'); // Trick for Laravel PUT request with files
    formData.append('nama', currentItem.value.nama);
    formData.append('harga_beli', currentItem.value.harga_beli);
    formData.append('harga_jual', currentItem.value.harga_jual);
    formData.append('diskon', currentItem.value.diskon);
    formData.append('active', currentItem.value.active ? 1 : 0);
    if (currentItem.value.foto) {
      formData.append('foto', currentItem.value.foto);
    }

    const token = sessionStorage.getItem("token");
    await axios.post(`/api/admin/item-sampah/${currentItem.value.item_id}`, formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    });

    Swal.fire('Berhasil', 'Item sampah berhasil diperbarui', 'success');
    isItemModalOpen.value = false;
    fetchKategori(); // Refresh tampilan
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'Gagal menyimpan item', 'error');
  } finally {
    loading.value = false;
  }
};

// ==========================================
// COMPUTED FLAT ITEMS & SEARCH (Table View)
// ==========================================
const allItems = computed(() => {
  const items = [];
  kategoriList.value.forEach(kategori => {
    if (kategori.item_sampah) {
      kategori.item_sampah.forEach(item => {
        items.push({
          ...item,
          kategori_nama: kategori.nama,
          kategori_id: kategori.kategori_id,
          kategori_active: kategori.active
        });
      });
    }
  });
  return items;
});

const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return allItems.value;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return allItems.value.filter(item => {
    return (
      item.nama.toLowerCase().includes(query) ||
      item.kategori_nama.toLowerCase().includes(query)
    );
  });
});

const currentPage = ref(1);
const itemsPerPage = 10;

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredItems.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredItems.value.length / itemsPerPage);
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

onMounted(() => {
  fetchKategori();
});
</script>

<template>
  <DashboardLayout title="Kelola Sampah">
    <div class="space-y-6">
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
          <span class="font-bold">Tambah Kategori</span>
        </button>
      </div>

      <div v-if="loading && kategoriList.length === 0" class="flex justify-center py-20">
        <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 text-[#4A7043]" />
      </div>

      <div v-else class="space-y-6">
        <!-- Controls: Toggle View & Search Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
          <!-- View Toggle Buttons -->
          <div class="flex items-center bg-gray-100 p-1 rounded-xl w-fit">
            <button 
              type="button"
              @click="viewMode = 'accordion'"
              :class="[
                'px-4 py-2 rounded-lg font-bold text-sm transition-all flex items-center gap-2 cursor-pointer',
                viewMode === 'accordion' 
                  ? 'bg-[#4A7043] text-white shadow-sm' 
                  : 'text-gray-500 hover:text-[#4A7043]'
              ]"
            >
              <Icon icon="material-symbols:format-list-bulleted" class="w-5 h-5" />
              Grup Kategori
            </button>
            <button 
              type="button"
              @click="viewMode = 'table'"
              :class="[
                'px-4 py-2 rounded-lg font-bold text-sm transition-all flex items-center gap-2 cursor-pointer',
                viewMode === 'table' 
                  ? 'bg-[#4A7043] text-white shadow-sm' 
                  : 'text-gray-500 hover:text-[#4A7043]'
              ]"
            >
              <Icon icon="material-symbols:table-chart-outline-rounded" class="w-5 h-5" />
              Semua Item (Tabel)
            </button>
          </div>

          <!-- Search Bar -->
          <div v-if="viewMode === 'table'" class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
              <Icon icon="material-symbols:search" class="w-5 h-5" />
            </span>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari item sampah atau kategori..."
              class="w-full bg-gray-50 border border-gray-100 rounded-xl pl-12 pr-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-all placeholder:text-gray-400"
            />
            <button 
              v-if="searchQuery" 
              type="button"
              @click="searchQuery = ''"
              class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-gray-600"
            >
              <Icon icon="material-symbols:close" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Accordion View -->
        <div v-if="viewMode === 'accordion'" class="space-y-4">
          <div 
            v-for="kategori in kategoriList" 
            :key="kategori.kategori_id"
            class="overflow-hidden"
          >
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
                  Edit Semua
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
                    @click="openEditItemModal(item)"
                    class="bg-white rounded-2xl border-2 border-gray-100 p-4 shadow-sm hover:shadow-lg hover:border-[#4A7043] transition-all duration-300 group cursor-pointer relative"
                  >
                    <div v-if="item.active === 0" class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-md z-10">
                      NONAKTIF
                    </div>

                    <div class="relative aspect-video rounded-xl overflow-hidden mb-4 bg-gray-100">
                      <img 
                        v-if="item.foto" 
                        :src="item.foto.startsWith('http') ? item.foto : `${axios.defaults.baseURL}/storage/${item.foto}`" 
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        alt="Item Foto"
                      />
                      <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                        <Icon icon="material-symbols:image-outline" class="w-12 h-12" />
                        <span class="text-xs font-bold uppercase tracking-widest mt-2">No Photo</span>
                      </div>
                      
                      <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <div class="bg-white text-gray-800 px-4 py-2 rounded-full font-bold text-sm flex items-center gap-2">
                          <Icon icon="material-symbols:edit" class="w-4 h-4" /> Edit
                        </div>
                      </div>
                    </div>
                    
                    <h4 class="text-lg font-bold text-gray-800 mb-4 truncate">{{ item.nama }}</h4>
                    
                    <div class="space-y-2 text-sm">
    
                      <div class="flex justify-between items-center">
                        <span class="text-gray-500">Harga Beli:</span>
                        <span class="font-bold text-[#4A7043]">{{ formatRupiah(item.harga_beli) }}/kg</span>
                      </div>

                      <div class="flex justify-between items-center">
                        <span class="text-gray-500">Harga Jual:</span>
                        <div class="text-right">
                          <span v-if="item.diskon > 0" class="text-xs text-gray-400 line-through mr-1 font-normal">
                            {{ formatRupiah(item.harga_jual) }}
                          </span>
                          <span class="font-bold text-blue-600">
                            {{ formatRupiah(item.harga_jual - (item.harga_jual * item.diskon)) }}/kg
                          </span>
                        </div>
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

        <!-- Table View -->
        <div v-else-if="viewMode === 'table'" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs font-bold uppercase tracking-wider">
                  <th class="py-5 px-6">No</th>
                  <th class="py-5 px-6">Foto</th>
                  <th class="py-5 px-6">Nama Item</th>
                  <th class="py-5 px-6">Kategori</th>
                  <th class="py-5 px-6">Harga Beli</th>
                  <th class="py-5 px-6">Harga Jual Bersih</th>
                  <th class="py-5 px-6">Diskon</th>
                  <th class="py-5 px-6">Status</th>
                  <th class="py-5 px-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                <tr v-if="paginatedItems.length === 0">
                  <td colspan="9" class="py-12 text-center text-gray-400 italic">
                    Tidak ditemukan item sampah yang cocok.
                  </td>
                </tr>
                <tr 
                  v-for="(item, index) in paginatedItems" 
                  :key="item.item_id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="py-4 px-6 font-medium text-gray-400">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                  <td class="py-4 px-6">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 border border-gray-100 flex items-center justify-center shrink-0">
                      <img 
                        v-if="item.foto" 
                        :src="item.foto.startsWith('http') ? item.foto : `${axios.defaults.baseURL}/storage/${item.foto}`" 
                        class="w-full h-full object-cover"
                        alt="Foto Item"
                      />
                      <Icon v-else icon="material-symbols:image-outline" class="w-6 h-6 text-gray-300" />
                    </div>
                  </td>
                  <td class="py-4 px-6 font-bold text-gray-800">{{ item.nama }}</td>
                  <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#4A7043]/10 text-[#4A7043]">
                      {{ item.kategori_nama }}
                    </span>
                  </td>
                  <td class="py-4 px-6 font-bold text-[#4A7043]">{{ formatRupiah(item.harga_beli) }}/kg</td>
                  <td class="py-4 px-6">
                    <div class="flex flex-col">
                      <span class="font-bold text-blue-600">
                        {{ formatRupiah(item.harga_jual - (item.harga_jual * item.diskon)) }}/kg
                      </span>
                      <span v-if="item.diskon > 0" class="text-xs text-gray-400 line-through">
                        {{ formatRupiah(item.harga_jual) }}
                      </span>
                    </div>
                  </td>
                  <td class="py-4 px-6 font-bold text-orange-500">
                    {{ item.diskon * 100 }}%
                  </td>
                  <td class="py-4 px-6">
                    <span 
                      :class="[
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold',
                        item.active === 1 
                          ? 'bg-green-100 text-green-700' 
                          : 'bg-red-100 text-red-700'
                      ]"
                    >
                      {{ item.active === 1 ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <button 
                      @click="openEditItemModal(item)"
                      class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-600 hover:bg-[#4A7043] hover:text-white transition-all active:scale-95 shadow-sm hover:shadow-md cursor-pointer"
                      title="Edit Item"
                    >
                      <Icon icon="material-symbols:edit-outline" class="w-5 h-5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-100 px-6 py-4 bg-gray-50/50 gap-4">
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">
              Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredItems.length) }} dari {{ filteredItems.length }} item
            </div>
            <div class="flex items-center gap-2">
              <button 
                type="button"
                @click="currentPage > 1 && (currentPage--)"
                :disabled="currentPage === 1"
                class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all font-bold text-xs flex items-center gap-1 cursor-pointer"
              >
                <Icon icon="material-symbols:arrow-back-ios-new-rounded" class="w-3 h-3" />
                Sebelumnya
              </button>
              <button 
                v-for="page in totalPages" 
                :key="page"
                type="button"
                @click="currentPage = page"
                :class="[
                  'w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer border',
                  currentPage === page 
                    ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-md shadow-green-900/10' 
                    : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
              <button 
                type="button"
                @click="currentPage < totalPages && (currentPage++)"
                :disabled="currentPage === totalPages"
                class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all font-bold text-xs flex items-center gap-1 cursor-pointer"
              >
                Berikutnya
                <Icon icon="material-symbols:arrow-forward-ios-rounded" class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isItemModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isItemModalOpen = false"></div>
      
      <div class="relative bg-white w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-[2.5rem] shadow-2xl flex flex-col">
        <div class="bg-[#4A7043] p-8 text-white">
          <h3 class="text-2xl font-bold">Edit Item Sampah</h3>
          <p class="text-sm opacity-80 mt-1">Perbarui detail, harga, atau foto untuk item ini</p>
        </div>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar bg-gray-50/30">
          <div class="space-y-6">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Item Sampah *</label>
              <input 
                v-model="currentItem.nama"
                type="text" 
                class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Beli (Rp) *</label>
                <input 
                  v-model="currentItem.harga_beli"
                  type="number"
                  class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Jual (Rp) *</label>
                <input 
                  v-model="currentItem.harga_jual"
                  type="number"
                  class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Diskon (%)</label>
                <input 
                  v-model="currentItem.diskon"
                  type="number"
                  class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                />
              </div>
              
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status</label>
                <select 
                  v-model="currentItem.active"
                  class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                >
                  <option :value="1">Aktif</option>
                  <option :value="0">Nonaktif</option>
                </select>
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Foto Item (Opsional)</label>
              <div 
                @click="$refs.singleFileInput.click()"
                class="relative aspect-video rounded-2xl border-2 border-dashed border-gray-300 hover:border-[#4A7043] transition-all cursor-pointer overflow-hidden flex flex-col items-center justify-center bg-white group"
              >
                <img v-if="currentItem.fotoPreview" :src="currentItem.fotoPreview" class="w-full h-full object-cover" />
                <div v-else class="flex flex-col items-center text-gray-400 group-hover:text-[#4A7043]">
                  <Icon icon="material-symbols:cloud-upload-outline" class="w-10 h-10 mb-2" />
                  <span class="text-xs font-bold uppercase tracking-widest">Klik untuk Ubah Foto</span>
                </div>
                <input 
                  ref="singleFileInput"
                  type="file" 
                  class="hidden" 
                  accept="image/*"
                  @change="handleSingleItemPhoto"
                />
              </div>
            </div>

          </div>
        </div>

        <div class="p-6 border-t border-gray-100 flex items-center justify-end gap-4 bg-white">
          <button 
            @click="isItemModalOpen = false"
            class="px-6 py-3 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-all"
          >
            Batal
          </button>
          <button 
            @click="saveSingleItem"
            :disabled="loading"
            class="bg-[#4A7043] hover:bg-[#3D5C37] text-white px-8 py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center gap-2"
          >
            <Icon v-if="loading" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isModalOpen = false"></div>
      
      <div class="relative bg-white w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-[2.5rem] shadow-2xl flex flex-col">
        <div class="bg-[#4A7043] p-8 text-white">
          <h3 class="text-2xl font-bold">{{ modalMode === 'add' ? 'Tambah Kategori Sampah Baru' : 'Edit Kategori Sampah' }}</h3>
          <p class="text-sm opacity-80 mt-1">
            Langkah {{ currentStep }}: {{ currentStep === 1 ? 'Detail Kategori' : `Tambahkan item sampah (${currentCategory.items.length} item)` }}
          </p>
        </div>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
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
                  <div class="space-y-4">
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Item Sampah *</label>
                      <input 
                        v-model="item.nama"
                        placeholder="Contoh: Plastik PET"
                        class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
                      />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Beli (Rp/kg) *</label>
                        <input v-model="item.harga_beli" type="number" class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all" />
                      </div>
                      <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Harga Jual (Rp/kg) *</label>
                        <input v-model="item.harga_jual" type="number" class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all" />
                      </div>
                    </div>

                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Diskon (%)</label>
                      <input v-model="item.diskon" type="number" placeholder="0" class="w-full bg-white border border-gray-200 rounded-xl p-3.5 focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all" />
                    </div>
                  </div>

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
                      <input :ref="`fileInput${index}`" type="file" class="hidden" accept="image/*" @change="(e) => handleFileUpload(e, index)" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-8 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
          <button @click="isModalOpen = false" class="text-gray-500 hover:text-gray-700 font-bold">Batal</button>
          <div class="flex items-center gap-4">
            <button v-if="currentStep === 2" @click="prevStep" class="px-8 py-3.5 rounded-2xl border-2 border-gray-200 text-gray-600 font-bold flex items-center gap-2 hover:bg-gray-100 transition-all active:scale-95">
              <Icon icon="material-symbols:arrow-back" class="w-5 h-5" /> Kembali
            </button>
            <button v-if="currentStep === 1" @click="nextStep" class="bg-[#4A7043] text-white px-10 py-3.5 rounded-2xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all active:scale-95">
              Lanjutkan ke Item
            </button>
            <button v-if="currentStep === 2" @click="saveKategori" :disabled="loading" class="bg-[#4A7043] text-white px-10 py-3.5 rounded-2xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50">
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