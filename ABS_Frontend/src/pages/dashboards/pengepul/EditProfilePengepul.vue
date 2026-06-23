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
      <div class="bg-white rounded-3xl p-4 md:p-8 shadow-sm border border-stone-100 overflow-hidden">
        
        <!-- Tab Headers -->
        <div class="flex border border-stone-200 bg-stone-50/50 rounded-2xl p-1 mb-6">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="cn(
              'py-2.5 text-xs md:text-sm font-bold transition-all duration-300 flex items-center justify-center gap-1.5 rounded-xl cursor-pointer',
              activeTab === tab.id 
                ? 'flex-1 bg-white border border-[#4A7043] text-[#4A7043] shadow-sm px-4' 
                : 'flex-none w-12 md:flex-1 text-stone-500 hover:text-stone-850 bg-transparent border-transparent'
            )"
          >
            <Icon :icon="tab.icon" class="w-4.5 h-4.5 shrink-0" />
            <span :class="[activeTab === tab.id ? 'inline' : 'hidden md:inline']">{{ tab.name }}</span>
          </button>
        </div>

        <!-- Tab Contents -->
        <div class="space-y-5">
          
          <!-- TAB 1: INFORMASI PRIBADI -->
          <div v-if="activeTab === 'pribadi'" class="space-y-5 animate-in slide-in-from-bottom-4 duration-300">
            <div class="grid grid-cols-1 gap-5">
              
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-stone-500" />
                  Username<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.username" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Username"
                />
                <p v-if="errors.username" class="text-xs text-red-600 font-bold mt-1">{{ errors.username[0] }}</p>
              </div>

              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-stone-500" />
                  Nama Lembaga<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.nama_lembaga" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nama Lembaga"
                />
                <p v-if="errors.nama_lembaga" class="text-xs text-red-600 font-bold mt-1">{{ errors.nama_lembaga[0] }}</p>
              </div>

              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-stone-500" />
                  Nama Lengkap<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.nama" 
                  type="text" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nama Lengkap"
                />
                <p v-if="errors.nama" class="text-xs text-red-600 font-bold mt-1">{{ errors.nama[0] }}</p>
              </div>

              <div class="space-y-1 opacity-70">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:description-outline" class="w-4 h-4 text-stone-500" />
                  Email<span class="text-red-500">*</span>
                </label>
                <input 
                  :value="form.email" 
                  type="email" 
                  disabled 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-500 cursor-not-allowed" 
                />
                <p class="text-[10px] font-bold text-stone-400 mt-1 italic">* Email tidak dapat diubah secara mandiri</p>
              </div>

              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:description-outline" class="w-4 h-4 text-stone-500" />
                  Nomor Telepon<span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.no_telp" 
                  type="tel" 
                  @input="form.no_telp = $event.target.value.replace(/[^\d+]/g, '')"
                  maxlength="15"
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                  placeholder="Masukkan Nomor Telepon"
                />
                <p v-if="errors.no_telp" class="text-xs text-red-600 font-bold mt-1">{{ errors.no_telp[0] }}</p>
              </div>

              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:description-outline" class="w-4 h-4 text-stone-500" />
                  Alamat Lengkap<span class="text-red-500">*</span>
                </label>
                <textarea 
                  v-model="form.alamat" 
                  rows="3" 
                  class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200 resize-none" 
                  placeholder="Masukkan alamat lengkap"
                ></textarea>
                <p v-if="errors.alamat" class="text-xs text-red-600 font-bold mt-1">{{ errors.alamat[0] }}</p>
              </div>

            </div>

            <div class="pt-4">
              <button 
                @click="updateProfile"
                :disabled="isUpdating"
                class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-3 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50 shadow-sm cursor-pointer"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
                <Icon v-else icon="material-symbols:save-outline" class="w-5 h-5" />
                Simpan Perubahan
              </button>
            </div>
          </div>

          <!-- TAB 2: UBAH PASSWORD -->
          <div v-if="activeTab === 'password'" class="space-y-5 animate-in slide-in-from-bottom-4 duration-300">

            <!-- Info banner -->
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3.5">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
              <p class="text-xs font-bold text-blue-700 leading-relaxed">
                Untuk keamanan akun, masukkan password lama Anda terlebih dahulu sebelum membuat password baru.
              </p>
            </div>
            
            <div class="space-y-5">

              <!-- Password Lama -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:lock-outline" class="w-4 h-4 text-stone-500" />
                  Password Lama<span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.password_current"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    :class="[
                      'w-full border-0 rounded-xl py-2.5 px-3.5 pr-11 text-xs md:text-sm font-semibold focus:outline-none focus:ring-2 transition-all duration-200',
                      errors.password_current
                        ? 'bg-red-50 text-red-700 focus:ring-red-400/30'
                        : 'bg-[#EEF0ED] text-stone-700 focus:ring-[#4A7043]/20 focus:bg-white'
                    ]"
                    placeholder="Masukkan password lama Anda"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors cursor-pointer"
                  >
                    <Icon :icon="showCurrentPassword ? 'material-symbols:visibility-off-outline' : 'material-symbols:visibility-outline'" class="w-5 h-5" />
                  </button>
                </div>
                <!-- Error dari server (password salah) -->
                <p v-if="errors.password_current" class="text-xs text-red-600 font-bold mt-1 flex items-center gap-1.5">
                  <Icon icon="material-symbols:error-outline" class="w-3.5 h-3.5" />
                  {{ errors.password_current[0] }}
                </p>
              </div>

              <!-- Divider -->
              <div class="flex items-center gap-3 py-1">
                <div class="flex-1 h-px bg-stone-100"></div>
                <span class="text-[10px] font-black text-stone-300 uppercase tracking-widest">Password Baru</span>
                <div class="flex-1 h-px bg-stone-100"></div>
              </div>

              <!-- Password Baru -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:lock-reset" class="w-4 h-4 text-stone-500" />
                  Password Baru<span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    class="w-full bg-[#EEF0ED] border-0 rounded-xl py-2.5 px-3.5 pr-11 text-xs md:text-sm font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:bg-white transition-all duration-200" 
                    placeholder="Buat password baru (min. 6 karakter)"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors cursor-pointer"
                  >
                    <Icon :icon="showPassword ? 'material-symbols:visibility-off-outline' : 'material-symbols:visibility-outline'" class="w-5 h-5" />
                  </button>
                </div>
                <!-- Strength indicator -->
                <div v-if="passwordForm.password.length > 0" class="flex gap-1.5 mt-2">
                  <div
                    v-for="i in 4" :key="i"
                    :class="[
                      'h-1 flex-1 rounded-full transition-all duration-300',
                      passwordStrength >= i
                        ? passwordStrength <= 1 ? 'bg-red-400'
                          : passwordStrength === 2 ? 'bg-orange-400'
                          : passwordStrength === 3 ? 'bg-yellow-400'
                          : 'bg-green-500'
                        : 'bg-stone-100'
                    ]"
                  ></div>
                </div>
                <p v-if="passwordForm.password.length > 0" :class="[
                  'text-[10px] font-bold mt-1',
                  passwordStrength <= 1 ? 'text-red-500' : passwordStrength === 2 ? 'text-orange-500' : passwordStrength === 3 ? 'text-yellow-600' : 'text-green-600'
                ]">
                  {{
                    passwordStrength <= 1 ? 'Terlalu lemah' :
                    passwordStrength === 2 ? 'Cukup' :
                    passwordStrength === 3 ? 'Bagus' : 'Kuat'
                  }}
                </p>
                <p v-if="errors.password" class="text-xs text-red-600 font-bold mt-1">{{ errors.password[0] }}</p>
              </div>

              <!-- Konfirmasi Password Baru -->
              <div class="space-y-1">
                <label class="flex items-center gap-2 text-xs md:text-sm font-bold text-stone-700 mb-1.5">
                  <Icon icon="material-symbols:lock-reset" class="w-4 h-4 text-stone-500" />
                  Konfirmasi Password Baru<span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.password_confirmation" 
                    :type="showPassword ? 'text' : 'password'"
                    :class="[
                      'w-full border-0 rounded-xl py-2.5 px-3.5 pr-11 text-xs md:text-sm font-semibold focus:outline-none focus:ring-2 transition-all duration-200',
                      passwordForm.password_confirmation.length > 0 && passwordForm.password !== passwordForm.password_confirmation
                        ? 'bg-red-50 text-red-700 focus:ring-red-400/30'
                        : passwordForm.password_confirmation.length > 0 && passwordForm.password === passwordForm.password_confirmation
                        ? 'bg-green-50 text-green-700 focus:ring-green-400/30'
                        : 'bg-[#EEF0ED] text-stone-700 focus:ring-[#4A7043]/20 focus:bg-white'
                    ]"
                    placeholder="Ulangi password baru"
                  />
                  <!-- Match indicator icon -->
                  <div class="absolute right-3.5 top-1/2 -translate-y-1/2">
                    <Icon
                      v-if="passwordForm.password_confirmation.length > 0 && passwordForm.password === passwordForm.password_confirmation"
                      icon="material-symbols:check-circle"
                      class="w-5 h-5 text-green-500"
                    />
                    <Icon
                      v-else-if="passwordForm.password_confirmation.length > 0 && passwordForm.password !== passwordForm.password_confirmation"
                      icon="material-symbols:cancel"
                      class="w-5 h-5 text-red-400"
                    />
                  </div>
                </div>
                <p
                  v-if="passwordForm.password_confirmation.length > 0 && passwordForm.password !== passwordForm.password_confirmation"
                  class="text-xs text-red-600 font-bold mt-1 flex items-center gap-1.5"
                >
                  <Icon icon="material-symbols:error-outline" class="w-3.5 h-3.5" />
                  Password tidak cocok
                </p>
              </div>

            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button 
                @click="updatePassword"
                :disabled="isUpdating || !isPasswordFormValid"
                class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-3 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50 shadow-sm cursor-pointer"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
                <Icon v-else icon="material-symbols:lock-reset" class="w-5 h-5" />
                Perbarui Password
              </button>
            </div>
          </div>

          <!-- TAB 3: DOKUMEN -->
          <div v-if="activeTab === 'dokumen'" class="space-y-6 animate-in slide-in-from-bottom-4 duration-300">
            
            <p class="text-[#4A7043] text-sm font-bold leading-relaxed mb-6">
              Unggah dokumen pendukung untuk verifikasi akun Anda. Format yang diterima: JPG, PNG, PDF (Maks. 5MB)
            </p>

            <div class="grid grid-cols-1 gap-6">
              
              <input type="file" ref="ktpInput" @change="handleKtpChange" accept="image/*,application/pdf" class="hidden" />
              <input type="file" ref="npwpInput" @change="handleNpwpChange" accept="image/*,application/pdf" class="hidden" />

              <!-- KTP Box -->
              <div class="bg-white rounded-3xl p-5 border border-stone-200/60 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2 text-stone-700">
                    <Icon icon="material-symbols:description-outline" class="w-5 h-5 text-[#4A7043]" />
                    <span class="text-sm font-bold text-stone-850">KTP (Kartu Tanda Penduduk)</span><span class="text-red-500">*</span>
                  </div>
                  <div v-if="form.ktp" class="flex items-center gap-1.5 text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">
                    <Icon icon="material-symbols:check" class="w-3.5 h-3.5" />
                    <span>Sudah diunggah</span>
                  </div>
                </div>
                <div class="bg-[#EEF0ED] rounded-[1.5rem] p-4 flex items-center justify-center h-48 md:h-64 overflow-hidden relative group">
                  <img v-if="form.ktp && !isKtpPdf" :src="`${axios.defaults.baseURL}/storage/${form.ktp}`" class="w-full h-full object-contain rounded-xl hover:scale-105 transition-transform duration-300" alt="KTP Preview" />
                  <div v-else-if="form.ktp && isKtpPdf" class="flex flex-col items-center gap-3 text-[#4A7043]">
                    <Icon icon="material-symbols:picture-as-pdf-outline" class="w-16 h-16" />
                    <span class="text-xs font-bold">KTP Dokumen (PDF)</span>
                    <a :href="`${axios.defaults.baseURL}/storage/${form.ktp}`" target="_blank" class="text-xs font-black text-[#4A7043] underline hover:text-[#3D5C37]">Lihat Dokumen</a>
                  </div>
                  <div v-else class="text-stone-400 flex flex-col items-center gap-2">
                    <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12 opacity-40" />
                    <span class="text-xs font-bold">KTP belum diunggah</span>
                  </div>
                </div>
                <button @click="triggerKtpUpload" :disabled="isUploading" class="w-full bg-[#EEF0ED]/65 hover:bg-[#EEF0ED] text-[#4A7043] border border-stone-200/50 py-3 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer">
                  <Icon icon="material-symbols:upload" class="w-4.5 h-4.5" />
                  Ganti KTP
                </button>
              </div>

              <!-- NPWP Box -->
              <div class="bg-white rounded-3xl p-5 border border-stone-200/60 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2 text-stone-700">
                    <Icon icon="material-symbols:description-outline" class="w-5 h-5 text-[#4A7043]" />
                    <span class="text-sm font-bold text-stone-850">NPWP (Nomor Pokok Wajib Pajak)</span><span class="text-red-500">*</span>
                  </div>
                  <div v-if="form.npwp" class="flex items-center gap-1.5 text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">
                    <Icon icon="material-symbols:check" class="w-3.5 h-3.5" />
                    <span>Sudah diunggah</span>
                  </div>
                </div>
                <div class="bg-[#EEF0ED] rounded-[1.5rem] p-4 flex items-center justify-center h-48 md:h-64 overflow-hidden relative group">
                  <img v-if="form.npwp && !isNpwpPdf" :src="`${axios.defaults.baseURL}/storage/${form.npwp}`" class="w-full h-full object-contain rounded-xl hover:scale-105 transition-transform duration-300" alt="NPWP Preview" />
                  <div v-else-if="form.npwp && isNpwpPdf" class="flex flex-col items-center gap-3 text-[#4A7043]">
                    <Icon icon="material-symbols:picture-as-pdf-outline" class="w-16 h-16" />
                    <span class="text-xs font-bold">NPWP Dokumen (PDF)</span>
                    <a :href="`${axios.defaults.baseURL}/storage/${form.npwp}`" target="_blank" class="text-xs font-black text-[#4A7043] underline hover:text-[#3D5C37]">Lihat Dokumen</a>
                  </div>
                  <div v-else class="text-stone-400 flex flex-col items-center gap-2">
                    <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12 opacity-40" />
                    <span class="text-xs font-bold">NPWP belum diunggah</span>
                  </div>
                </div>
                <button @click="triggerNpwpUpload" :disabled="isUploading" class="w-full bg-[#EEF0ED]/65 hover:bg-[#EEF0ED] text-[#4A7043] border border-stone-200/50 py-3 rounded-xl font-bold text-xs md:text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer">
                  <Icon icon="material-symbols:upload" class="w-4.5 h-4.5" />
                  Ganti NPWP
                </button>
              </div>

            </div>

            <div class="pt-6">
              <button @click="confirmDocuments" class="w-full bg-[#4A7043] hover:bg-[#3D5C37] text-white py-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.99] cursor-pointer">
                <Icon icon="material-symbols:save-outline" class="w-5 h-5" />
                Simpan Perubahan
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="toastMessage" class="fixed bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-stone-150 p-5 pr-8 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600 shrink-0">
          <Icon icon="material-symbols:check-circle" class="w-5 h-5" />
        </div>
        <p class="text-sm font-bold text-stone-800">{{ toastMessage }}</p>
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

checkRole('pengepul');

const axios = inject('axios');

const activeTab = ref('pribadi');
const tabs = [
  { id: 'pribadi',  name: 'Informasi Pribadi', icon: 'material-symbols:person-outline' },
  { id: 'password', name: 'Ubah Password',     icon: 'material-symbols:lock-outline' },
  { id: 'dokumen',  name: 'Dokumen',            icon: 'material-symbols:description-outline' }
];

const form = ref({
  nama: '', username: '', email: '',
  no_telp: '', nama_lembaga: '', alamat: '',
  ktp: '', npwp: ''
});

// ── Password form sekarang punya 3 field ─────────────────────────────────────
const passwordForm = ref({
  password_current:      '',   // ← field baru: password lama
  password:              '',
  password_confirmation: ''
});

const isUpdating  = ref(false);
const isUploading = ref(false);
const successMessage = ref('');
const toastMessage   = ref('');
const errors         = ref({});

// Visibility toggles
const showCurrentPassword = ref(false);
const showPassword        = ref(false);

const ktpInput  = ref(null);
const npwpInput = ref(null);

const user = computed(() => {
  try { return JSON.parse(sessionStorage.getItem("user") || "{}"); }
  catch (e) { return {}; }
});

const isKtpPdf  = computed(() => form.value.ktp  && form.value.ktp.toLowerCase().endsWith('.pdf'));
const isNpwpPdf = computed(() => form.value.npwp && form.value.npwp.toLowerCase().endsWith('.pdf'));

// ── Password strength meter ───────────────────────────────────────────────────
const passwordStrength = computed(() => {
  const p = passwordForm.value.password;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 6)  score++;
  if (p.length >= 10) score++;
  if (/[A-Z]/.test(p) || /[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
});

// ── Form valid: semua 3 field terisi, password baru cocok, min 6 char ─────────
const isPasswordFormValid = computed(() => {
  return (
    passwordForm.value.password_current.length > 0 &&
    passwordForm.value.password.length >= 6 &&
    passwordForm.value.password === passwordForm.value.password_confirmation
  );
});

// ── Fetch profile ─────────────────────────────────────────────────────────────
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

// ── Update profil ─────────────────────────────────────────────────────────────
const updateProfile = async () => {
  isUpdating.value = true;
  errors.value = {};
  successMessage.value = '';
  try {
    const id = user.value.pengepul_id;
    const payload = {
      nama: form.value.nama, username: form.value.username,
      no_telp: form.value.no_telp, nama_lembaga: form.value.nama_lembaga,
      alamat: form.value.alamat
    };
    const res = await axios.put(`/api/pengepul/edit-profile/${id}`, payload);
    sessionStorage.setItem("user", JSON.stringify(res.data.data));
    form.value = { ...res.data.data };
    successMessage.value = "Profil berhasil diperbarui!";
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => successMessage.value = '', 3000);
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors;
    else alert("Gagal memperbarui profil: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
  }
};

// ── Update password — sekarang kirim password_current ke backend ──────────────
const updatePassword = async () => {
  isUpdating.value = true;
  errors.value = {};
  successMessage.value = '';
  try {
    const id = user.value.pengepul_id;
    await axios.put(`/api/pengepul/update-password/${id}`, {
      password_current:      passwordForm.value.password_current,
      password:              passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });
    // Reset semua field setelah sukses
    passwordForm.value = { password_current: '', password: '', password_confirmation: '' };
    successMessage.value = "Password berhasil diubah!";
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => successMessage.value = '', 3000);
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors;
    } else if (err.response?.status === 400) {
      // Backend mengembalikan 400 jika password lama salah
      errors.value = { password_current: [err.response.data.message] };
    } else {
      alert("Gagal mengubah password: " + (err.response?.data?.message || err.message));
    }
  } finally {
    isUpdating.value = false;
  }
};

// ── Document upload helpers ───────────────────────────────────────────────────
const triggerKtpUpload  = () => ktpInput.value.click();
const triggerNpwpUpload = () => npwpInput.value.click();

const handleKtpChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { alert("Ukuran berkas melebihi batas maksimal 5MB."); return; }
  isUpdating.value = true;
  const formData = new FormData();
  formData.append('ktp', file);
  try {
    const id = user.value.pengepul_id;
    const res = await axios.post(`/api/pengepul/upload-ktp/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    form.value.ktp = res.data.data.ktp;
    sessionStorage.setItem("user", JSON.stringify(res.data.data));
    showToast("KTP berhasil diunggah");
  } catch (err) {
    alert("Gagal mengunggah KTP: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
    event.target.value = '';
  }
};

const handleNpwpChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { alert("Ukuran berkas melebihi batas maksimal 5MB."); return; }
  isUpdating.value = true;
  const formData = new FormData();
  formData.append('npwp', file);
  try {
    const id = user.value.pengepul_id;
    const res = await axios.post(`/api/pengepul/upload-npwp/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    form.value.npwp = res.data.data.npwp;
    sessionStorage.setItem("user", JSON.stringify(res.data.data));
    showToast("NPWP berhasil diunggah");
  } catch (err) {
    alert("Gagal mengunggah NPWP: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
    event.target.value = '';
  }
};

const showToast = (message) => {
  toastMessage.value = message;
  setTimeout(() => toastMessage.value = '', 4000);
};

const confirmDocuments = () => {
  successMessage.value = "Dokumen verifikasi berhasil diperbarui!";
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(() => successMessage.value = '', 3000);
};

onMounted(() => { fetchProfile(); });
</script>

<style scoped>
.animate-in {
  animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>