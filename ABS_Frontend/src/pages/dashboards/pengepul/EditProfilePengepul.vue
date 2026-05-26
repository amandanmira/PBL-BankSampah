<template>
  <DashboardLayout title="Profile">
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500 pb-20">
      
      <!-- Top Success Alert -->
      <div 
        v-if="successMessage" 
        class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm"
      >
        <Icon icon="material-symbols:check-circle" class="w-6 h-6 text-green-600 shrink-0" />
        <span class="font-bold text-sm">{{ successMessage }}</span>
      </div>

      <!-- Main Profile Box -->
      <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100 overflow-hidden">
        
        <!-- Tab Headers -->
        <div class="flex border border-stone-200 bg-stone-50/50 rounded-2xl p-1.5 mb-8">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="cn(
              'flex-1 py-3 text-xs md:text-sm font-bold transition-all flex items-center justify-center gap-2 rounded-xl',
              activeTab === tab.id 
                ? 'bg-white border border-[#4A7043] text-[#4A7043] shadow-sm' 
                : 'text-stone-500 hover:text-stone-800 bg-transparent border-transparent'
            )"
          >
            <Icon :icon="tab.icon" class="w-4.5 h-4.5" />
            {{ tab.name }}
          </button>
        </div>

        <!-- Tab Contents -->
        <div class="space-y-6">
          
          <!-- TAB 1: INFORMASI PRIBADI -->
          <div v-if="activeTab === 'pribadi'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-300">
            <div class="grid grid-cols-1 gap-6">
              
              <!-- Username -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:person-outline" class="w-4.5 h-4.5" />
                  Username<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.username" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Username"
                />
                <p v-if="errors.username" class="text-xs text-red-600 font-bold mt-1">{{ errors.username[0] }}</p>
              </div>

              <!-- Nama Lembaga -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:person-outline" class="w-4.5 h-4.5" />
                  Nama Lembaga<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.nama_lembaga" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nama Lembaga"
                />
                <p v-if="errors.nama_lembaga" class="text-xs text-red-600 font-bold mt-1">{{ errors.nama_lembaga[0] }}</p>
              </div>

              <!-- Nama Lengkap -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:person-outline" class="w-4.5 h-4.5" />
                  Nama Lengkap<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.nama" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nama Lengkap"
                />
                <p v-if="errors.nama" class="text-xs text-red-600 font-bold mt-1">{{ errors.nama[0] }}</p>
              </div>

              <!-- Email (Read-only) -->
              <div class="space-y-1 opacity-70">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:description-outline" class="w-4.5 h-4.5" />
                  Email<span class="text-red-500">*</span>
                </label>
                <input 
                  :value="form.email" 
                  type="email" 
                  disabled 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-500 cursor-not-allowed" 
                />
                <p class="text-[10px] font-bold text-stone-400 mt-1 italic">* Email tidak dapat diubah secara mandiri</p>
              </div>

              <!-- Nomor Telepon -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:description-outline" class="w-4.5 h-4.5" />
                  Nomor Telepon<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.no_telp" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nomor Telepon"
                />
                <p v-if="errors.no_telp" class="text-xs text-red-600 font-bold mt-1">{{ errors.no_telp[0] }}</p>
              </div>

              <!-- Alamat Lengkap -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:description-outline" class="w-4.5 h-4.5" />
                  Alamat Lengkap<span class="text-red-500">*</span>
                </label>
                <textarea 
                  v-model="form.alamat" 
                  rows="4" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200 resize-none" 
                  placeholder="Masukkan Alamat Lengkap"
                ></textarea>
                <p v-if="errors.alamat" class="text-xs text-red-600 font-bold mt-1">{{ errors.alamat[0] }}</p>
              </div>

            </div>

            <!-- Submit Button -->
            <div class="pt-6">
              <button 
                @click="updateProfile"
                :disabled="isUpdating"
                class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-4.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50 shadow-lg shadow-[#4A7043]/10 active:scale-[0.99] cursor-pointer"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
                <Icon v-else icon="material-symbols:description-outline" class="w-5 h-5" />
                Simpan Perubahan
              </button>
            </div>
          </div>

          <!-- TAB 2: UBAH PASSWORD -->
          <div v-if="activeTab === 'password'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-300">
            
            <div class="space-y-6">
              
              <!-- Password Baru -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:lock-outline" class="w-4.5 h-4.5" />
                  Password Baru<span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                    placeholder="Masukkan password baru" 
                  />
                  <button @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors cursor-pointer">
                    <Icon :icon="showPassword ? 'material-symbols:visibility-off-outline' : 'material-symbols:visibility-outline'" class="w-5 h-5" />
                  </button>
                </div>
                <p class="text-[10px] font-bold text-stone-400 mt-1">Minimal 6 karakter</p>
                <p v-if="errors.password" class="text-xs text-red-600 font-bold mt-1">{{ errors.password[0] }}</p>
              </div>

              <!-- Konfirmasi Password -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs font-bold text-[#4A7043] uppercase tracking-wider mb-2">
                  <Icon icon="material-symbols:lock-outline" class="w-4.5 h-4.5" />
                  Konfirmasi Password<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="passwordForm.password_confirmation" 
                  :type="showPassword ? 'text' : 'password'" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-4 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Ulangi password baru" 
                />
              </div>

            </div>

            <!-- Submit Button -->
            <div class="pt-6">
              <button 
                @click="updatePassword"
                :disabled="isUpdating || !isPasswordFormValid"
                class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-4.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50 shadow-lg shadow-[#4A7043]/10 active:scale-[0.99] cursor-pointer"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
                <Icon v-else icon="material-symbols:description-outline" class="w-5 h-5" />
                Simpan Perubahan
              </button>
            </div>
          </div>

          <!-- TAB 3: DOKUMEN -->
          <div v-if="activeTab === 'dokumen'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-300">
            
            <p class="text-[#4A7043] text-sm font-bold leading-relaxed mb-6">
              Unggah dokumen pendukung untuk verifikasi akun Anda. Format yang diterima: JPG, PNG, PDF (Maks. 5MB)
            </p>

            <div class="grid grid-cols-1 gap-6">
              
              <!-- Hidden Inputs for File Selection -->
              <input 
                type="file" 
                ref="ktpInput" 
                @change="handleKtpChange" 
                accept="image/*,application/pdf" 
                class="hidden" 
              />
              <input 
                type="file" 
                ref="npwpInput" 
                @change="handleNpwpChange" 
                accept="image/*,application/pdf" 
                class="hidden" 
              />

              <!-- KTP Box -->
              <div class="bg-white rounded-3xl p-6 border border-stone-200/60 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2 text-stone-700">
                    <Icon icon="material-symbols:description-outline" class="w-5 h-5 text-[#4A7043]" />
                    <span class="text-sm font-bold text-stone-800">KTP (Kartu Tanda Penduduk)</span><span class="text-red-500">*</span>
                  </div>
                  <div v-if="form.ktp" class="flex items-center gap-1.5 text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">
                    <Icon icon="material-symbols:check" class="w-3.5 h-3.5" />
                    <span>Sudah diunggah</span>
                  </div>
                </div>

                <!-- Preview container -->
                <div class="bg-[#EEF0ED] rounded-[1.5rem] p-4 flex items-center justify-center h-48 md:h-64 overflow-hidden relative group">
                  <img 
                    v-if="form.ktp && !isKtpPdf" 
                    :src="`${axios.defaults.baseURL}/storage/${form.ktp}`" 
                    class="w-full h-full object-contain rounded-xl hover:scale-105 transition-transform duration-300" 
                    alt="KTP Preview"
                  />
                  <div v-else-if="form.ktp && isKtpPdf" class="flex flex-col items-center gap-3 text-[#4A7043]">
                    <Icon icon="material-symbols:picture-as-pdf-outline" class="w-16 h-16" />
                    <span class="text-xs font-bold">KTP Dokumen (PDF)</span>
                    <a 
                      :href="`${axios.defaults.baseURL}/storage/${form.ktp}`" 
                      target="_blank" 
                      class="text-xs font-black text-[#4A7043] underline hover:text-[#3D5C37]"
                    >
                      Lihat Dokumen
                    </a>
                  </div>
                  <div v-else class="text-stone-400 flex flex-col items-center gap-2">
                    <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12 opacity-40" />
                    <span class="text-xs font-bold">KTP belum diunggah</span>
                  </div>
                </div>

                <button 
                  @click="triggerKtpUpload"
                  :disabled="isUploading"
                  class="w-full bg-[#EEF0ED]/60 hover:bg-[#EEF0ED] text-[#4A7043] border border-stone-200/50 py-3.5 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer"
                >
                  <Icon icon="material-symbols:upload" class="w-4.5 h-4.5" />
                  Ganti KTP
                </button>
              </div>

              <!-- NPWP Box -->
              <div class="bg-white rounded-3xl p-6 border border-stone-200/60 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2 text-stone-700">
                    <Icon icon="material-symbols:description-outline" class="w-5 h-5 text-[#4A7043]" />
                    <span class="text-sm font-bold text-stone-800">NPWP (Nomor Pokok Wajib Pajak)</span><span class="text-red-500">*</span>
                  </div>
                  <div v-if="form.npwp" class="flex items-center gap-1.5 text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">
                    <Icon icon="material-symbols:check" class="w-3.5 h-3.5" />
                    <span>Sudah diunggah</span>
                  </div>
                </div>

                <!-- Preview container -->
                <div class="bg-[#EEF0ED] rounded-[1.5rem] p-4 flex items-center justify-center h-48 md:h-64 overflow-hidden relative group">
                  <img 
                    v-if="form.npwp && !isNpwpPdf" 
                    :src="`${axios.defaults.baseURL}/storage/${form.npwp}`" 
                    class="w-full h-full object-contain rounded-xl hover:scale-105 transition-transform duration-300" 
                    alt="NPWP Preview"
                  />
                  <div v-else-if="form.npwp && isNpwpPdf" class="flex flex-col items-center gap-3 text-[#4A7043]">
                    <Icon icon="material-symbols:picture-as-pdf-outline" class="w-16 h-16" />
                    <span class="text-xs font-bold">NPWP Dokumen (PDF)</span>
                    <a 
                      :href="`${axios.defaults.baseURL}/storage/${form.npwp}`" 
                      target="_blank" 
                      class="text-xs font-black text-[#4A7043] underline hover:text-[#3D5C37]"
                    >
                      Lihat Dokumen
                    </a>
                  </div>
                  <div v-else class="text-stone-400 flex flex-col items-center gap-2">
                    <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12 opacity-40" />
                    <span class="text-xs font-bold">NPWP belum diunggah</span>
                  </div>
                </div>

                <button 
                  @click="triggerNpwpUpload"
                  :disabled="isUploading"
                  class="w-full bg-[#EEF0ED]/60 hover:bg-[#EEF0ED] text-[#4A7043] border border-stone-200/50 py-3.5 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer"
                >
                  <Icon icon="material-symbols:upload" class="w-4.5 h-4.5" />
                  Ganti NPWP
                </button>
              </div>

            </div>

            <!-- Submit Button (Visual Confirm/Back) -->
            <div class="pt-6">
              <button 
                @click="confirmDocuments"
                class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-4.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer"
              >
                <Icon icon="material-symbols:description-outline" class="w-5 h-5" />
                Simpan Perubahan
              </button>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- Success Toast Notification -->
    <transition 
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="toastMessage" 
        class="fixed bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-stone-150 p-5 pr-8 flex items-center gap-3 animate-in slide-in-from-right-4 duration-300"
      >
        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600 shrink-0">
          <Icon icon="material-symbols:check-circle" class="w-5 h-5" />
        </div>
        <div>
          <p class="text-sm font-bold text-stone-800">{{ toastMessage }}</p>
        </div>
      </div>
    </transition>

  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { Icon } from '@iconify/vue';
import { cn } from '@/lib/utils';
import { checkRole } from '@/utils';

// Check role authentication
checkRole('pengepul');

const axios = inject('axios');

// Tab Configuration
const activeTab = ref('pribadi');
const tabs = [
  { id: 'pribadi', name: 'Informasi Pribadi', icon: 'material-symbols:person-outline' },
  { id: 'password', name: 'Ubah Password', icon: 'material-symbols:lock-outline' },
  { id: 'dokumen', name: 'Dokumen', icon: 'material-symbols:description-outline' }
];

// Profile data state
const form = ref({
  nama: '',
  username: '',
  email: '',
  no_telp: '',
  nama_lembaga: '',
  alamat: '',
  ktp: '',
  npwp: ''
});

// Password form state
const passwordForm = ref({
  password: '',
  password_confirmation: ''
});

// Loading & UI States
const isUpdating = ref(false);
const successMessage = ref('');
const toastMessage = ref('');
const showPassword = ref(false);
const errors = ref({});

// Hidden upload triggers
const ktpInput = ref(null);
const npwpInput = ref(null);

// Get Pengepul user from session storage
const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});

// Check if document files are PDFs
const isKtpPdf = computed(() => {
  return form.value.ktp && form.value.ktp.toLowerCase().endsWith('.pdf');
});

const isNpwpPdf = computed(() => {
  return form.value.npwp && form.value.npwp.toLowerCase().endsWith('.pdf');
});

// Fetch Pengepul Profile Info
const fetchProfile = async () => {
  try {
    const id = user.value.pengepul_id;
    if (!id) return;
    
    const res = await axios.get(`/api/pengepul/profile/${id}`);
    form.value = { ...res.data };
  } catch (err) {
    console.error("Gagal mengambil data profil:", err);
  }
};

// Validate password strength & confirmation
const isPasswordFormValid = computed(() => {
  return passwordForm.value.password.length >= 6 && 
         passwordForm.value.password === passwordForm.value.password_confirmation;
});

// Update personal information
const updateProfile = async () => {
  isUpdating.value = true;
  errors.value = {};
  successMessage.value = '';
  
  try {
    const id = user.value.pengepul_id;
    const payload = {
      nama: form.value.nama,
      username: form.value.username,
      no_telp: form.value.no_telp,
      nama_lembaga: form.value.nama_lembaga,
      alamat: form.value.alamat
    };
    
    const res = await axios.put(`/api/pengepul/edit-profile/${id}`, payload);
    
    // Update session storage
    sessionStorage.setItem("user", JSON.stringify(res.data.data));
    form.value = { ...res.data.data };
    
    successMessage.value = "Profil berhasil diperbarui!";
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => successMessage.value = '', 3000);
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      alert("Gagal memperbarui profil: " + (err.response?.data?.message || err.message));
    }
  } finally {
    isUpdating.value = false;
  }
};

// Update Password
const updatePassword = async () => {
  isUpdating.value = true;
  errors.value = {};
  successMessage.value = '';
  
  try {
    const id = user.value.pengepul_id;
    await axios.put(`/api/pengepul/update-password/${id}`, passwordForm.value);
    
    passwordForm.value = { password: '', password_confirmation: '' };
    successMessage.value = "Password berhasil diubah!";
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => successMessage.value = '', 3000);
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      alert("Gagal mengubah password: " + (err.response?.data?.message || err.message));
    }
  } finally {
    isUpdating.value = false;
  }
};

// Document Upload Programmatic Click
const triggerKtpUpload = () => {
  ktpInput.value.click();
};

const triggerNpwpUpload = () => {
  npwpInput.value.click();
};

// Handle file selections & immediately upload
const handleKtpChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert("Ukuran berkas melebihi batas maksimal 5MB.");
    return;
  }

  isUpdating.value = true;
  const formData = new FormData();
  formData.append('ktp', file);

  try {
    const id = user.value.pengepul_id;
    const res = await axios.post(`/api/pengepul/upload-ktp/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    // Update state & session
    form.value.ktp = res.data.data.ktp;
    sessionStorage.setItem("user", JSON.stringify(res.data.data));

    showToast("KTP berhasil diunggah");
  } catch (err) {
    alert("Gagal mengunggah KTP: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
    event.target.value = ''; // Reset input selection
  }
};

const handleNpwpChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert("Ukuran berkas melebihi batas maksimal 5MB.");
    return;
  }

  isUpdating.value = true;
  const formData = new FormData();
  formData.append('npwp', file);

  try {
    const id = user.value.pengepul_id;
    const res = await axios.post(`/api/pengepul/upload-npwp/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    // Update state & session
    form.value.npwp = res.data.data.npwp;
    sessionStorage.setItem("user", JSON.stringify(res.data.data));

    showToast("NPWP berhasil diunggah");
  } catch (err) {
    alert("Gagal mengunggah NPWP: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
    event.target.value = ''; // Reset input selection
  }
};

// Helper to trigger success toast
const showToast = (message) => {
  toastMessage.value = message;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4000);
};

// Visual confirm on Document tab
const confirmDocuments = () => {
  successMessage.value = "Dokumen verifikasi berhasil diperbarui!";
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(() => successMessage.value = '', 3000);
};

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
/* Smooth transition effects */
.animate-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
