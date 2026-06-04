<template>
  <DashboardLayout title="Profile">
    <!-- Main container: max-w-md on mobile, max-w-5xl on desktop -->
    <div class="max-w-md lg:max-w-7xl mx-auto bg-[#F7F7F5] lg:bg-transparent min-h-screen lg:min-h-0 pb-20 font-sans antialiased text-stone-800 relative">
      
      <!-- Spacing and Content Wrapper -->
      <div class="p-4 lg:p-0 space-y-4">
        
        <!-- Notifikasi Berhasil -->
        <div v-if="successMessage" class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-2xl flex items-center gap-2 shadow-sm animate-in fade-in duration-300">
          <Icon icon="material-symbols:check-circle" class="w-5 h-5 shrink-0" />
          <span class="text-xs font-bold">{{ successMessage }}</span>
        </div>

        <!-- Card Kelengkapan Profil -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-stone-200/60 relative overflow-hidden">
          <div class="flex justify-between items-start mb-3">
            <div class="flex items-center gap-2">
              <div class="p-2 bg-[#A86444]/10 rounded-xl text-[#A86444]">
                <Icon icon="material-symbols:trending-up" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="text-sm font-black text-stone-800">Kelengkapan Profil</h2>
                <p class="text-stone-400 text-[10px] font-medium leading-tight">Lengkapi profil Anda untuk mendapatkan pengalaman terbaik menggunakan ABS.</p>
              </div>
            </div>
            <div class="text-right shrink-0">
              <span class="text-2xl font-black text-[#A86444]">{{ completionPercentage }}%</span>
              <p class="text-stone-400 text-[9px] font-bold uppercase tracking-wider">{{ completedFieldsCount }}/{{ totalFieldsCount }} lengkap</p>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden mb-4">
            <div 
              class="h-full bg-[#A86444] transition-all duration-1000 ease-out rounded-full"
              :style="{ width: `${completionPercentage}%` }"
            ></div>
          </div>

          <!-- Missing Fields List -->
          <div v-if="missingFields.length > 0" class="bg-[#FDF8F6] rounded-2xl p-4 border border-[#A86444]/10">
            <div class="flex items-center gap-1.5 mb-2 text-[#A86444]">
              <Icon icon="material-symbols:info-outline" class="w-4 h-4 shrink-0" />
              <span class="text-[10px] font-black uppercase tracking-wider">Field yang Belum Dilengkapi:</span>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
              <div v-for="field in missingFields" :key="field" class="flex items-center gap-1.5 text-stone-500">
                <Icon :icon="getFieldIcon(field)" class="w-3.5 h-3.5 opacity-60 text-[#A86444]" />
                <span class="text-[11px] font-bold leading-none">{{ field }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Tab buttons -->
        <div class="bg-[#EFEFEF] rounded-2xl p-1 flex border border-stone-200/20 gap-1.5">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="cn(
              'flex-1 py-2.5 text-[10px] font-bold rounded-xl transition-all text-center px-1.5',
              activeTab === tab.id 
                ? 'bg-white text-[#4A7043] shadow-xs font-black' 
                : 'text-stone-500 hover:text-stone-700'
            )"
          >
            {{ tab.name }}
          </button>
        </div>

        <!-- Card Content Area -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-stone-200/60 space-y-5">
          
          <!-- TAB 1: INFORMASI PRIBADI -->
          <div v-if="activeTab === 'pribadi'" class="space-y-4 animate-in fade-in duration-300">
            
            <!-- Username (Disabled) -->
            <div class="space-y-1.5 opacity-70">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-[#4A7043]" />
                Username
              </label>
              <input 
                :value="form.username" 
                type="text" 
                disabled 
                class="w-full bg-stone-50 border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-500 cursor-not-allowed" 
              />
            </div>

            <!-- Nama Lengkap -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-[#4A7043]" />
                Nama Lengkap
              </label>
              <input 
                v-model="form.nama" 
                type="text" 
                placeholder="Masukkan nama lengkap"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Email (Disabled) -->
            <div class="space-y-1.5 opacity-70">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:mail-outline" class="w-4 h-4 text-[#4A7043]" />
                Email
              </label>
              <input 
                :value="form.email" 
                type="email" 
                disabled 
                class="w-full bg-stone-50 border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-500 cursor-not-allowed" 
              />
            </div>

            <!-- Nomor Telepon -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:call-outline" class="w-4 h-4 text-[#4A7043]" />
                Nomor Telepon
              </label>
              <input 
                v-model="form.no_telp" 
                type="text" 
                placeholder="Masukkan nomor telepon"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Alamat Lengkap -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-[#4A7043]" />
                Alamat Lengkap
              </label>
              <textarea 
                v-model="form.alamat" 
                rows="3" 
                placeholder="Masukkan alamat lengkap Anda"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all resize-none"
              ></textarea>
            </div>

            <!-- Map Trigger Button -->
            <div class="space-y-2 pt-1">
              <button 
                @click="isMapActive = true"
                class="w-full bg-[#5BA09B] text-white py-3 rounded-2xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm hover:bg-[#4D8884] transition-all active:scale-[0.98]"
              >
                <Icon icon="material-symbols:location-on" class="w-4 h-4" />
                Atur Titik Jemput
              </button>
              <p class="text-[10px] text-stone-400 text-center font-bold">
                {{ form.gmap ? 'Lokasi sudah diatur' : 'Titik lokasi belum diatur' }}
              </p>
            </div>

            <!-- Save Changes Button -->
            <div class="pt-2">
              <button 
                @click="updateProfile"
                :disabled="isUpdating || !isProfileDirty"
                :class="cn(
                  'w-full py-3.5 rounded-2xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm',
                  isProfileDirty 
                    ? 'bg-[#4A7043] text-white hover:bg-[#3D5C37]' 
                    : 'bg-[#D1D5DB] text-stone-500 cursor-not-allowed'
                )"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-4.5 h-4.5" />
                <Icon v-else icon="material-symbols:save" class="w-4.5 h-4.5" />
                {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>

          <!-- TAB 2: REKENING BANK -->
          <div v-if="activeTab === 'bank'" class="space-y-4 animate-in fade-in duration-300">
            
            <!-- Nama Bank -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:account-balance-outline" class="w-4 h-4 text-[#4A7043]" />
                Nama Bank
              </label>
              <input 
                v-model="form.nama_bank" 
                type="text" 
                placeholder="Masukkan nama bank"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Nomor Rekening -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:payments-outline" class="w-4 h-4 text-[#4A7043]" />
                Nomor Rekening
              </label>
              <input 
                v-model="form.no_rekening" 
                type="text" 
                placeholder="Masukkan nomor rekening"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Nama Pemilik Rekening -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:person-edit-outline" class="w-4 h-4 text-[#4A7043]" />
                Nama Pemilik Rekening
              </label>
              <input 
                v-model="form.nama_rek" 
                type="text" 
                placeholder="Sesuai dengan rekening bank"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Tip Alert Block -->
            <div class="bg-[#EBF3FF] p-3 rounded-2xl border border-[#D0E5FF] flex gap-2.5 items-start">
              <Icon icon="material-symbols:lightbulb-outline" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <p class="text-[10px] font-bold text-blue-600 leading-normal">
                Pastikan informasi rekening bank Anda benar untuk memudahkan proses penarikan dana.
              </p>
            </div>

            <!-- Save Changes Button -->
            <div class="pt-2">
              <button 
                @click="updateProfile"
                :disabled="isUpdating || !isProfileDirty"
                :class="cn(
                  'w-full py-3.5 rounded-2xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm',
                  isProfileDirty 
                    ? 'bg-[#4A7043] text-white hover:bg-[#3D5C37]' 
                    : 'bg-[#D1D5DB] text-stone-500 cursor-not-allowed'
                )"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-4.5 h-4.5" />
                <Icon v-else icon="material-symbols:save" class="w-4.5 h-4.5" />
                {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>

          <!-- TAB 3: UBAH PASSWORD -->
          <div v-if="activeTab === 'password'" class="space-y-4 animate-in fade-in duration-300">
            
            <!-- Tip Alert Block -->
            <div class="bg-[#FFF8E6] p-3 rounded-2xl border border-[#FFE8B3] flex gap-2.5 items-start">
              <Icon icon="material-symbols:lock-outline" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
              <p class="text-[10px] font-bold text-amber-700 leading-normal">
                Pastikan password baru Anda kuat dan mudah diingat. Minimal 6 karakter.
              </p>
            </div>

            <!-- Password Saat Ini (Visual only or sent to backend if needed) -->
            <div class="space-y-1.5 lg:hidden">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:lock-open-outline" class="w-4 h-4 text-[#4A7043]" />
                Password Saat Ini
              </label>
              <input 
                v-model="passwordForm.password_current" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Masukkan password saat ini"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Password Baru -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:lock-outline" class="w-4 h-4 text-[#4A7043]" />
                Password Baru
              </label>
              <input 
                v-model="passwordForm.password" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Masukkan password baru"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="space-y-1.5">
              <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                <Icon icon="material-symbols:lock-outline" class="w-4 h-4 text-[#4A7043]" />
                Konfirmasi Password Baru
              </label>
              <input 
                v-model="passwordForm.password_confirmation" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Konfirmasi password baru"
                class="w-full bg-white border border-stone-200 rounded-2xl py-3 px-4 text-xs font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" 
              />
            </div>

            <!-- Show Password Checkbox -->
            <div class="flex items-center gap-2 px-1">
              <input 
                id="show-password-checkbox"
                v-model="showPassword" 
                type="checkbox" 
                class="rounded border-stone-300 text-[#4A7043] focus:ring-[#4A7043] w-4 h-4 cursor-pointer"
              />
              <label for="show-password-checkbox" class="text-xs text-stone-600 font-semibold select-none cursor-pointer">
                Tampilkan password
              </label>
            </div>

            <!-- Save Password Button -->
            <div class="pt-2">
              <button 
                @click="updatePassword"
                :disabled="isUpdating || !isPasswordFormValid"
                :class="cn(
                  'w-full py-3.5 rounded-2xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm',
                  isPasswordFormValid 
                    ? 'bg-[#4A7043] text-white hover:bg-[#3D5C37]' 
                    : 'bg-[#D1D5DB] text-stone-500 cursor-not-allowed'
                )"
              >
                <Icon v-if="isUpdating" icon="line-md:loading-twotone-loop" class="w-4.5 h-4.5" />
                <Icon v-else icon="material-symbols:lock-outline" class="w-4.5 h-4.5" />
                {{ isUpdating ? 'Mengubah...' : 'Ubah Password' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MAP MODAL: ATUR TITIK JEMPUT -->
      <div v-if="isMapActive" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
        <div class="bg-white rounded-3xl w-full max-w-sm max-h-[90vh] flex flex-col overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
          
          <!-- Header -->
          <div class="p-4 border-b border-stone-100 flex justify-between items-start">
            <div class="flex gap-2.5">
              <Icon icon="material-symbols:location-on" class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
              <div>
                <h3 class="text-sm font-black text-stone-800 leading-tight">Atur Titik Jemput</h3>
                <p class="text-[10px] text-stone-400 font-semibold leading-normal">Tentukan lokasi tepat untuk penjemputan sampah</p>
              </div>
            </div>
            <button @click="isMapActive = false" class="w-7 h-7 rounded-full bg-stone-100 flex items-center justify-center text-stone-500 hover:bg-stone-200 transition-all shrink-0">
              <Icon icon="material-symbols:close" class="w-4.5 h-4.5" />
            </button>
          </div>
          
          <!-- Search & Current Location Controls -->
          <div class="p-4 space-y-3 bg-[#FDFBF9] border-b border-stone-100">
            <div class="flex gap-2">
              <div class="relative flex-1">
                <Icon icon="material-symbols:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari alamat atau nama tempat.."
                  class="w-full pl-9 pr-3 py-2 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-[#4A7043] transition-all text-xs font-semibold"
                  @keyup.enter="searchLocation"
                />
              </div>
              <button 
                @click="searchLocation"
                :disabled="isSearching"
                class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#3D5C37] transition-all disabled:opacity-50"
              >
                {{ isSearching ? '...' : 'Cari' }}
              </button>
            </div>

            <button 
              @click="requestLocation" 
              :disabled="isGettingLocation" 
              class="w-full bg-[#5BA09B] text-white py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 hover:bg-[#4D8884] transition-all disabled:opacity-50"
            >
              <Icon v-if="isGettingLocation" icon="line-md:loading-twotone-loop" class="w-4 h-4" />
              <Icon v-else icon="material-symbols:near-me-outline" class="w-4 h-4" />
              Gunakan Lokasi Saat Ini
            </button>
          </div>

          <!-- Address & Lat Long Display -->
          <div class="p-4 bg-white border-b border-stone-100 space-y-1">
            <div class="bg-[#F6F8F5] border border-[#E1EADF] rounded-xl p-3 space-y-1">
              <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Alamat Terpilih:</span>
              <p class="text-xs font-bold text-stone-700 leading-relaxed max-h-16 overflow-y-auto custom-scrollbar">
                {{ form.alamat || 'Belum ada alamat terpilih' }}
              </p>
              <div class="text-[9px] font-mono text-stone-400 font-semibold pt-1">
                Lat: {{ currentCoord.lat.toFixed(6) }}, Lng: {{ currentCoord.lng.toFixed(6) }}
              </div>
            </div>
          </div>

          <!-- Leaflet Interactive Map Container -->
          <div class="flex-1 relative bg-stone-100 min-h-[220px]">
            <div ref="mapContainer" class="absolute inset-0 z-0"></div>
            <!-- Buka di Maps external link button -->
            <a 
              :href="`https://www.google.com/maps?q=${currentCoord.lat},${currentCoord.lng}`"
              target="_blank"
              class="absolute top-3 right-3 z-[400] bg-white text-stone-700 shadow-md px-3 py-1.5 rounded-lg text-[10px] font-bold flex items-center gap-1 border border-stone-200/80 hover:bg-stone-50"
            >
              <Icon icon="material-symbols:open-in-new" class="w-3.5 h-3.5" />
              Buka di Maps
            </a>
          </div>

          <!-- Coordinate Inputs (Optional Manual adjustment) -->
          <div class="p-4 bg-[#F9F9F9] border-t border-stone-100 space-y-2">
            <div class="flex items-center gap-1 text-[#4A7043]">
              <Icon icon="material-symbols:keyboard-outline" class="w-3.5 h-3.5" />
              <span class="text-[10px] font-bold uppercase tracking-wider">Atur Koordinat Manual (Opsional)</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <span class="text-[9px] font-bold text-stone-500 uppercase tracking-wider">Latitude</span>
                <input 
                  v-model.number="currentCoord.lat" 
                  type="number" 
                  step="0.000001"
                  class="w-full bg-white border border-stone-200 rounded-lg py-1.5 px-2 text-[11px] font-bold text-stone-700 focus:outline-none" 
                  @input="updateMarkerPosition"
                />
              </div>
              <div class="space-y-1">
                <span class="text-[9px] font-bold text-stone-500 uppercase tracking-wider">Longitude</span>
                <input 
                  v-model.number="currentCoord.lng" 
                  type="number" 
                  step="0.000001"
                  class="w-full bg-white border border-stone-200 rounded-lg py-1.5 px-2 text-[11px] font-bold text-stone-700 focus:outline-none" 
                  @input="updateMarkerPosition"
                />
              </div>
            </div>
            <div class="text-[9px] text-stone-400 font-semibold flex gap-1 items-start mt-1">
              <Icon icon="material-symbols:info-outline" class="w-3 h-3 text-[#4A7043] shrink-0 mt-0.5" />
              <span>Tips: Gunakan tombol "Buka di Maps" untuk melihat peta interaktif dan mendapatkan koordinat yang lebih presisi.</span>
            </div>
          </div>
          
          <!-- Actions footer -->
          <div class="p-4 border-t border-stone-100 grid grid-cols-3 gap-3 bg-white">
            <button @click="isMapActive = false" class="col-span-1 py-3 rounded-xl border border-stone-200 font-bold text-xs text-stone-500 hover:bg-stone-50 transition-all text-center">
              Batal
            </button>
            <button @click="saveLocation" :disabled="isSavingLocation" class="col-span-2 py-3 rounded-xl bg-[#4A7043] text-white font-bold text-xs shadow-sm hover:bg-[#3D5C37] transition-all disabled:opacity-50 flex items-center justify-center gap-1.5">
              <Icon v-if="isSavingLocation" icon="line-md:loading-twotone-loop" class="w-4 h-4" />
              Konfirmasi Lokasi
            </button>
          </div>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject, watch, nextTick } from "vue";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});
import { checkRole } from '@/utils';
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";

const axios = inject('axios');

checkRole('nasabah');

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
  password_current: "",
  password: "",
  password_confirmation: ""
});

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem("user") || "{}");
  } catch (e) { return {}; }
});

const mapContainer = ref(null);
let map = null;
let marker = null;
const currentCoord = ref({ lat: -6.2088, lng: 106.8456 });
const isGettingLocation = ref(false);
const isSavingLocation = ref(false);
const isSearching = ref(false);

const initMap = async () => {
  await nextTick();
  if (mapContainer.value) {
    if (map) {
      map.remove();
    }
    
    let initialLat = -6.2088;
    let initialLng = 106.8456;
    
    if (form.value.gmap) {
      try {
        const parsed = JSON.parse(form.value.gmap);
        if (parsed.lat && parsed.lng) {
          initialLat = parsed.lat;
          initialLng = parsed.lng;
        }
      } catch(e) {}
    }
    
    currentCoord.value = { lat: initialLat, lng: initialLng };

    map = L.map(mapContainer.value).setView([initialLat, initialLng], 13);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    
    marker.on('dragend', function (e) {
      const pos = marker.getLatLng();
      currentCoord.value = { lat: pos.lat, lng: pos.lng };
      reverseGeocode(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
      marker.setLatLng(e.latlng);
      currentCoord.value = { lat: e.latlng.lat, lng: e.latlng.lng };
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
    
    setTimeout(() => {
      map.invalidateSize();
    }, 100);
  }
};

const updateMarkerPosition = () => {
  if (map && marker && currentCoord.value.lat && currentCoord.value.lng) {
    const newLatLng = new L.LatLng(currentCoord.value.lat, currentCoord.value.lng);
    marker.setLatLng(newLatLng);
    map.setView(newLatLng, map.getZoom());
    reverseGeocode(currentCoord.value.lat, currentCoord.value.lng);
  }
};

const reverseGeocode = async (lat, lng) => {
  try {
    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
    const data = await response.json();
    if (data && data.display_name) {
      form.value.alamat = data.display_name;
    }
  } catch (err) {
    console.error("Gagal reverse geocode:", err);
  }
};

const searchLocation = async () => {
  if (!searchQuery.value) return;
  isSearching.value = true;
  try {
    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}`);
    const data = await response.json();
    if (data && data.length > 0) {
      const first = data[0];
      const lat = parseFloat(first.lat);
      const lng = parseFloat(first.lon);
      currentCoord.value = { lat, lng };
      if (map && marker) {
        map.setView([lat, lng], 15);
        marker.setLatLng([lat, lng]);
      }
      form.value.alamat = first.display_name;
    } else {
      alert("Lokasi tidak ditemukan.");
    }
  } catch (err) {
    console.error("Gagal mencari lokasi:", err);
  } finally {
    isSearching.value = false;
  }
};

const requestLocation = () => {
  if (navigator.geolocation) {
    isGettingLocation.value = true;
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        currentCoord.value = { lat, lng };
        if (map && marker) {
          map.setView([lat, lng], 15);
          marker.setLatLng([lat, lng]);
        }
        reverseGeocode(lat, lng);
        isGettingLocation.value = false;
      },
      (error) => {
        alert("Gagal mendapatkan lokasi. Pastikan izin GPS diberikan.");
        isGettingLocation.value = false;
      }
    );
  } else {
    alert("Geolocation tidak didukung oleh browser ini.");
  }
};

watch(isMapActive, (newVal) => {
  if (newVal) {
    initMap();
    if (!form.value.gmap) {
      setTimeout(() => {
        requestLocation();
      }, 500);
    }
  }
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
  return passwordForm.value.password.length >= 6 && 
         passwordForm.value.password === passwordForm.value.password_confirmation;
});

const updateProfile = async () => {
  isUpdating.value = true;
  try {
    const id = user.value.nasabah_id;
    const res = await axios.put(`/api/nasabah/edit-profile/${id}`, form.value);
    
    localStorage.setItem("user", JSON.stringify(res.data.data));
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
    
    passwordForm.value = { password_current: "", password: "", password_confirmation: "" };
    successMessage.value = "Password berhasil diubah!";
    setTimeout(() => successMessage.value = "", 3000);
  } catch (err) {
    alert("Gagal mengubah password: " + (err.response?.data?.message || err.message));
  } finally {
    isUpdating.value = false;
  }
};

const saveLocation = () => {
  const lat = currentCoord.value.lat;
  const lng = currentCoord.value.lng;
  form.value.gmap = JSON.stringify({ lat, lng });
  isMapActive.value = false;
};

// Logic Kelengkapan Profil
const totalFieldsCount = 9;
const completedFieldsCount = computed(() => {
  let count = 0;
  if (form.value.username) count++;
  if (form.value.nama) count++;
  if (form.value.email) count++;
  if (form.value.no_telp) count++;
  if (form.value.alamat || form.value.gmap) count++;
  if (form.value.nama_bank) count++;
  if (form.value.no_rekening) count++;
  if (form.value.nama_rek) count++;
  // Foto Profil is always empty/missing (counts as 0)
  return count;
});

const completionPercentage = computed(() => {
  return Math.round((completedFieldsCount.value / totalFieldsCount) * 100);
});

const missingFields = computed(() => {
  const missing = [];
  if (!form.value.gmap) missing.push("Titik Jemput");
  if (!form.value.nama_bank) missing.push("Nama Bank");
  if (!form.value.nama_rek) missing.push("Nama Pemilik Rekening");
  missing.push("Foto Profil");
  if (!form.value.no_rekening) missing.push("Nomor Rekening");
  return missing;
});

const getFieldIcon = (field) => {
  const icons = {
    "Username": "material-symbols:person-outline",
    "Nama Lengkap": "material-symbols:badge-outline",
    "Email": "material-symbols:mail-outline",
    "Nomor Telepon": "material-symbols:call-outline",
    "Titik Jemput": "material-symbols:near-me-outline",
    "Nama Bank": "material-symbols:account-balance-outline",
    "Nomor Rekening": "material-symbols:payments-outline",
    "Nama Pemilik Rekening": "material-symbols:person-outline",
    "Foto Profil": "material-symbols:photo-camera-outline"
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
