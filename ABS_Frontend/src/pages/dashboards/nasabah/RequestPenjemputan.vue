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
const uploadedPhotos = ref([]);
const selectedPreview = ref(null);
const fileInput = ref(null);
const loading = ref(false);
const showSuccessModal = ref(false);
const submittedTrackingId = ref("");
const selectedItems = ref([]); // Array of selected item names
const selectedItemsId = ref([]); // Array of selected item names
const searchQuery = ref("");
const statusFilter = ref("Semua");
const showInstructions = ref(true);

// Pagination for Sampah
const currentPage = ref(1);
const itemsPerPage = 5;

// Dropdown State
const isWeightDropdownOpen = ref(false);

const form = ref({
  deskripsi: "",
  alamat: "",
  gudang_id: "",
  estimasi_berat: "1-5 kg",
  rentang_hari: [],
  rentan_waktu: ""
});

const user = JSON.parse(sessionStorage.getItem('user') || "{}");

const isProfileAddressEmpty = computed(() => {
  return !user.alamat || user.alamat.trim() === '' || user.alamat === '-';
});

// Selected Gudang Info
const selectedGudang = computed(() => {
  return gudangList.value.find(g => Number(g.gudang_id) === Number(form.value.gudang_id)) || null;
});

// Paginated Sampah
const paginatedSampah = computed(() => {
  if (!selectedGudang.value || !selectedGudang.value.sampah) return [];
  const uniqueSampah = [];
  const seenIds = new Set();
  for (const s of selectedGudang.value.sampah) {
    if (s.item_sampah && !seenIds.has(s.item_sampah.item_id)) {
      seenIds.add(s.item_sampah.item_id);
      uniqueSampah.push(s);
    }
  }
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return uniqueSampah.slice(start, end);
});

const totalPages = computed(() => {
  if (!selectedGudang.value || !selectedGudang.value.sampah) return 0;
  const uniqueSampahCount = new Set(
    selectedGudang.value.sampah.map(s => s.item_sampah?.item_id).filter(Boolean)
  ).size;
  return Math.ceil(uniqueSampahCount / itemsPerPage);
});

// Setor Manual Computed
const filteredGudangList = computed(() => {
  let list = gudangList.value;

  // Search Filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(g => 
      g.alamat.toLowerCase().includes(q) || 
      (g.nama && g.nama.toLowerCase().includes(q))
    );
  }

  // Status Filter
  if (statusFilter.value === 'Tersedia') {
    list = list.filter(g => {
      const totalStok = g.sampah?.reduce((acc, s) => acc + (parseFloat(s.stok) || 0), 0) || 0;
      return totalStok < g.kapasitas && g.active === 1;
    });
  }

  return list;
});

const getGudangStats = (gudang) => {
  const totalStok = gudang.sampah?.reduce((acc, s) => acc + (parseFloat(s.stok) || 0), 0) || 0;
  const percentage = Math.min(Math.round((totalStok / gudang.kapasitas) * 100), 100);
  const isFull = percentage >= 100 || gudang.active !== 1;
  
  return {
    totalStok,
    percentage,
    isFull
  };
};

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

const compressImage = (file) => {
  return new Promise((resolve) => {
    if (file.size <= 1024 * 1024 || !file.type.startsWith('image/')) {
      resolve(file);
      return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        const MAX_WIDTH = 1600;
        const MAX_HEIGHT = 1600;
        if (width > height) {
          if (width > MAX_WIDTH) {
            height = Math.round((height * MAX_WIDTH) / width);
            width = MAX_WIDTH;
          }
        } else {
          if (height > MAX_HEIGHT) {
            width = Math.round((width * MAX_HEIGHT) / height);
            height = MAX_HEIGHT;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        let quality = 0.8;
        const checkAndCompress = (q) => {
          canvas.toBlob((blob) => {
            if (!blob) {
              resolve(file);
              return;
            }
            
            if (blob.size > 1024 * 1024 && q > 0.3) {
              checkAndCompress(q - 0.15);
            } else {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });
              resolve(compressedFile);
            }
          }, 'image/jpeg', q);
        };

        checkAndCompress(quality);
      };
      img.src = event.target.result;
    };
    reader.readAsDataURL(file);
  });
};

const handleFile = async (e) => {
  const files = Array.from(e.target.files);
  if (uploadedPhotos.value.length + files.length > 3) {
    alert("Maksimal hanya bisa mengupload 3 foto.");
    if (fileInput.value) fileInput.value.value = '';
    return;
  }
  
  for (const file of files) {
    if (uploadedPhotos.value.length < 3) {
      const compressedFile = await compressImage(file);
      uploadedPhotos.value.push({
        file: compressedFile,
        previewUrl: URL.createObjectURL(compressedFile)
      });
    }
  }
  
  if (fileInput.value) fileInput.value.value = '';
};

const removePhoto = (index) => {
  uploadedPhotos.value.splice(index, 1);
};

const openPreview = (url) => {
  selectedPreview.value = url;
};

const closePreview = () => {
  selectedPreview.value = null;
};

const closeSuccessModal = () => {
  showSuccessModal.value = false;
  router.push('/dashboard-nasabah');
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
  if (addressType.value === 'alamat_profil') {
    if (!user.alamat || user.alamat.trim() === '' || user.alamat === '-') {
      alert("Alamat di profil Anda belum diisi. Silakan isi alamat di profil terlebih dahulu.");
      return;
    }
  } else {
    if (!form.value.alamat || form.value.alamat.trim() === '') {
      alert("Silakan isi alamat lengkap.");
      return;
    }
  }
  if (uploadedPhotos.value.length === 0) {
    alert("Silakan upload minimal satu foto sampah.");
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
    
    formData.append("deskripsi", combinedDesc);
    formData.append("alamat", addressType.value === 'alamat_profil' ? (user.alamat || '-') : form.value.alamat);
    uploadedPhotos.value.forEach((photo) => {
      formData.append("foto[]", photo.file);
    });
    formData.append("estimasi_berat", form.value.estimasi_berat);
    formData.append("rentang_hari", form.value.rentang_hari.join(','));
    formData.append("rentan_waktu", form.value.rentan_waktu);
    formData.append("nasabah_id", user.nasabah_id);
    formData.append("gudang_id", form.value.gudang_id);

    let i = 0;
    for (const s of selectedItemsId.value) {
      formData.append(`detail[${i}][sampah_id]`, s);

      i++;
    }

    // console.log(selectedItemsId.value);
    // console.log(Object.fromEntries(formData));

    const response = await axios.post("/api/nasabah/request-penjemputan", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    submittedTrackingId.value = `#JMP-${String(response.data.data.penjemputan_id).padStart(9, '0')}`;
    showSuccessModal.value = true;
    
    // Clear form
    uploadedPhotos.value = [];
    selectedItems.value = [];
    selectedItemsId.value = [];
    form.value.deskripsi = "";
    form.value.rentang_hari = [];
    form.value.rentan_waktu = "";
    if (fileInput.value) fileInput.value.value = '';

  } catch (err) {
    console.error("Failed to submit request:", err);
    if (err.response && err.response.data && err.response.data.errors) {
      const messages = Object.values(err.response.data.errors).flat().join("\n");
      alert("Gagal mengirim request:\n" + messages);
    } else if (err.response && err.response.data && err.response.data.message) {
      alert("Gagal mengirim request: " + err.response.data.message);
    } else {
      alert("Gagal mengirim request. Pastikan semua data terisi.");
    }
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
  <DashboardLayout title="Request Jemput/Setor">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 max-w-xl lg:max-w-7xl mx-auto">
      
      <!-- Top Tab Switcher -->
      <div class="flex gap-4 p-2 bg-white rounded-[2rem] shadow-sm border border-stone-100/50">
        <button 
          type="button"
          @click="activeTab = 'Jemput Sampah'; addressType = 'alamat_profil'; form.alamat = user.alamat || ''"
          :class="[
            'flex-1 flex items-center justify-center gap-2.5 py-4 rounded-[1.5rem] text-sm font-bold transition-all cursor-pointer border border-transparent',
            activeTab === 'Jemput Sampah' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-[#F5F5F0] text-stone-500 hover:bg-stone-100'
          ]"
        >
          <Icon icon="material-symbols:local-shipping-outline" class="w-5.5 h-5.5" />
          Jemput Sampah
        </button>
        <button 
          type="button"
          @click="activeTab = 'Setor Manual'"
          :class="[
            'flex-1 flex items-center justify-center gap-2.5 py-4 rounded-[1.5rem] text-sm font-bold transition-all cursor-pointer border border-transparent',
            activeTab === 'Setor Manual' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-[#F5F5F0] text-stone-500 hover:bg-stone-100'
          ]"
        >
          <Icon icon="material-symbols:package-2-outline" class="w-5.5 h-5.5" />
          Setor Manual
        </button>
      </div>

      <!-- Main Form Card (Only visible when activeTab is Jemput Sampah) -->
      <div v-if="activeTab === 'Jemput Sampah'" class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-stone-100/50 space-y-6">
        
        <!-- Gudang Selection and details side-by-side on desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
          <div class="space-y-2.5">
            <label class="flex items-center gap-2 text-xs font-bold text-stone-500 uppercase tracking-wider">
              <Icon icon="material-symbols:home-work-outline" class="w-4 h-4 text-stone-400" />
              Pilih Gudang Tujuan <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <select 
                v-model="form.gudang_id"
                class="w-full bg-[#F5F5F0] border border-stone-100 rounded-2xl py-4 px-5 text-sm font-bold appearance-none focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all pr-12 text-stone-700"
              >
                <option value="" disabled>Pilih Gudang Cabang</option>
                <option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id" :disabled="getGudangStats(gudang).isFull">
                  Gudang {{ gudang.alamat.split(',')[0] }} {{ getGudangStats(gudang).isFull ? '(Penuh)' : '' }}
                </option>
              </select>
              <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-6 h-6 text-stone-400 pointer-events-none" />
            </div>
          </div>

          <!-- Gudang Details box (Desktop & Mobile styled) -->
          <div v-if="selectedGudang" class="bg-[#F5F5F0] rounded-2xl p-4.5 text-[11px] text-stone-500 space-y-2 border border-stone-100/50 flex flex-col justify-center h-full min-h-[84px]">
            <div>
              <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider">ID Gudang</p>
              <p class="font-bold text-stone-700 text-xs">GDG-{{ String(selectedGudang.gudang_id).padStart(3, '0') }}</p>
            </div>
            <div>
              <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider">Alamat Gudang</p>
              <p class="font-medium text-stone-700 text-xs leading-relaxed">{{ selectedGudang.alamat }}</p>
            </div>
          </div>
        </div>

        <!-- Address Selection -->
        <div class="space-y-4">
          <label class="flex items-center gap-2 text-xs font-bold text-stone-500 uppercase tracking-wider">
            <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-stone-400" />
            Pilih Alamat Penjemputan
          </label>
          
          <div class="grid grid-cols-2 gap-3">
            <button 
              type="button"
              @click="addressType = 'alamat_profil'; form.alamat = user.alamat || ''"
              :class="[
                'flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all gap-1.5 cursor-pointer',
                addressType === 'alamat_profil' ? 'bg-[#E8F0E6] border-[#4A7043] text-[#4A7043]' : 'bg-white border-stone-100 text-stone-400 hover:border-stone-200'
              ]"
            >
              <Icon icon="material-symbols:home-outline" class="w-6 h-6" />
              <div class="text-center">
                <p class="font-bold text-xs">Alamat di Profil</p>
                <p class="text-[9px] font-medium opacity-60">Gunakan alamat tersimpan</p>
              </div>
            </button>
            <button 
              type="button"
              @click="addressType = 'alamat_baru'; form.alamat = ''"
              :class="[
                'flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all gap-1.5 cursor-pointer',
                addressType === 'alamat_baru' ? 'bg-[#E8F0E6] border-[#4A7043] text-[#4A7043]' : 'bg-white border-stone-100 text-stone-400 hover:border-stone-200'
              ]"
            >
              <Icon icon="material-symbols:add-location-outline" class="w-6 h-6" />
              <div class="text-center">
                <p class="font-bold text-xs">Alamat Baru</p>
                <p class="text-[9px] font-medium opacity-60">Input alamat berbeda</p>
              </div>
            </button>
          </div>

          <!-- Address Textarea (Display in both modes) -->
          <div class="space-y-2">
            <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider ml-1">Alamat Lengkap <span class="text-red-500">*</span></label>
            <textarea 
              v-model="form.alamat"
              :readonly="addressType === 'alamat_profil'"
              :placeholder="addressType === 'alamat_profil' ? (isProfileAddressEmpty ? 'Alamat profil Anda masih kosong!' : 'Alamat dari profil akan digunakan') : 'Masukkan alamat baru'"
              :class="[
                'w-full rounded-2xl py-3.5 px-4 text-xs font-medium focus:outline-none transition-all min-h-[90px] text-stone-700',
                addressType === 'alamat_profil' && isProfileAddressEmpty 
                  ? 'bg-red-50 border border-red-200 placeholder-red-400' 
                  : 'bg-[#F5F5F0] border border-stone-100 focus:ring-2 focus:ring-[#4A7043]/20'
              ]"
            ></textarea>
            
            <div v-if="addressType === 'alamat_profil' && isProfileAddressEmpty" class="flex items-center justify-between mt-1 px-1">
              <p class="text-[10px] font-bold text-red-500 flex items-center gap-1">
                <Icon icon="material-symbols:error-outline" class="w-3.5 h-3.5" />
                Alamat profil belum diisi.
              </p>
              <RouterLink to="/dashboard-nasabah/edit-profile" class="text-[10px] font-bold text-[#4A7043] hover:underline flex items-center gap-1">
                Isi Profil Sekarang <Icon icon="material-symbols:arrow-right-alt" class="w-3.5 h-3.5" />
              </RouterLink>
            </div>

            <p v-if="addressType === 'alamat_baru'" class="text-[9px] font-medium text-stone-400 italic px-1">Edit alamat di profil jika ingin mengubah alamat default</p>
          </div>
        </div>

        <!-- Waktu Penjemputan Section -->
        <div class="space-y-6">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-widest">
            <Icon icon="material-symbols:event-available-outline" class="w-4 h-4" />
            Preferensi Waktu Penjemputan
          </label>
          
          <!-- Rentang Hari -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest ml-1">Pilih Hari (Bisa lebih dari satu)</label>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-7 gap-3">
              <button 
                v-for="hari in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']"
                :key="hari"
                @click="() => {
                  const index = form.rentang_hari.indexOf(hari);
                  if (index > -1) form.rentang_hari.splice(index, 1);
                  else form.rentang_hari.push(hari);
                }"
                :class="[
                  'py-3 px-2 rounded-xl text-xs font-black transition-all border-2',
                  form.rentang_hari.includes(hari) 
                    ? 'bg-[#4A7043] text-white border-[#4A7043]' 
                    : 'bg-white border-stone-100 hover:border-stone-200 text-stone-500'
                ]"
              >
                {{ hari }}
              </button>
            </div>
          </div>

          <!-- Rentang Waktu -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest ml-1">Pilih Rentang Waktu</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <button 
                v-for="waktu in ['Pagi (08:00-12:00)', 'Siang (12:00-15:00)', 'Sore (15:00-17:00)']"
                :key="waktu"
                @click="form.rentan_waktu = waktu"
                :class="[
                  'py-3 px-4 rounded-xl text-xs font-black transition-all border-2 text-left',
                  form.rentan_waktu === waktu 
                    ? 'bg-[#4A7043] text-white border-[#4A7043]' 
                    : 'bg-white border-stone-100 hover:border-stone-200 text-stone-600'
                ]"
              >
                {{ waktu }}
              </button>
            </div>
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
            class="border-2 border-dashed border-stone-200 rounded-2xl p-8 flex flex-col items-center justify-center gap-4 cursor-pointer hover:border-[#4A7043] hover:bg-stone-50/50 transition-all"
          >
            <input 
              type="file" 
              ref="fileInput" 
              class="hidden" 
              @change="handleFile" 
              accept="image/*"
              multiple
            />
            <Icon icon="material-symbols:upload" class="w-8 h-8 text-stone-400" />
            <div class="text-center">
              <p class="text-sm font-bold text-stone-800">Upload Foto Sampah</p>
              <p class="text-[10px] font-medium text-stone-400 mt-0.5">Klik untuk memilih foto (bisa lebih dari 1)</p>
            </div>
          </div>
          
          <!-- Photo Thumbnails -->
          <div v-if="uploadedPhotos.length > 0" class="flex flex-wrap gap-3 mt-3">
            <div v-for="(photo, index) in uploadedPhotos" :key="index" class="relative group w-16 h-16 rounded-xl overflow-hidden border border-stone-200 shadow-sm cursor-pointer" @click="openPreview(photo.previewUrl)">
              <img :src="photo.previewUrl" class="w-full h-full object-cover" />
              <button @click.stop="removePhoto(index)" class="absolute top-1 right-1 bg-white/90 text-red-500 rounded-full p-1 shadow-sm hover:bg-red-50">
                <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
          <p class="text-[9px] text-stone-400 font-medium italic mt-1.5 flex items-start gap-1">
            <Icon icon="material-symbols:lightbulb-outline" class="w-3 h-3 text-orange-400 shrink-0 mt-0.5" />
            Tips: Pastikan sampah sudah ditata rapi dan foto terlihat jelas untuk mempercepat proses verifikasi
          </p>
        </div>

        <!-- Description -->
        <div class="space-y-2.5">
          <label class="flex items-center gap-2 text-xs font-bold text-stone-500 uppercase tracking-wider">
            <Icon icon="material-symbols:description-outline" class="w-4 h-4 text-stone-400" />
            Deskripsi
          </label>
          <textarea 
            v-model="form.deskripsi"
            placeholder="Contoh: Ada sampah botol plastik sekitar 5kg, kardus 3kg..." 
            class="w-full bg-[#F5F5F0] border border-stone-100 rounded-2xl py-3.5 px-4 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all min-h-[90px] text-stone-700"
          ></textarea>
        </div>

        <!-- Estimasi Berat -->
        <div class="space-y-2.5">
          <label class="flex items-center gap-2 text-xs font-bold text-stone-500 uppercase tracking-wider">
            <Icon icon="material-symbols:monitor-weight-outline" class="w-4 h-4 text-stone-400" />
            Estimasi Berat <span class="text-red-500">*</span>
          </label>
          <div class="relative w-full">
            <button 
              type="button"
              @click="isWeightDropdownOpen = !isWeightDropdownOpen"
              class="w-full bg-white border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold flex justify-between items-center shadow-sm hover:border-stone-300 transition-all"
            >
              {{ form.estimasi_berat || 'Pilih estimasi berat' }}
              <Icon icon="material-symbols:keyboard-arrow-down" :class="['w-5 h-5 text-stone-400 transition-transform duration-300', isWeightDropdownOpen ? 'rotate-180' : '']" />
            </button>
            
            <div v-if="isWeightDropdownOpen" class="absolute z-20 w-full mt-1.5 bg-white border border-stone-200 rounded-xl shadow-xl overflow-hidden">
              <div 
                v-for="opt in ['1-5 kg', '6-10 kg', '11-20 kg', 'Lebih dari 20 kg']" 
                :key="opt"
                @click="form.estimasi_berat = opt; isWeightDropdownOpen = false"
                :class="[
                  'px-5 py-3 text-xs font-bold cursor-pointer transition-colors',
                  form.estimasi_berat === opt ? 'bg-[#4A7043] text-white' : 'hover:bg-stone-50 text-stone-600'
                ]"
              >
                {{ opt }}
              </div>
            </div>
          </div>
        </div>

        <!-- Jenis Sampah Checklist -->
        <div class="space-y-3">
          <label class="flex items-center gap-2 text-xs font-bold text-stone-500 uppercase tracking-wider">
            <Icon icon="material-symbols:recycling" class="w-4 h-4 text-stone-400" />
            Jenis Sampah <span class="text-red-500">*</span>
            <span class="text-[10px] text-stone-400 font-bold lowercase normal-case ml-1">(Bisa pilih lebih dari 1)</span>
          </label>

          <!-- Checklist Rows -->
          <div class="space-y-2">
            <div v-if="selectedGudang && paginatedSampah.length > 0" class="space-y-2">
              <div 
                v-for="s in paginatedSampah" 
                :key="s.sampah_id"
                @click="toggleItem(s.item_sampah.nama, s.sampah_id)"
                class="p-4 rounded-xl border border-stone-100 shadow-sm cursor-pointer flex justify-between items-center bg-white hover:border-stone-200 transition-all"
              >
                <div class="flex items-center gap-3">
                  <div :class="['w-5 h-5 rounded border flex items-center justify-center transition-all', 
                    selectedItems.includes(s.item_sampah.nama) ? 'border-[#4A7043] bg-[#4A7043] text-white' : 'border-stone-300 bg-white'
                  ]">
                    <Icon v-if="selectedItems.includes(s.item_sampah.nama)" icon="material-symbols:check" class="w-4 h-4 font-black" />
                  </div>
                  <span class="font-bold text-stone-700 text-xs md:text-sm">{{ s.item_sampah.nama }}</span>
                </div>
                <div class="text-right">
                  <span class="font-black text-[#4A7043] text-xs md:text-sm">{{ formatCurrency(s.item_sampah.harga_beli) }}/kg</span>
                </div>
              </div>

              <!-- Selected Items List Summary matching desktop mockup -->
              <div v-if="selectedItems.length > 0" class="bg-[#F5F5F0] rounded-xl p-4 text-xs font-bold text-stone-600 mt-3 border border-stone-100/50">
                Dipilih: {{ selectedItems.join(', ') }}
              </div>
            </div>
            <div v-else class="py-10 flex flex-col items-center justify-center text-center px-4 bg-stone-50 rounded-2xl border border-dashed border-stone-200">
              <Icon icon="material-symbols:inventory-2-outline" class="w-10 h-10 text-stone-300 mb-2" />
              <p class="text-stone-400 font-bold text-xs italic">
                {{ form.gudang_id ? 'Gudang ini tidak memiliki jenis sampah.' : 'Silakan pilih gudang tujuan terlebih dahulu.' }}
              </p>
            </div>
          </div>

          <!-- Pagination inside form matching mockup -->
          <div v-if="totalPages > 1" class="mt-4 flex items-center justify-center gap-2">
            <button 
              type="button"
              @click="currentPage > 1 && currentPage--"
              :disabled="currentPage === 1"
              class="w-8 h-8 flex items-center justify-center text-stone-400 hover:text-stone-700 disabled:opacity-20 transition-colors"
            >
              <Icon icon="material-symbols:chevron-left" class="w-5 h-5" />
            </button>
            <div class="flex items-center gap-1">
              <button 
                v-for="p in totalPages" 
                :key="p"
                type="button"
                @click="currentPage = p"
                :class="[
                  'w-8 h-8 flex items-center justify-center text-xs font-bold rounded-full transition-all',
                  currentPage === p ? 'bg-[#4A7043] text-white shadow-sm' : 'text-stone-400 hover:bg-stone-50'
                ]"
              >
                {{ p }}
              </button>
            </div>
            <button 
              type="button"
              @click="currentPage < totalPages && currentPage++"
              :disabled="currentPage === totalPages"
              class="w-8 h-8 flex items-center justify-center text-stone-400 hover:text-stone-700 disabled:opacity-20 transition-colors"
            >
              <Icon icon="material-symbols:chevron-right" class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button 
          @click="submitRequest"
          :disabled="loading || (addressType === 'alamat_profil' && isProfileAddressEmpty)"
          class="w-full py-4.5 rounded-2xl bg-[#4A7043] text-white font-black text-sm hover:bg-[#3d5c37] active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-stone-400"
        >
          <template v-if="!loading">
            Request Penjemputan
          </template>
          <template v-else>
            <Icon icon="line-md:loading-twotone-loop" class="w-5 h-5" />
            Mengirim Request...
          </template>
        </button>

      </div>

      <!-- Setor Manual View (Warehouse list matching nasabah - setor manual.png) -->
      <div v-else-if="activeTab === 'Setor Manual'" class="space-y-6 animate-in fade-in duration-500">
        <!-- Instructions Accordion -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-stone-100/50 overflow-hidden">
          <button 
            @click="showInstructions = !showInstructions"
            class="w-full flex items-center justify-between p-6 bg-[#4A7043] text-white font-bold text-sm uppercase tracking-wider transition-all"
          >
            <div class="flex items-center gap-3">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5" />
              Cara Setor Sampah ke Gudang
            </div>
            <Icon icon="material-symbols:keyboard-arrow-down" :class="['w-6 h-6 transition-transform duration-300', showInstructions ? 'rotate-180' : '']" />
          </button>
          
          <div v-if="showInstructions" class="p-8 bg-white border-t border-stone-100">
            <ol class="space-y-3 list-decimal pl-5 text-stone-600 font-bold text-sm leading-relaxed">
              <li>Pilih gudang yang tersedia sesuai lokasi terdekatmu.</li>
              <li>Siapkan sampah yang sudah dipilah dan disusun rapi.</li>
              <li>Datang ke gudang dan temui petugas untuk melakukan penyetoran.</li>
              <li>Petugas akan menimbang sampahmu dan mencatat hasilnya.</li>
              <li>Pantau status setor melalui menu “Sampah Saya”.</li>
            </ol>
          </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-stone-100/50 space-y-4">
          <div class="relative">
            <Icon icon="material-symbols:search" class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-stone-400" />
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari gudang..." 
              class="w-full bg-white border border-stone-200 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all text-stone-700"
            />
          </div>
          
          <div class="flex items-center gap-2">
            <button 
              type="button"
              @click="statusFilter = 'Semua'"
              :class="[
                'px-4 py-2.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                statusFilter === 'Semua' ? 'bg-[#4A7043] text-white shadow-sm' : 'bg-[#F5F5F0] text-stone-600 hover:bg-stone-200'
              ]"
            >
              Semua
            </button>
            <button 
              type="button"
              @click="statusFilter = 'Tersedia'"
              :class="[
                'px-4 py-2.5 rounded-lg text-xs font-bold transition-all cursor-pointer whitespace-nowrap',
                statusFilter === 'Tersedia' ? 'bg-[#4A7043] text-white shadow-sm' : 'bg-[#F5F5F0] text-stone-600 hover:bg-stone-200'
              ]"
            >
              Tersedia ({{ filteredGudangList.filter(g => getGudangStats(g).percentage < 100).length }})
            </button>
            
            <div class="flex-1 relative">
              <select class="w-full bg-white border border-stone-200 rounded-xl py-2 px-3 text-xs font-bold appearance-none text-stone-500 focus:outline-none pr-8">
                <option>Semua Wilayah</option>
              </select>
              <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" />
            </div>
          </div>
        </div>

        <!-- Warehouse List (Matching mockup style) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="gudang in filteredGudangList" 
            :key="gudang.gudang_id"
            class="bg-white rounded-[2rem] border border-stone-100/50 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300"
          >
            <!-- Card Header -->
            <div :class="['p-5 flex items-center gap-3 text-white transition-colors', getGudangStats(gudang).isFull ? 'bg-[#E86B6B]' : 'bg-[#4A7043]']">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:storefront-outline" class="w-6 h-6" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-base truncate">Gudang {{ gudang.alamat.split(',')[0] }}</h4>
                <div class="flex items-center gap-1 opacity-90 mt-0.5">
                  <Icon icon="material-symbols:location-on" class="w-3.5 h-3.5" />
                  <p class="text-xs font-medium truncate">{{ gudang.alamat }}</p>
                </div>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-5 space-y-4">
              <!-- Grid Stats -->
              <div class="grid grid-cols-[1fr_1fr_1.2fr] gap-2">
                <div class="bg-[#F5F5F0] p-3.5 rounded-2xl text-center">
                  <p class="text-base font-black text-[#4A7043] leading-none">{{ gudang.petugas?.length || 0 }}</p>
                  <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mt-1.5">Petugas</p>
                </div>
                <div class="bg-[#F5F5F0] p-3.5 rounded-2xl text-center">
                  <p class="text-base font-black text-[#0D9488] leading-none">{{ gudang.tukang?.length || 0 }}</p>
                  <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mt-1.5">Tukang</p>
                </div>
                <div class="bg-[#F5F5F0] p-3.5 rounded-2xl text-center flex flex-col justify-center">
                  <p :class="['text-xs sm:text-sm lg:text-base font-black leading-none', getGudangStats(gudang).isFull ? 'text-[#E86B6B]' : 'text-[#4A7043]']">
                    {{ Math.round(getGudangStats(gudang).totalStok) }}/{{ gudang.kapasitas }}
                  </p>
                  <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mt-1.5">kg</p>
                </div>
              </div>

              <!-- Capacity Progress -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-stone-400">
                  <span>Kapasitas</span>
                  <span :class="getGudangStats(gudang).isFull ? 'text-[#E86B6B]' : 'text-[#4A7043]'">{{ getGudangStats(gudang).percentage }}%</span>
                </div>
                <div class="h-2 w-full bg-stone-100 rounded-full overflow-hidden">
                  <div 
                    :class="['h-full transition-all duration-1000', getGudangStats(gudang).isFull ? 'bg-[#E86B6B]' : 'bg-[#EBB82C]']"
                    :style="{ width: getGudangStats(gudang).percentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredGudangList.length === 0" class="bg-white rounded-[2.5rem] p-12 flex flex-col items-center text-center border border-dashed border-stone-200">
          <Icon icon="material-symbols:search-off" class="w-16 h-16 text-stone-200 mb-4" />
          <h3 class="text-base font-bold text-stone-800">Gudang Tidak Ditemukan</h3>
          <p class="text-xs font-bold text-stone-400 mt-1">Coba gunakan kata kunci lain atau ubah filter status.</p>
        </div>
      </div>

    </div>

    <!-- Image Preview Modal -->
    <div v-if="selectedPreview" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm" @click="closePreview">
      <div class="relative max-w-4xl max-h-[90vh] p-4">
        <button @click="closePreview" class="absolute -top-12 right-0 bg-white text-stone-800 rounded-full w-10 h-10 flex items-center justify-center hover:bg-stone-200 transition-colors">
          <Icon icon="material-symbols:close" class="w-5 h-5" />
        </button>
        <img :src="selectedPreview" class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl object-contain" @click.stop />
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="bg-white w-full max-w-md rounded-[2rem] p-6 relative shadow-2xl text-center mx-4">
        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <Icon icon="material-symbols:check-circle-outline" class="w-10 h-10 text-[#4A7043]" />
        </div>
        <h3 class="text-xl font-black text-stone-800 mb-2">Request Berhasil Dibuat!</h3>
        <p class="text-stone-500 text-xs font-medium mb-6">
          Permintaan Anda telah kami daftarkan. Petugas akan memproses pesanan Anda.
        </p>

        <div class="bg-stone-50 rounded-2xl p-4 mb-6 border border-stone-100">
          <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider mb-1">ID Transaksi / Tracking</p>
          <p class="text-lg font-black text-[#4A7043] tracking-wide">{{ submittedTrackingId }}</p>
        </div>

        <div class="space-y-2">
          <button 
            @click="router.push('/dashboard-nasabah/sampah-saya')"
            class="w-full py-3.5 rounded-xl bg-[#4A7043] text-white font-bold text-xs"
          >
            Lihat Riwayat
          </button>
          <button @click="closeSuccessModal" class="w-full py-3.5 rounded-xl bg-white border border-stone-200 text-stone-500 font-bold text-xs">
            Kembali
          </button>
        </div>
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
