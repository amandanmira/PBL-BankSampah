<template>
  <DashboardLayout title="Konfigurasi Website">
    <div class="flex flex-col lg:flex-row gap-6 items-start">
      <!-- Tabs Sidebar -->
      <div class="w-full lg:w-72 bg-white rounded-3xl shadow-sm border border-gray-100 p-5 shrink-0">
        <nav class="flex flex-col gap-3">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 text-sm font-bold group',
              activeTab === tab.id
                ? 'bg-[#4A7043] text-white shadow-lg shadow-green-900/20'
                : 'text-gray-400 hover:bg-gray-50 hover:text-[#4A7043]'
            ]"
          >
            <div :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center transition-colors',
              activeTab === tab.id ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-[#4A7043]/10'
            ]">
              <Icon :icon="tab.icon" class="w-5 h-5" />
            </div>
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="flex-1 bg-white rounded-3xl shadow-sm border border-gray-100 p-10 min-h-[550px] flex flex-col">
        <form @submit.prevent="updateData" class="flex-1 flex flex-col">
          <!-- Logo Website Tab -->
          <div v-if="activeTab === 'logo'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 class="text-2xl font-bold text-gray-800">Logo Website</h2>
            
            <div class="flex flex-col md:flex-row gap-10 items-start md:items-center">
              <!-- Upload Area -->
              <div 
                @click="$refs.fileInput.click()"
                class="w-48 h-48 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center bg-gray-50/50 hover:bg-gray-100/50 transition-all cursor-pointer relative overflow-hidden group hover:border-[#4A7043]/50"
              >
                <input 
                  type="file" 
                  ref="fileInput" 
                  @change="handleFile" 
                  class="hidden" 
                  accept="image/png, image/jpeg"
                />
                
                <div v-if="preview" class="absolute inset-0 p-4 flex items-center justify-center bg-white">
                  <img :src="preview" class="max-w-full max-h-full object-contain" alt="Logo Preview" />
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <Icon icon="material-symbols:edit-outline" class="text-white w-10 h-10" />
                  </div>
                </div>
                
                <div v-else class="flex flex-col items-center text-gray-300">
                  <Icon icon="material-symbols:upload" class="w-12 h-12 mb-3" />
                  <span class="text-xs font-bold uppercase tracking-widest">Pilih Logo</span>
                </div>

                <!-- Resizing Overlay -->
                <div v-if="resizing" class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center z-10">
                  <Icon icon="line-md:loading-twotone-loop" class="w-10 h-10 text-[#4A7043]" />
                  <span class="text-[10px] font-bold mt-2 text-gray-500 uppercase tracking-widest">Memproses...</span>
                </div>
              </div>

              <!-- Upload Info -->
              <div class="space-y-6">
                <div>
                  <h3 class="text-xl font-bold text-gray-700">Upload Logo Baru</h3>
                  <p class="text-sm text-gray-400 font-medium">Sistem akan otomatis menyesuaikan ukuran agar optimal untuk sidebar dan footer.</p>
                </div>
                
                <div class="flex flex-col gap-2">
                  <button 
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="bg-[#4A7043] text-white px-8 py-3 rounded-2xl text-sm font-bold flex items-center gap-3 hover:bg-[#3D5C37] transition-all shadow-md shadow-green-900/10 active:scale-95"
                  >
                    <Icon icon="material-symbols:upload" class="w-5 h-5" />
                    Pilih File
                  </button>
                  <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest px-2">Format: PNG, JPG (Max: 5MB)</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Informasi Kontak Tab -->
          <div v-if="activeTab === 'kontak'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 class="text-2xl font-bold text-gray-800">Informasi Kontak</h2>
            
            <div class="space-y-6 max-w-2xl">
              <!-- No Telp -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="material-symbols:call-outline" class="w-5 h-5 text-[#4A7043]" />
                  Nomor Telepon
                </label>
                <input 
                  v-model="form.no_telp"
                  type="text" 
                  placeholder="0812-3456-7890"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>

              <!-- Email -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="material-symbols:mail-outline" class="w-5 h-5 text-[#4A7043]" />
                  Email
                </label>
                <input 
                  v-model="form.email"
                  type="email" 
                  placeholder="info@abs-banksampah.com"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>

              <!-- Alamat -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="material-symbols:location-on-outline" class="w-5 h-5 text-[#4A7043]" />
                  Alamat
                </label>
                <textarea 
                  v-model="form.alamat"
                  placeholder="Alamat lengkap kantor"
                  rows="4"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all resize-none placeholder:text-gray-300"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Quote Tab -->
          <div v-if="activeTab === 'quote'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 class="text-2xl font-bold text-gray-800">Quote Website</h2>
            
            <div class="space-y-6 max-w-2xl">
              <!-- Quote Hero Atas -->
              <div class="space-y-3">
                <label class="text-sm font-bold text-gray-500 uppercase tracking-wider">Quote Hero Atas (Heading)</label>
                <input 
                  v-model="form.hero_quote_top"
                  type="text" 
                  placeholder="Kelola Sampah, Raih Manfaat"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
                <p class="text-xs text-gray-400 font-medium mt-2 leading-relaxed">Quote ini akan ditampilkan sebagai judul utama di halaman Landing Page</p>
              </div>

              <!-- Quote Hero Bawah -->
              <div class="space-y-3">
                <label class="text-sm font-bold text-gray-500 uppercase tracking-wider">Quote Hero Bawah (Sub-heading)</label>
                <textarea 
                  v-model="form.hero_quote_bottom"
                  placeholder="Kelola Sampah Menjadi Tabungan Digital yang Transparan dan Terintegrasi"
                  rows="3"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all resize-none placeholder:text-gray-300"
                ></textarea>
                <p class="text-xs text-gray-400 font-medium mt-2 leading-relaxed">Quote ini akan ditampilkan sebagai sub-judul di halaman Landing Page</p>
              </div>

              <hr class="border-gray-50 my-2" />

              <div class="space-y-3">
                <label class="text-sm font-bold text-gray-500 uppercase tracking-wider">Semboyan Footer (Tagline)</label>
                <input 
                  v-model="form.quote"
                  type="text" 
                  placeholder="Mengubah Sampah Menjadi Berkah"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
                <p class="text-xs text-gray-400 font-medium mt-2 leading-relaxed">Quote ini akan ditampilkan persis di bawah logo pada bagian kaki halaman (Footer).</p>
              </div>
            </div>
          </div>

          <!-- Media Sosial Tab -->
          <div v-if="activeTab === 'social'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 class="text-2xl font-bold text-gray-800">Media Sosial</h2>
            
            <div class="space-y-6 max-w-2xl">
              <!-- Instagram -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="mdi:instagram" class="w-5 h-5 text-[#4A7043]" />
                  Instagram
                </label>
                <input 
                  v-model="form.instagram"
                  type="text" 
                  placeholder="https://instagram.com/abs_banksampah"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>

              <!-- Facebook -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="mdi:facebook" class="w-5 h-5 text-[#4A7043]" />
                  Facebook
                </label>
                <input 
                  v-model="form.facebook"
                  type="text" 
                  placeholder="https://facebook.com/abs.banksampah"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>

              <!-- LinkedIn -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="mdi:linkedin" class="w-5 h-5 text-[#4A7043]" />
                  LinkedIn
                </label>
                <input 
                  v-model="form.linkedin"
                  type="text" 
                  placeholder="https://linkedin.com/company/abs-banksampah"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>

              <!-- YouTube -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="mdi:youtube" class="w-5 h-5 text-[#4A7043]" />
                  YouTube
                </label>
                <input 
                  v-model="form.youtube"
                  type="text" 
                  placeholder="https://youtube.com/@abs_banksampah"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
              </div>
            </div>
          </div>

          <!-- Pengaturan Sistem Tab -->
          <div v-if="activeTab === 'sistem'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Sistem</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
              <!-- Batas Waktu Edit -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-500 uppercase tracking-wider">
                  <Icon icon="material-symbols:edit-calendar-outline" class="w-5 h-5 text-[#4A7043]" />
                  Batas Waktu Edit Penimbangan (Jam)
                </label>
                <input 
                  v-model="form.batas_waktu_edit"
                  type="number" 
                  min="1"
                  placeholder="Contoh: 12"
                  class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-medium focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all placeholder:text-gray-300"
                />
                <p class="text-xs text-gray-400 font-medium mt-2 leading-relaxed">Batas toleransi waktu petugas dapat mengedit transaksi yang sudah selesai.</p>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <div class="mt-auto pt-10 border-t border-gray-50">
            <button 
              type="submit"
              :disabled="loading || resizing"
              class="w-full bg-gray-100 text-gray-400 hover:bg-[#4A7043] hover:text-white font-bold py-5 rounded-3xl flex items-center justify-center gap-3 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed group active:scale-[0.98]"
            >
              <Icon :icon="loading ? 'line-md:loading-twotone-loop' : 'material-symbols:save-outline'" class="w-6 h-6 transition-transform group-hover:scale-110" />
              <span class="tracking-wide">{{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import { useRouter } from 'vue-router';
import { checkRole } from '@/utils';
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";

checkRole('admin');

const router = useRouter();
const axios = inject('axios');
const activeTab = ref('logo');
const resizing = ref(false);

const tabs = [
  { id: 'logo', name: 'Logo Website', icon: 'material-symbols:image-outline' },
  { id: 'kontak', name: 'Informasi Kontak', icon: 'material-symbols:call-outline' },
  { id: 'quote', name: 'Quote', icon: 'material-symbols:format-quote-outline' },
  { id: 'social', name: 'Media Sosial', icon: 'mdi:instagram' },
  { id: 'sistem', name: 'Pengaturan Sistem', icon: 'material-symbols:settings-outline' },
];

const form = ref({
  quote: "",
  hero_quote_top: "",
  hero_quote_bottom: "",
  instagram: "",
  facebook: "",
  linkedin: "",
  youtube: "",
  no_telp: "",
  email: "",
  lama_deadline: "",
  batas_waktu_edit: "",
  alamat: "",
  tentang: ""
});

const logoFile = ref(null);
const preview = ref(null);
const loading = ref(false);

const token = sessionStorage.getItem('token');

if (!token) {
  router.push('/login');
}

// Function to resize image using Canvas
const resizeImage = (file, maxWidth = 512, maxHeight = 512) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target.result;
      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        if (width > height) {
          if (width > maxWidth) {
            height *= maxWidth / width;
            width = maxWidth;
          }
        } else {
          if (height > maxHeight) {
            width *= maxHeight / height;
            height = maxHeight;
          }
        }

        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);
        
        canvas.toBlob((blob) => {
          resolve(new File([blob], file.name, { type: file.type }));
        }, file.type, 0.9);
      };
      img.onerror = reject;
    };
    reader.onerror = reject;
  });
};

// Ambil data awal dari API
const getData = async () => {
  try {
    loading.value = true;
    const headers = { 'Authorization': `Bearer ${token}` };
    const res = await axios.get("/api/admin/web-config", { headers });
    const data = res.data;

    form.value = {
      ...form.value,
      ...data
    };

    if (data.logo) {
      preview.value = data.logo.startsWith('http') ? data.logo : `${axios.defaults.baseURL}/storage/${data.logo}`;
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Handle file input
const handleFile = async (e) => {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert("Ukuran file terlalu besar (Max 5MB)");
      return;
    }
    
    try {
      resizing.value = true;
      const resizedFile = await resizeImage(file);
      logoFile.value = resizedFile;
      preview.value = URL.createObjectURL(resizedFile);
    } catch (err) {
      console.error("Resize error:", err);
      logoFile.value = file; // Fallback ke file asli
      preview.value = URL.createObjectURL(file);
    } finally {
      resizing.value = false;
    }
  }
};

// Submit update
const updateData = async () => {
  try {
    loading.value = true;

    const formData = new FormData();

    Object.keys(form.value).forEach((key) => {
      // Pastikan kita juga mengirim angka 0 jika admin menginput 0, 
      // tapi menolak null atau string kosong untuk field numerik jika memungkinkan.
      if (form.value[key] !== null && form.value[key] !== "" && key !== "logo") {
        formData.append(key, form.value[key]);
      }
    });

    if (logoFile.value) {
      formData.append("logo", logoFile.value);
    }

    // Explicitly add method spoofing inside FormData
    formData.append("_method", "PUT");

    const headers = {
      'Authorization': `Bearer ${token}`,
      "Content-Type": "multipart/form-data"
    };

    await axios.post("/api/admin/web-config", formData, { headers });

    alert("Berhasil memperbarui konfigurasi");
    getData(); // Refresh data
  } catch (err) {
    console.error(err);
    alert("Gagal memperbarui konfigurasi");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  getData();
});
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slide-in-from-bottom {
  from { transform: translateY(1rem); }
  to { transform: translateY(0); }
}

.animate-in {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}

.fade-in {
  animation-name: fade-in;
}

.slide-in-from-bottom-4 {
  animation-name: slide-in-from-bottom;
}
</style>