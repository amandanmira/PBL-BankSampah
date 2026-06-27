<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import { checkRole } from "@/utils";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import Swal from "sweetalert2";

checkRole("admin");

const router = useRouter();
const axios = inject("axios");

const gudangList = ref([]);
const isLoading = ref(true);
const expandedId = ref(null);

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentGudangId = ref(null);
const selectedGudang = ref(null);
const formData = ref({
  alamat: "",
  kapasitas: "",
});

// Manage Sampah Modal state
const isManageSampahModalOpen = ref(false);
const itemSampahList = ref([]);
const selectedCategory = ref(null);
const managedSampah = ref([]);
const isSubmittingSampah = ref(false);

// Manage Tukang Modal state
const isManageTukangModalOpen = ref(false);
const managedTukang = ref([]);
const isSubmittingTukang = ref(false);

const toggleAccordion = (id) => {
  expandedId.value = expandedId.value === id ? null : id;
};

const fetchGudang = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get("/api/admin/gudang");
    gudangList.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch warehouses:", err);
    Swal.fire("Error", "Gagal memuat data gudang", "error");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchGudang();
});

const openAddModal = () => {
  isEditing.value = false;
  currentGudangId.value = null;
  formData.value = { alamat: "", kapasitas: "" };
  isModalOpen.value = true;
};

const openEditModal = (gudang) => {
  isEditing.value = true;
  currentGudangId.value = gudang.gudang_id;
  formData.value = { 
    alamat: gudang.alamat, 
    kapasitas: gudang.kapasitas 
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

// --- FRONTEND VALIDATION & SUBMIT ---
const handleSubmit = async () => {
  const inputAlamat = formData.value.alamat.trim();

  // 1. Validasi Kosong
  if (!inputAlamat) {
    Swal.fire("Error", "Alamat gudang harus diisi", "error");
    return;
  }

  // 2. Validasi Unique (Mencegah Duplikat)
  const isDuplicate = gudangList.value.some(gudang => {
    // Jika sedang mode 'edit', abaikan alamat gudang itu sendiri
    if (isEditing.value && Number(gudang.gudang_id) === Number(currentGudangId.value)) {
      return false;
    }
    // Pengecekan case-insensitive (huruf besar/kecil tidak ngaruh)
    return gudang.alamat.toLowerCase() === inputAlamat.toLowerCase();
  });

  if (isDuplicate) {
    Swal.fire({
      title: "Alamat Sudah Ada!",
      text: `Gudang dengan alamat "${inputAlamat}" sudah terdaftar. Silakan gunakan alamat yang berbeda.`,
      icon: "warning",
      confirmButtonColor: "#4A7043"
    });
    return;
  }

  // 3. Eksekusi API
  try {
    if (isEditing.value) {
      await axios.put(`/api/admin/gudang/${currentGudangId.value}`, formData.value);
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Data gudang berhasil diperbarui",
        timer: 1500,
        showConfirmButton: false,
      });
    } else {
      await axios.post("/api/admin/gudang", formData.value);
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Gudang baru berhasil ditambahkan",
        timer: 1500,
        showConfirmButton: false,
      });
    }
    closeModal();
    fetchGudang();
  } catch (err) {
    console.error("Submit failed:", err);
    // Menangkap pesan error dari validasi backend jika masih tembus
    const errorMsg = err.response?.data?.message || err.response?.data?.errors?.alamat?.[0] || "Gagal menyimpan data gudang";
    Swal.fire("Error", errorMsg, "error");
  }
};

const toggleStatus = async (gudang) => {
  const newStatus = Number(gudang.active) === 1 ? 0 : 1;
  const actionText = newStatus === 1 ? "mengaktifkan" : "menonaktifkan";

  const result = await Swal.fire({
    title: "Apakah anda yakin?",
    text: `Anda akan ${actionText} gudang ini.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: newStatus === 1 ? "#4A7043" : "#EF4444",
    cancelButtonColor: "#6B7280",
    confirmButtonText: "Ya, Lanjutkan!",
    cancelButtonText: "Batal",
  });

  if (result.isConfirmed) {
    try {
      await axios.put(`/api/admin/gudang/${gudang.gudang_id}/status`, {
        active: newStatus,
      });
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: `Gudang berhasil di${actionText === "mengaktifkan" ? "aktifkan" : "nonaktifkan"}`,
        timer: 1500,
        showConfirmButton: false,
      });
      fetchGudang();
    } catch (err) {
      console.error("Status update failed:", err);
      Swal.fire("Error", "Gagal memperbarui status gudang", "error");
    }
  }
};

// Manage Sampah Logic
const openManageSampah = async (gudang) => {
  selectedGudang.value = gudang;
  managedSampah.value = JSON.parse(JSON.stringify(gudang.sampah || []));
  selectedCategory.value = null;
  
  try {
    const res = await axios.get("/api/admin/kategori-sampah");
    itemSampahList.value = res.data.filter(c => Number(c.active) === 1);
    isManageSampahModalOpen.value = true;
  } catch (err) {
    console.error("Failed to fetch categories:", err);
  }
};

const selectCategory = (category) => {
  selectedCategory.value = category;
  // Initialize missing items in managedSampah for this category
  category.item_sampah.forEach(item => {
    if (!managedSampah.value.find(s => Number(s.item_id) === Number(item.item_id))) {
      managedSampah.value.push({
        item_id: item.item_id,
        stok: 0,
        active: 1,
        item_sampah: item
      });
    }
  });
};

const totalAllocated = computed(() => {
  return managedSampah.value.reduce((sum, s) => sum + (parseFloat(s.stok) || 0), 0);
});

const remainingCapacity = computed(() => {
  if (!selectedGudang.value) return 0;
  return selectedGudang.value.kapasitas - totalAllocated.value;
});

const isOverCapacity = computed(() => remainingCapacity.value < 0);
const isLowCapacity = computed(() => {
  if (!selectedGudang.value) return false;
  return remainingCapacity.value <= selectedGudang.value.kapasitas * 0.1 && remainingCapacity.value >= 0;
});

const submitSampah = async () => {
  if (isOverCapacity.value) return;
  
  isSubmittingSampah.value = true;
  try {
    const payload = {
      sampah: managedSampah.value.map(s => ({
        item_id: s.item_id,
        stok: s.stok === "" || s.stok === null ? 0 : parseFloat(s.stok),
        active: 1
      }))
    };
    await axios.put(`/api/admin/gudang/sampah/${selectedGudang.value.gudang_id}`, payload);
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Stok gudang berhasil diperbarui",
      timer: 1500,
      showConfirmButton: false,
    });
    isManageSampahModalOpen.value = false;
    fetchGudang();
  } catch (err) {
    console.error("Submit sampah failed:", err);
    Swal.fire("Error", "Gagal memperbarui stok sampah", "error");
  } finally {
    isSubmittingSampah.value = false;
  }
};

// Manage Tukang Logic
const openManageTukang = (gudang) => {
  selectedGudang.value = gudang;
  managedTukang.value = JSON.parse(JSON.stringify(gudang.tukang || [])).map(t => ({
    ...t,
    checkbox: Number(t.active) === 1,
    foto_baru: null,
    preview_url: t.foto ? `${axios.defaults.baseURL}/storage/${t.foto}` : null
  }));
  isManageTukangModalOpen.value = true;
};

const addTukangField = () => {
  managedTukang.value.push({
    nama: "",
    no_telp: "",
    foto_baru: null,
    preview_url: null,
    checkbox: true,
    active: 1
  });
};

const removeTukangField = async (index) => {
  const tukang = managedTukang.value[index];
  
  if (tukang.tukang_id) {
    const result = await Swal.fire({
      title: "Hapus Tukang?",
      text: "Data tukang akan dihapus permanen dari database.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#EF4444",
      cancelButtonColor: "#6B7280",
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/admin/tukang/${tukang.tukang_id}`);
        managedTukang.value.splice(index, 1);
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: "Tukang berhasil dihapus",
          timer: 1500,
          showConfirmButton: false,
        });
        fetchGudang();
      } catch (err) {
        console.error("Delete tukang failed:", err);
        Swal.fire("Error", "Gagal menghapus tukang", "error");
      }
    }
  } else {
    managedTukang.value.splice(index, 1);
  }
};

const handleTukangFile = (e, index) => {
  const file = e.target.files[0];
  if (file) {
    managedTukang.value[index].foto_baru = file;
    managedTukang.value[index].preview_url = URL.createObjectURL(file);
  }
};

const submitTukang = async () => {
  // Validasi Kelengkapan Data
  const isIncomplete = managedTukang.value.some(t => {
    return !t.nama || String(t.nama).trim() === '' || !t.no_telp || String(t.no_telp).trim() === '';
  });

  if (isIncomplete) {
    Swal.fire({
      title: "Data Tidak Lengkap!",
      text: "Pastikan semua nama dan nomor telepon tukang telah diisi dengan benar.",
      icon: "warning",
      confirmButtonColor: "#4A7043"
    });
    return;
  }

  isSubmittingTukang.value = true;
  const formData = new FormData();
  
  managedTukang.value.forEach((t, i) => {
    if (t.tukang_id) formData.append(`tukang[${i}][tukang_id]`, t.tukang_id);
    formData.append(`tukang[${i}][nama]`, t.nama);
    formData.append(`tukang[${i}][no_telp]`, t.no_telp);
    formData.append(`tukang[${i}][active]`, t.checkbox ? 1 : 0);
    if (t.foto_baru) formData.append(`tukang[${i}][foto]`, t.foto_baru);
  });

  try {
    await axios.post(`/api/admin/tukang/${selectedGudang.value.gudang_id}?_method=PUT`, formData);
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Data tukang berhasil diperbarui",
      timer: 1500,
      showConfirmButton: false,
    });
    isManageTukangModalOpen.value = false;
    fetchGudang();
  } catch (err) {
    console.error("Submit tukang failed:", err);
  } finally {
    isSubmittingTukang.value = false;
  }
};
</script>

<template>
  <DashboardLayout title="Kelola Gudang">
    <div class="space-y-6">
      <!-- Header Action -->
      <div class="flex justify-end">
        <button
          @click="openAddModal"
          class="bg-[#4A7043] hover:bg-[#3d5c37] text-white px-6 py-3 rounded-2xl flex items-center gap-2 font-bold shadow-lg transition-all transform hover:scale-105"
        >
          <Icon icon="material-symbols:add" class="w-6 h-6" />
          Tambah Gudang
        </button>
      </div>

      <!-- Warehouse List -->
      <div v-if="isLoading" class="flex justify-center py-20">
        <Icon icon="eos-icons:loading" class="w-12 h-12 text-[#4A7043]" />
      </div>

      <div v-else-if="gudangList.length === 0" class="bg-white rounded-[2rem] p-12 text-center shadow-sm border border-gray-100">
        <Icon icon="material-symbols:home-work-outline" class="w-20 h-20 text-gray-300 mx-auto mb-4" />
        <h3 class="text-xl font-bold text-gray-800">Belum Ada Gudang</h3>
        <p class="text-gray-500 mt-2">Silahkan tambahkan gudang baru untuk mulai mengelola.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="gudang in gudangList"
          :key="gudang.gudang_id"
          class="overflow-hidden transition-all duration-300"
        >
          <!-- Warehouse Card -->
          <div
            :class="[
              'rounded-[2rem] p-6 flex flex-col transition-all duration-300 shadow-md',
              Number(gudang.active) === 1 ? 'bg-[#4A7043] text-white' : 'bg-[#D1D5DB] text-gray-600'
            ]"
          >
            <!-- Card Header -->
            <div @click="toggleAccordion(gudang.gudang_id)" class="flex items-center gap-4 cursor-pointer">
              <!-- Expand Toggle -->
              <button
                class="hover:bg-black/10 rounded-full p-1 transition-transform duration-300"
                :style="{ transform: expandedId === gudang.gudang_id ? 'rotate(180deg)' : 'rotate(0deg)' }"
              >
                <Icon icon="material-symbols:keyboard-arrow-down" class="w-8 h-8" />
              </button>

              <!-- Warehouse Icon -->
              <div 
                :class="[
                  'w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 border-2',
                  Number(gudang.active) === 1 ? 'bg-white/10 border-white/20' : 'bg-gray-200 border-gray-300'
                ]"
              >
                <Icon icon="material-symbols:home-work-outline" class="w-8 h-8" />
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <h3 class="text-lg lg:text-xl font-black truncate">{{ gudang.alamat }}</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs lg:text-sm font-medium opacity-80">
                  <span class="flex items-center gap-1">Stok: {{ Math.round(gudang.sampah?.reduce((sum, s) => sum + (parseFloat(s.stok) || 0), 0) || 0) }} / {{ gudang.kapasitas }} kg</span>
                  <span class="hidden sm:inline">•</span>
                  <span class="flex items-center gap-1">{{ gudang.tukang?.length || 0 }} Tukang</span>
                  <span class="hidden sm:inline">•</span>
                  <span class="flex items-center gap-1">{{ gudang.sampah?.filter(s => parseFloat(s.stok) > 0).length || 0 }} Jenis Stok</span>
                </div>
              </div>

              <!-- Status Badge (Only if inactive) -->
              <div v-if="Number(gudang.active) === 0" class="hidden md:block bg-red-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                Nonaktif
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-3">
                <button
                  @click.stop="openEditModal(gudang)"
                  class="bg-white text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-xl flex items-center gap-2 font-bold text-sm shadow-sm transition-all"
                >
                  <Icon icon="material-symbols:edit-square-outline" class="w-5 h-5 text-gray-400" />
                  Edit
                </button>
                <button
                  @click.stop="toggleStatus(gudang)"
                  :class="[
                    'px-4 py-2 rounded-xl flex items-center gap-2 font-bold text-sm shadow-sm transition-all',
                    Number(gudang.active) === 1 
                      ? 'bg-[#EF4444] hover:bg-red-600 text-white' 
                      : 'bg-white text-[#4A7043] hover:bg-gray-50'
                  ]"
                >
                  <Icon 
                    :icon="Number(gudang.active) === 1 ? 'material-symbols:power-settings-new' : 'material-symbols:power-settings-new'" 
                    class="w-5 h-5" 
                  />
                  {{ Number(gudang.active) === 1 ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </div>
            </div>

            <!-- Expanded Content -->
            <div
              v-show="expandedId === gudang.gudang_id"
              class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 transition-all duration-300"
            >
              <!-- Manage Gudang Card -->
              <button
                @click="openManageSampah(gudang)"
                class="bg-white/10 hover:bg-white/15 border border-white/10 rounded-[2rem] p-8 flex items-center gap-6 text-left group transition-all"
              >
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Icon icon="material-symbols:inventory-2-outline" class="w-8 h-8" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xl font-black">Manage Gudang</h4>
                    <span class="bg-white text-[#4A7043] px-4 py-1 rounded-full text-xs font-bold">{{ gudang.sampah?.length || 0 }} Item</span>
                  </div>
                  <p class="text-sm opacity-70 mt-1">Kelola stok sampah per kategori</p>
                </div>
              </button>

              <!-- Manage Tukang Card -->
              <button
                @click="openManageTukang(gudang)"
                class="bg-[#3B8A8A] hover:bg-[#347878] border border-white/10 rounded-[2rem] p-8 flex items-center gap-6 text-left group transition-all"
              >
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Icon icon="material-symbols:group-outline" class="w-8 h-8" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xl font-black text-white">Manage Tukang</h4>
                    <span class="bg-white text-[#3B8A8A] px-4 py-1 rounded-full text-xs font-bold">{{ gudang.tukang?.length || 0 }} Tukang</span>
                  </div>
                  <p class="text-sm text-white/70 mt-1">Kelola daftar tukang gudang</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Tambah/Edit Gudang) -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center px-4 overflow-y-auto">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
      
      <div class="relative bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Modal Header -->
        <div class="bg-[#4A7043] p-8 flex items-center justify-between text-white">
          <h2 class="text-2xl font-black tracking-tight">{{ isEditing ? 'Edit Data Gudang' : 'Tambah Gudang Baru' }}</h2>
          <button @click="closeModal" class="hover:bg-white/10 p-2 rounded-full transition-colors">
            <Icon icon="material-symbols:close" class="w-8 h-8" />
          </button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="handleSubmit" class="p-8 space-y-8">
          <!-- Alamat -->
          <div class="space-y-3">
            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest px-1">Alamat Gudang *</label>
            <textarea
              v-model="formData.alamat"
              required
              rows="4"
              placeholder="Masukkan alamat lengkap"
              class="w-full bg-gray-50 border-2 border-gray-100 rounded-[1.5rem] p-5 text-gray-800 font-medium focus:border-[#4A7043] focus:ring-0 transition-all resize-none"
            ></textarea>
          </div>

          <!-- Kapasitas -->
          <div class="space-y-3">
            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest px-1">Kapasitas (kg) *</label>
            <input
              v-model.number="formData.kapasitas"
              type="number"
              required
              placeholder="500"
              class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-5 text-gray-800 font-medium focus:border-[#4A7043] focus:ring-0 transition-all"
            />
          </div>

          <!-- Footer Actions -->
          <div class="flex gap-4 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 rounded-2xl shadow-lg transition-all"
            >
              Batal
            </button>
            <button
              type="submit"
              class="flex-1 bg-[#4A7043] hover:bg-[#3d5c37] text-white font-bold py-4 rounded-2xl shadow-lg transition-all"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Gudang' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Manage Sampah -->
    <div v-if="isManageSampahModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center px-4 overflow-y-auto">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="isManageSampahModalOpen = false"></div>
      
      <div class="relative bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="bg-[#4A7043] p-8 text-white">
          <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black tracking-tight">Manage Gudang</h2>
            <button @click="isManageSampahModalOpen = false" class="hover:bg-white/10 p-2 rounded-full transition-colors">
              <Icon icon="material-symbols:close" class="w-8 h-8" />
            </button>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mt-2 gap-2">
            <p class="text-sm opacity-90 font-medium">Gudang: {{ selectedGudang?.alamat }}</p>
            <p class="text-sm font-black bg-white/10 px-3 py-1 rounded-full w-fit">
              Stok: {{ Math.round(totalAllocated) }} / {{ selectedGudang?.kapasitas }} kg
            </p>
          </div>
        </div>

        <!-- Body -->
        <div class="p-8 space-y-8">
          <!-- Category List -->
          <div>
            <h3 class="text-lg font-black text-gray-800 mb-4">Pilih Kategori Sampah</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <button
                v-for="cat in itemSampahList"
                :key="cat.kategori_id"
                @click="selectCategory(cat)"
                :class="[
                  'p-6 rounded-3xl border-2 transition-all text-center group',
                  Number(selectedCategory?.kategori_id) === Number(cat.kategori_id)
                    ? 'bg-[#4A7043] border-[#4A7043] text-white shadow-lg'
                    : 'bg-gray-50 border-gray-100 text-gray-500 hover:border-[#4A7043]/30 hover:bg-white'
                ]"
              >
                <p class="font-black text-lg">{{ cat.nama }}</p>
                <p :class="['text-xs mt-1 font-bold', Number(selectedCategory?.kategori_id) === Number(cat.kategori_id) ? 'text-white/70' : 'text-gray-400']">
                  {{ cat.item_sampah?.length || 0 }} item
                </p>
              </button>
            </div>
          </div>

          <!-- Item Config (Only if category selected) -->
          <div v-if="selectedCategory" class="space-y-6">
            <h3 class="text-lg font-black text-gray-800">Atur Stok Item</h3>
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
              <div
                v-for="item in selectedCategory.item_sampah"
                :key="item.item_id"
                class="bg-gray-50 rounded-3xl p-6 border-2 border-transparent hover:border-[#4A7043]/20 transition-all"
              >
                <div class="flex items-center gap-6">
                  <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-200 shrink-0">
                    <img v-if="item.foto" :src="`${axios.defaults.baseURL}/storage/${item.foto}`" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                      <Icon icon="material-symbols:image-outline" class="w-8 h-8" />
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="font-black text-gray-800">{{ item.nama }}</p>
                    <p class="text-xs font-bold text-gray-400">Harga Jual: Rp {{ (item.harga_jual || 0).toLocaleString() }}/kg</p>
                  </div>
                  <div class="w-40 relative">
                    <input
                      type="number"
                      v-model.number="managedSampah.find(s => Number(s.item_id) === Number(item.item_id)).stok"
                      placeholder="0"
                      class="w-full bg-white border-2 border-gray-100 rounded-2xl p-4 pr-10 text-right font-black focus:border-[#4A7043] focus:ring-0 transition-all"
                    />
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-300">kg</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Capacity Notifications -->
            <div class="space-y-2">
              <div v-if="isLowCapacity" class="bg-yellow-50 border-2 border-yellow-100 p-6 rounded-3xl flex items-center gap-4 animate-pulse">
                <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center text-white shrink-0">
                  <Icon icon="material-symbols:lightbulb-outline" class="w-6 h-6" />
                </div>
                <p class="text-sm font-bold text-yellow-800">
                  <span class="font-black">Info:</span> Kapasitas tersisa {{ remainingCapacity }} kg ({{ ((remainingCapacity / selectedGudang?.kapasitas) * 100).toFixed(1) }}%). Segera kelola stok Anda.
                </p>
              </div>

              <div v-if="isOverCapacity" class="bg-red-50 border-2 border-red-100 p-6 rounded-3xl flex items-center gap-4">
                <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center text-white shrink-0">
                  <Icon icon="material-symbols:error-outline" class="w-6 h-6" />
                </div>
                <p class="text-sm font-bold text-red-800">
                  <span class="font-black">Peringatan:</span> Alokasi stok melebihi kapasitas gudang sebesar 
                  <span class="font-black underline">{{ Math.abs(remainingCapacity) }} kg</span>. Harap sesuaikan kembali.
                </p>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex gap-4 pt-4">
            <button
              @click="isManageSampahModalOpen = false"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 rounded-2xl shadow-lg transition-all"
            >
              Batal
            </button>
            <button
              @click="submitSampah"
              :disabled="isSubmittingSampah || isOverCapacity || !selectedCategory"
              class="flex-1 bg-[#4A7043] hover:bg-[#3d5c37] text-white font-bold py-4 rounded-2xl shadow-lg transition-all disabled:opacity-50"
            >
              <div class="flex items-center justify-center gap-2">
                <Icon v-if="isSubmittingSampah" icon="eos-icons:loading" class="w-5 h-5" />
                Simpan Stok
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Manage Tukang -->
    <div v-if="isManageTukangModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center px-4 overflow-y-auto">
      <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="isManageTukangModalOpen = false"></div>
      
      <div class="relative bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="bg-[#3B8A8A] p-8 text-white">
          <div class="flex items-center justify-between">
            <h2 class="text-2xl font-black tracking-tight">Manage Tukang</h2>
            <button @click="isManageTukangModalOpen = false" class="hover:bg-white/10 p-2 rounded-full transition-colors">
              <Icon icon="material-symbols:close" class="w-8 h-8" />
            </button>
          </div>
          <p class="text-sm opacity-80 mt-1">Gudang: {{ selectedGudang?.alamat }}</p>
        </div>

        <!-- Body -->
        <div class="p-8 space-y-6">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-800">Daftar Tukang ({{ managedTukang.length }})</h3>
            <button
              @click="addTukangField"
              class="bg-[#3B8A8A] hover:bg-[#347878] text-white px-4 py-2 rounded-xl flex items-center gap-2 font-bold text-sm shadow-md transition-all"
            >
              <Icon icon="material-symbols:person-add-outline" class="w-5 h-5" />
              Tambah Tukang
            </button>
          </div>

          <div v-if="managedTukang.length === 0" class="py-20 text-center bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-200">
            <Icon icon="material-symbols:group-outline" class="w-20 h-20 text-gray-200 mx-auto mb-4" />
            <p class="text-gray-500 font-bold">Belum ada tukang</p>
            <p class="text-xs text-gray-400">Klik "Tambah Tukang" untuk menambahkan</p>
          </div>

          <div v-else class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
            <div
              v-for="(t, index) in managedTukang"
              :key="t.tukang_id || index"
              class="bg-white rounded-[2rem] p-8 border-2 border-gray-100 shadow-sm relative group"
            >
              <button
                @click="removeTukangField(index)"
                class="absolute top-6 right-8 flex items-center gap-1 text-red-400 hover:text-red-600 font-black text-xs transition-colors"
              >
                <Icon icon="material-symbols:delete-outline" class="w-4 h-4" />
                Hapus
              </button>

              <div class="flex items-center gap-4 mb-6">
                <span class="bg-[#3B8A8A] text-white px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Tukang #{{ index + 1 }}</span>
              </div>

              <div class="flex flex-col md:flex-row gap-8">
                <!-- Foto Tukang (Left) -->
                <div class="w-full md:w-48 shrink-0">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1 block mb-2">Foto Tukang</label>
                  <div class="relative group/photo aspect-square bg-gray-50 rounded-[1.5rem] border-2 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden transition-all hover:border-[#3B8A8A]/30">
                    <template v-if="t.preview_url">
                      <img :src="t.preview_url" class="w-full h-full object-cover" />
                      <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover/photo:opacity-100 transition-opacity">
                        <button @click="t.foto_baru = null; t.preview_url = null" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-xl font-black text-[10px] flex items-center gap-2 shadow-lg">
                          <Icon icon="material-symbols:close" class="w-4 h-4" />
                        </button>
                      </div>
                    </template>
                    <template v-else>
                      <Icon icon="material-symbols:cloud-upload-outline" class="w-8 h-8 text-gray-300 mb-1" />
                      <p class="text-[10px] font-black text-[#3B8A8A]">Upload</p>
                      <input type="file" @change="handleTukangFile($event, index)" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                    </template>
                  </div>
                </div>

                <!-- Inputs (Right) -->
                <div class="flex-1 space-y-6">
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Tukang *</label>
                    <input
                      v-model="t.nama"
                      placeholder="Nama lengkap tukang"
                      class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-4 font-bold focus:border-[#3B8A8A] focus:ring-0 transition-all"
                    />
                  </div>
                  <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">No. Telepon *</label>
                    <input
                      v-model="t.no_telp"
                      placeholder="08123456789"
                      class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-4 font-bold focus:border-[#3B8A8A] focus:ring-0 transition-all"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex gap-4 pt-4">
            <button
              @click="isManageTukangModalOpen = false"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 rounded-2xl shadow-lg transition-all"
            >
              Batal
            </button>
            <button
              @click="submitTukang"
              :disabled="isSubmittingTukang || managedTukang.length === 0"
              class="flex-1 bg-[#3B8A8A] hover:bg-[#347878] text-white font-bold py-4 rounded-2xl shadow-lg transition-all disabled:opacity-50"
            >
              <div class="flex items-center justify-center gap-2">
                <Icon v-if="isSubmittingTukang" icon="eos-icons:loading" class="w-5 h-5" />
                {{ managedTukang.length > 0 ? 'Buat Tukang' : 'Simpan Tukang' }}
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation-duration: 0.3s;
  animation-fill-mode: both;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes zoom-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.fade-in { animation-name: fade-in; }
.zoom-in { animation-name: zoom-in; }
</style>