<template>
  <DashboardLayout title="Profile">
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in duration-500 pb-20">
      
      <!-- Notifikasi Berhasil -->
      <div v-if="successMessage" class="bg-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <Icon icon="material-symbols:check-circle" class="w-6 h-6" />
        <span class="font-bold">{{ successMessage }}</span>
      </div>

      <!-- Card Kelengkapan Profil -->
      <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-stone-100 relative overflow-hidden group">
        <div class="flex justify-between items-start mb-4">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-[#A86444]/10 rounded-2xl text-[#A86444]">
              <Icon icon="material-symbols:trending-up" class="w-6 h-6" />
            </div>
            <div>
              <h2 class="text-xl font-black text-stone-800">Kelengkapan Profil</h2>
              <p class="text-stone-500 text-sm font-medium">Lengkapi profil Anda untuk mendapatkan pengalaman terbaik dalam menggunakan ABS.</p>
            </div>
          </div>
          <div class="text-right">
            <span class="text-3xl font-black text-[#A86444]">{{ completionPercentage }}%</span>
            <p class="text-stone-400 text-[10px] font-bold uppercase tracking-wider">{{ completedFieldsCount }}/{{ totalFieldsCount }} Lengkap</p>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-stone-100 h-3 rounded-full overflow-hidden mb-6">
          <div 
            class="h-full bg-[#A86444] transition-all duration-1000 ease-out rounded-full shadow-[0_0_10px_rgba(168,100,68,0.3)]"
            :style="{ width: `${completionPercentage}%` }"
          ></div>
        </div>

        <!-- Missing Fields List -->
        <div v-if="missingFields.length > 0" class="bg-[#FDF8F6] rounded-2xl p-6 border border-[#A86444]/10">
          <div class="flex items-center gap-2 mb-3 text-[#A86444]">
            <Icon icon="material-symbols:info-outline" class="w-4 h-4" />
            <span class="text-xs font-black uppercase tracking-wider">Field yang Belum Dilengkapi:</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
            <div v-for="field in missingFields" :key="field" class="flex items-center gap-2 text-stone-500">
              <Icon :icon="getFieldIcon(field)" class="w-4 h-4 opacity-50" />
              <span class="text-xs font-bold">{{ field }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Tabs Section -->
      <div class="bg-white rounded-[2rem] shadow-sm border border-stone-100 overflow-hidden">
        <!-- Tab Headers -->
        <div class="flex border-b border-stone-100 bg-stone-50/50">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="cn(
              'flex-1 py-5 text-sm font-black transition-all relative',
              activeTab === tab.id ? 'text-[#4A7043]' : 'text-stone-400 hover:text-stone-600'
            )"
          >
            {{ tab.name }}
            <div v-if="activeTab === tab.id" class="absolute bottom-0 left-0 w-full h-1 bg-[#4A7043] rounded-t-full"></div>
          </button>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- TAB 1: INFORMASI PRIBADI -->
          <div v-if="activeTab === 'pribadi'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-500">
            <div class="grid grid-cols-1 gap-6">
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-[#4A7043]" />
                  Username
                </label>
                <input v-model="form.username" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" />
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:badge-outline" class="w-4 h-4 text-[#4A7043]" />
                  Nama Lengkap
                </label>
                <input v-model="form.nama" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" />
              </div>

              <div class="space-y-2 opacity-60">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:mail-outline" class="w-4 h-4 text-[#4A7043]" />
                  Email
                </label>
                <input :value="form.email" type="email" disabled class="w-full bg-stone-100 border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-500 cursor-not-allowed" />
                <p class="text-[10px] font-bold text-stone-400 italic">* Email tidak dapat diubah secara mandiri</p>
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:call-outline" class="w-4 h-4 text-[#4A7043]" />
                  Nomor Telepon
                </label>
                <input v-model="form.no_telp" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" placeholder="Contoh: 081234567890" />
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-[#4A7043]" />
                  Alamat Lengkap
                </label>
                <textarea v-model="form.alamat" rows="3" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all resize-none"></textarea>
              </div>

              <!-- GMap Static Trigger -->
              <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between px-1">
                  <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Titik Koordinat (Opsional)</span>
                </div>
                <button 
                  @click="isMapActive = true"
                  class="w-full bg-[#5BA09B] text-white py-4 rounded-xl font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#5BA09B]/20 hover:bg-[#4D8884] transition-all active:scale-[0.98]"
                >
                  <Icon icon="material-symbols:location-on" class="w-5 h-5" />
                  Atur Titik Jemput
                </button>
                <div v-if="form.gmap" class="flex items-center gap-2 text-green-600 px-1">
                  <Icon icon="material-symbols:check-circle" class="w-4 h-4" />
                  <span class="text-[10px] font-black uppercase tracking-wider">Lokasi sudah diatur (Otomatis)</span>
                </div>
              </div>
            </div>

            <div class="pt-6 flex flex-col gap-3">
              <button 
                @click="updateProfile"
                :disabled="isUpdating"
                class="w-full bg-[#4A7043] text-white py-4 rounded-xl font-black text-sm flex items-center justify-center gap-2 transition-all disabled:opacity-50 shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37]"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
                <Icon v-else icon="material-symbols:save" class="w-5 h-5" />
                {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
              <button v-if="isProfileDirty" @click="resetProfile" class="w-full py-4 rounded-xl border border-stone-200 text-stone-500 font-black text-sm hover:bg-stone-50 transition-all">
                Batal
              </button>
            </div>
          </div>

          <!-- TAB 2: REKENING BANK -->
          <div v-if="activeTab === 'bank'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-500">
            <div class="grid grid-cols-1 gap-6">
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:account-balance-outline" class="w-4 h-4 text-[#4A7043]" />
                  Nama Bank
                </label>
                <input v-model="form.nama_bank" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" placeholder="Contoh: Bank BRI" />
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:payments-outline" class="w-4 h-4 text-[#4A7043]" />
                  Nomor Rekening
                </label>
                <input v-model="form.no_rekening" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" placeholder="Masukkan nomor rekening" />
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:person-edit-outline" class="w-4 h-4 text-[#4A7043]" />
                  Nama Pemilik Rekening
                </label>
                <input v-model="form.nama_rek" type="text" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" placeholder="Sesuai dengan rekening bank" />
              </div>
              
              <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex gap-3">
                <Icon icon="material-symbols:lightbulb-outline" class="w-5 h-5 text-blue-500 shrink-0" />
                <p class="text-[11px] font-bold text-blue-600 leading-relaxed">
                  Pastikan informasi rekening bank Anda benar untuk memudahkan proses penarikan dana.
                </p>
              </div>
            </div>

            <button 
              @click="updateProfile"
              :disabled="isUpdating"
              class="w-full bg-[#4A7043] text-white py-4 rounded-xl font-black text-sm flex items-center justify-center gap-2 transition-all disabled:opacity-50 shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37]"
            >
              <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
              <Icon v-else icon="material-symbols:save" class="w-5 h-5" />
              {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>

          <!-- TAB 3: UBAH PASSWORD -->
          <div v-if="activeTab === 'password'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-500">
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 flex gap-3 mb-4">
              <Icon icon="material-symbols:lock-outline" class="w-5 h-5 text-amber-600 shrink-0" />
              <p class="text-[11px] font-bold text-amber-700 leading-relaxed">
                Pastikan password baru Anda kuat dan mudah diingat. Minimal 8 karakter.
              </p>
            </div>

            <div class="space-y-6">
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:lock-open-outline" class="w-4 h-4 text-[#4A7043]" />
                  Password Baru
                </label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
                    placeholder="Masukkan password baru" 
                  />
                  <button @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-400">
                    <Icon :icon="showPassword ? 'material-symbols:visibility-off-outline' : 'material-symbols:visibility-outline'" class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-black text-stone-800 uppercase tracking-widest">
                  <Icon icon="material-symbols:lock-outline" class="w-4 h-4 text-[#4A7043]" />
                  Konfirmasi Password Baru
                </label>
                <input v-model="passwordForm.password_confirmation" :type="showPassword ? 'text' : 'password'" class="w-full bg-[#F9F9F7] border border-stone-200 rounded-xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" placeholder="Konfirmasi password baru" />
              </div>
            </div>

            <button 
              @click="updatePassword"
              :disabled="isUpdating || !isPasswordFormValid"
              class="w-full bg-[#4A7043] text-white py-4 rounded-xl font-black text-sm flex items-center justify-center gap-2 transition-all disabled:opacity-50 shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37]"
            >
              <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
              <Icon v-else icon="material-symbols:vpn-key-outline" class="w-5 h-5" />
              {{ isUpdating ? 'Mengubah...' : 'Ubah Password' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Peta (Statis Placeholder) -->
    <div v-if="isMapActive" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[2.5rem] w-full max-w-4xl h-[80vh] flex flex-col overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-6 border-b border-stone-100 flex justify-between items-center bg-[#FDF8F6]">
          <div class="flex items-center gap-3">
            <Icon icon="material-symbols:location-on" class="w-6 h-6 text-[#5BA09B]" />
            <h3 class="text-xl font-black text-stone-800">Tentukan Lokasi Jemput</h3>
          </div>
          <button @click="isMapActive = false" class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center text-stone-500 hover:bg-red-100 hover:text-red-600 transition-all">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>
        
        <div class="flex-1 bg-stone-50 relative group">
          <!-- Real Leaflet Map would go here, for now using image as requested static -->
          <div class="absolute inset-0 flex flex-col items-center justify-center text-stone-400 p-10 text-center space-y-4">
            <Icon icon="material-symbols:map-outline" class="w-20 h-20 opacity-20" />
            <p class="font-bold text-lg">Peta Lokasi Interaktif</p>
            <p class="text-sm max-w-sm">Klik pada peta untuk menandai koordinat lokasi penjemputan Anda secara otomatis.</p>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-stone-100 text-stone-700 w-full max-w-md">
              <div class="flex items-center gap-3 mb-4 text-green-600">
                <Icon icon="material-symbols:my-location" class="w-5 h-5" />
                <span class="text-sm font-black uppercase tracking-wider">Simulasi Koordinat Aktif</span>
              </div>
              <div class="space-y-3 text-left">
                <div class="flex justify-between text-xs"><span class="font-bold">Latitude:</span> <span class="font-mono">-6.2088</span></div>
                <div class="flex justify-between text-xs"><span class="font-bold">Longitude:</span> <span class="font-mono">106.8456</span></div>
              </div>
            </div>
          </div>
          
          <div class="absolute bottom-6 left-1/2 -translate-x-1/2 w-full max-w-md px-6 z-10">
            <div class="bg-green-100 border border-green-200 p-4 rounded-2xl flex items-center gap-3 text-green-700 animate-bounce">
              <Icon icon="material-symbols:check-circle" class="w-5 h-5" />
              <span class="text-xs font-black">Titik lokasi berhasil disimpan!</span>
            </div>
          </div>
        </div>
        
        <div class="p-6 border-t border-stone-100 flex gap-4">
          <button @click="isMapActive = false" class="flex-1 py-4 rounded-2xl border border-stone-200 font-black text-sm text-stone-500 hover:bg-stone-50 transition-all">
            Tutup
          </button>
          <button @click="saveLocation" class="flex-[2] py-4 rounded-2xl bg-[#5BA09B] text-white font-black text-sm shadow-lg shadow-[#5BA09B]/20 hover:bg-[#4D8884] transition-all">
            Gunakan Lokasi Ini
          </button>
        </div>
      </div>
    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject, watch } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";

const axios = inject('axios');

const activeTab = ref('pribadi');
const tabs = [
  { id: 'pribadi', name: 'Informasi Pribadi' },
  { id: 'bank', name: 'Rekening Bank' },
  { id: 'password', name: 'Ubah Password' }
];

const form = ref({
  username: "",
  nama: "",
  email: "",
  no_telp: "",
  alamat: "",
  gmap: "",
  nama_bank: "",
  no_rekening: "",
  nama_rek: ""
});

const originalForm = ref({});
const isUpdating = ref(false);
const successMessage = ref("");
const isMapActive = ref(false);
const showPassword = ref(false);

const passwordForm = ref({
  password: "",
  password_confirmation: ""
});

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) { return {}; }
});

const fetchProfile = async () => {
  try {
    const id = user.value.nasabah_id;
    const res = await axios.get(`/api/nasabah/profile/${id}`);
    form.value = { ...res.data };
    originalForm.value = { ...res.data };
  } catch (err) {
    console.error("Failed to fetch profile:", err);
  }
};

const isProfileDirty = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(originalForm.value);
});

const isPasswordFormValid = computed(() => {
  return passwordForm.value.password.length >= 8 && 
         passwordForm.value.password === passwordForm.value.password_confirmation;
});

const resetProfile = () => {
  form.value = { ...originalForm.value };
};

const updateProfile = async () => {
  isUpdating.value = true;
  try {
    const id = user.value.nasabah_id;
    const res = await axios.put(`/api/nasabah/edit-profile/${id}`, form.value);
    
    // Update local storage
    sessionStorage.setItem("user", JSON.stringify(res.data.data));
    originalForm.value = { ...res.data.data };
    
    successMessage.value = "Profil berhasil diperbarui!";
    setTimeout(() => successMessage.value = "", 3000);
  } catch (err) {
    alert("Gagal memperbarui profil: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
  }
};

const updatePassword = async () => {
  isUpdating.value = true;
  try {
    const id = user.value.nasabah_id;
    await axios.put(`/api/nasabah/update-password/${id}`, passwordForm.value);
    
    passwordForm.value = { password: "", password_confirmation: "" };
    successMessage.value = "Password berhasil diubah!";
    setTimeout(() => successMessage.value = "", 3000);
  } catch (err) {
    alert("Gagal mengubah password: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
  }
};

const saveLocation = () => {
  // Simulasi simpan gmap
  form.value.gmap = JSON.stringify({ lat: -6.2088, lng: 106.8456 });
  isMapActive.value = false;
};

// Logic Kelengkapan Profil
const totalFieldsCount = 8;
const completedFieldsCount = computed(() => {
  let count = 0;
  if (form.value.username) count++;
  if (form.value.nama) count++;
  if (form.value.email) count++;
  if (form.value.no_telp) count++;
  if (form.value.alamat) count++;
  if (form.value.nama_bank) count++;
  if (form.value.no_rekening) count++;
  if (form.value.nama_rek) count++;
  return count;
});

const completionPercentage = computed(() => {
  return Math.round((completedFieldsCount.value / totalFieldsCount) * 100);
});

const missingFields = computed(() => {
  const missing = [];
  if (!form.value.username) missing.push("Username");
  if (!form.value.nama) missing.push("Nama Lengkap");
  if (!form.value.email) missing.push("Email");
  if (!form.value.no_telp) missing.push("Nomor Telepon");
  if (!form.value.alamat) missing.push("Alamat Lengkap");
  if (!form.value.nama_bank) missing.push("Nama Bank");
  if (!form.value.no_rekening) missing.push("Nomor Rekening");
  if (!form.value.nama_rek) missing.push("Nama Pemilik Rekening");
  return missing;
});

const getFieldIcon = (field) => {
  const icons = {
    "Username": "material-symbols:person-outline",
    "Nama Lengkap": "material-symbols:badge-outline",
    "Email": "material-symbols:mail-outline",
    "Nomor Telepon": "material-symbols:call-outline",
    "Alamat Lengkap": "material-symbols:location-on-outline",
    "Nama Bank": "material-symbols:account-balance-outline",
    "Nomor Rekening": "material-symbols:payments-outline",
    "Nama Pemilik Rekening": "material-symbols:person-edit-outline"
  };
  return icons[field] || "material-symbols:info-outline";
};

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(168, 100, 68, 0.1);
  border-radius: 10px;
}
</style>
