<template>
  <DashboardLayout title="Penarikan Uang">
    <!-- Main mobile viewport wrapper to simulate/render the layout nicely -->
    <div class="max-w-md mx-auto bg-[#F7F7F5] min-h-screen pb-16 font-sans antialiased text-stone-800">
      
      <!-- Top header spacing if not header is styled. We will just render the content cards. -->
      <div class="p-4 space-y-4">
        
        <!-- Saldo Tersedia Card -->
        <div class="bg-[#56774F] rounded-[1.5rem] p-5 text-white shadow-sm flex justify-between items-center relative overflow-hidden">
          <div class="space-y-1">
            <p class="text-white/80 text-xs font-medium">Saldo Tersedia</p>
            <h2 class="text-3xl font-bold tracking-tight">
              {{ formatRupiahShort(saldoTersedia) }}
            </h2>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
            <Icon icon="material-symbols:account-balance-wallet-outline" class="w-6 h-6 text-white" />
          </div>
        </div>

        <!-- Main Form Container Card -->
        <div class="bg-white rounded-3xl p-5 shadow-sm space-y-6">
          
          <!-- Jumlah Penarikan -->
          <div class="space-y-3">
            <label class="text-xs font-semibold text-stone-500">
              Jumlah Penarikan
            </label>
            
            <div class="relative flex items-center border border-stone-200 rounded-2xl px-4 py-3 bg-white focus-within:border-[#4A7043] focus-within:ring-1 focus-within:ring-[#4A7043] transition-all">
              <span class="text-base font-bold text-stone-400 mr-2">Rp</span>
              <input 
                v-model="displayJumlah"
                @input="handleJumlahInput"
                type="text" 
                placeholder="0"
                class="w-full text-lg font-bold text-stone-800 outline-none bg-transparent"
              />
            </div>
            
            <p class="text-xs text-stone-400">Minimal penarikan: Rp 50.000</p>

            <!-- Preset Amounts Grid -->
            <div class="grid grid-cols-2 gap-2">
              <button 
                v-for="amount in presetAmounts" 
                :key="amount"
                @click="setQuickAmount(amount)"
                type="button"
                :class="cn(
                  'py-2 px-3 rounded-xl text-xs font-bold transition-all text-center',
                  form.jumlah === amount 
                    ? 'bg-[#EEF3ED] text-[#4A7043] border border-[#4A7043]' 
                    : 'bg-[#F5F5F3] text-stone-600 hover:bg-[#EEF3ED]/50 border border-transparent'
                )"
              >
                {{ formatRupiahShort(amount) }}
              </button>
            </div>
          </div>

          <!-- Pilih Rekening Tujuan -->
          <div class="space-y-3">
            <label class="text-xs font-semibold text-stone-500">
              Pilih Rekening Tujuan
            </label>

            <div class="space-y-3">
              <!-- Rekening di Profil -->
              <button 
                @click="setTipeRekening('profil')"
                type="button"
                :class="cn(
                  'w-full flex items-start gap-3 p-4 rounded-2xl border transition-all text-left relative overflow-hidden',
                  tipeRekening === 'profil'
                    ? 'bg-[#EEF3ED] border-[#4A7043]'
                    : 'bg-white border-stone-200'
                )"
              >
                <div :class="cn(
                  'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all',
                  tipeRekening === 'profil' ? 'bg-[#4A7043]/10 text-[#4A7043]' : 'bg-stone-100 text-stone-400'
                )">
                  <Icon icon="material-symbols:person-outline" class="w-5 h-5" />
                </div>
                <div class="flex-1">
                  <h4 class="font-bold text-stone-800 text-sm">Rekening di Profil</h4>
                  <p class="text-xs text-stone-400 leading-tight">Gunakan rekening tersimpan</p>
                  
                  <!-- Show profile details if complete and selected -->
                  <div v-if="isProfileComplete" class="mt-3 text-xs text-stone-600 space-y-0.5">
                    <p class="font-bold text-stone-800">{{ rekeningProfil.nama_bank }}</p>
                    <p class="font-mono text-stone-700">{{ rekeningProfil.no_rekening }}</p>
                    <p class="text-stone-500">{{ rekeningProfil.nama_rek }}</p>
                  </div>
                </div>
              </button>

              <!-- Rekening Lain -->
              <button 
                @click="setTipeRekening('lain')"
                type="button"
                :class="cn(
                  'w-full flex items-start gap-3 p-4 rounded-2xl border transition-all text-left',
                  tipeRekening === 'lain'
                    ? 'bg-[#EEF3ED] border-[#4A7043]'
                    : 'bg-white border-stone-200'
                )"
              >
                <div :class="cn(
                  'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all',
                  tipeRekening === 'lain' ? 'bg-[#4A7043]/10 text-[#4A7043]' : 'bg-stone-100 text-stone-400'
                )">
                  <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5" />
                </div>
                <div class="flex-1">
                  <h4 class="font-bold text-stone-800 text-sm">Rekening Lain</h4>
                  <p class="text-xs text-stone-400 leading-tight">Input rekening baru</p>
                </div>
              </button>
            </div>

            <!-- Profile Incomplete Alert (when trying to select Profil, or generally informing) -->
            <div v-if="tipeRekening === 'profil' && !isProfileComplete" class="bg-[#FFFBEB] border border-amber-200 rounded-2xl p-4 flex flex-col gap-1 text-xs">
              <span class="text-amber-800 font-medium">Anda belum mengisi data rekening di profil.</span>
              <router-link to="/dashboard-nasabah/edit-profile" class="text-amber-800 font-bold underline decoration-1">Lengkapi profil Anda</router-link>
            </div>

            <!-- Rekening Lain Input Fields Container -->
            <div v-if="tipeRekening === 'lain'" class="bg-[#F7F7F5] rounded-2xl p-4 space-y-4 border border-stone-100">
              <!-- Nama Bank Select/Dropdown -->
              <div class="space-y-1.5">
                <label class="text-[11px] font-semibold text-stone-500 flex items-center gap-1.5">
                  <Icon icon="material-symbols:domain" class="w-4 h-4 text-stone-400" />
                  Nama Bank
                </label>
                <div class="relative">
                  <select 
                    v-model="form.nama_bank" 
                    class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm text-stone-700 focus:outline-none focus:border-[#4A7043] appearance-none cursor-pointer font-medium"
                  >
                    <option value="" disabled selected>Pilih Bank</option>
                    <option value="Bank BRI">Bank BRI</option>
                    <option value="Bank BCA">Bank BCA</option>
                    <option value="Bank Mandiri">Bank Mandiri</option>
                    <option value="Bank BNI">Bank BNI</option>
                    <option value="Bank BSI">Bank Syariah Indonesia (BSI)</option>
                    <option value="CIMB Niaga">CIMB Niaga</option>
                  </select>
                  <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-stone-400">
                    <Icon icon="material-symbols:keyboard-arrow-down-rounded" class="w-5 h-5" />
                  </div>
                </div>
              </div>

              <!-- Nomor Rekening Input -->
              <div class="space-y-1.5">
                <label class="text-[11px] font-semibold text-stone-500 flex items-center gap-1.5">
                  <Icon icon="material-symbols:credit-card-outline" class="w-4 h-4 text-stone-400" />
                  Nomor Rekening
                </label>
                <input 
                  v-model="form.no_rekening" 
                  type="text" 
                  placeholder="Masukkan nomor rekening" 
                  class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043]" 
                />
              </div>

              <!-- Nama Pemilik Rekening Input -->
              <div class="space-y-1.5">
                <label class="text-[11px] font-semibold text-stone-500 flex items-center gap-1.5">
                  <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-stone-400" />
                  Nama Pemilik Rekening
                </label>
                <input 
                  v-model="form.nama_rek" 
                  type="text" 
                  placeholder="Sesuai dengan rekening bank" 
                  class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043]" 
                />
              </div>
            </div>
          </div>

          <!-- Submit Button: Tarik Saldo -->
          <button 
            @click="submitRequest"
            :disabled="isSubmitting || !isFormValid"
            type="button"
            :class="cn(
              'w-full py-3.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98]',
              isFormValid 
                ? 'bg-[#4A7043] text-white shadow-md hover:bg-[#3D5C37]' 
                : 'bg-[#D2D6DC] text-white cursor-not-allowed shadow-none'
            )"
          >
            <Icon v-if="isSubmitting" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
            <span>Tarik Saldo</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[2rem] w-full max-w-md p-8 flex flex-col items-center text-center shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
          <Icon icon="material-symbols:check-circle-outline" class="w-12 h-12 text-green-600" />
        </div>
        
        <h3 class="text-xl font-bold text-stone-800 mb-2">Request Berhasil! 💰</h3>
        <p class="text-xs text-stone-500 mb-6 px-2 leading-relaxed">
          Permintaan penarikan Anda telah dikirim ke petugas. Kami akan segera memprosesnya.
        </p>
        
        <div class="w-full bg-[#F9F9F7] rounded-2xl p-4 border border-stone-100 mb-6">
           <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-1">Tracking ID</p>
           <p class="text-lg font-black text-[#4A7043] tracking-wider">
             #WD-{{ String(newPenarikanId).padStart(3, '0') }}
           </p>
        </div>
        
        <div class="flex flex-col w-full gap-2.5">
          <button @click="showSuccessModal = false; router.push('/dashboard-nasabah/penarikan-saya')" class="w-full py-3.5 rounded-xl bg-[#4A7043] text-white font-bold text-xs shadow-md hover:bg-[#3D5C37] transition-all">
            Lihat Tracking
          </button>
          <button @click="showSuccessModal = false; router.push('/dashboard-nasabah')" class="w-full py-3.5 rounded-xl border border-stone-200 text-stone-500 font-bold text-xs hover:bg-stone-50 transition-all">
            Kembali ke Dashboard
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";

const axios = inject('axios');
const router = useRouter();

const saldoTersedia = ref(0);
const completionPercentage = ref(0);
const isProfileComplete = computed(() => completionPercentage.value === 100);
const rekeningProfil = ref({
  nama_bank: "",
  no_rekening: "",
  nama_rek: ""
});

const tipeRekening = ref('profil'); // 'profil' or 'lain'
const isSubmitting = ref(false);
const showSuccessModal = ref(false);
const newPenarikanId = ref(null); // Menyimpan ID penarikan dari database

const form = ref({
  jumlah: null,
  nama_bank: "",
  no_rekening: "",
  nama_rek: ""
});

const displayJumlah = ref("");
const presetAmounts = [50000, 100000, 200000, 500000];

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val);
};

const formatRupiahShort = (val) => {
  return "Rp " + val.toLocaleString('id-ID');
};

const handleJumlahInput = (e) => {
  let val = e.target.value.replace(/[^0-9]/g, '');
  form.value.jumlah = val ? parseInt(val) : 0;
  displayJumlah.value = val ? parseInt(val).toLocaleString('id-ID') : "";
};

const setQuickAmount = (amount) => {
  form.value.jumlah = amount;
  displayJumlah.value = amount.toLocaleString('id-ID');
  
  if (tipeRekening.value === 'profil' && isProfileComplete.value) {
    form.value.nama_bank = rekeningProfil.value.nama_bank;
    form.value.no_rekening = rekeningProfil.value.no_rekening;
    form.value.nama_rek = rekeningProfil.value.nama_rek;
  }
};

const setTipeRekening = (tipe) => {
  tipeRekening.value = tipe;
  if (tipe === 'profil' && isProfileComplete.value) {
    form.value.nama_bank = rekeningProfil.value.nama_bank;
    form.value.no_rekening = rekeningProfil.value.no_rekening;
    form.value.nama_rek = rekeningProfil.value.nama_rek;
  } else if (tipe === 'lain') {
    form.value.nama_bank = "";
    form.value.no_rekening = "";
    form.value.nama_rek = "";
  }
};

const isFormValid = computed(() => {
  const isAmountValid = form.value.jumlah >= 50000;
  const isRekeningValid = form.value.nama_bank && form.value.no_rekening && form.value.nama_rek;
  
  if (tipeRekening.value === 'profil') {
    return isAmountValid && isProfileComplete.value;
  }
  return isAmountValid && isRekeningValid;
});

const fetchData = async () => {
  try {
    const res = await axios.get('/api/nasabah/penarikan-data');
    saldoTersedia.value = res.data.saldo_tersedia;
    completionPercentage.value = res.data.completion_percentage;
    rekeningProfil.value = res.data.rekening_profil;
    
    // Auto fill if profile complete
    if (isProfileComplete.value) {
      setTipeRekening('profil');
    } else {
      setTipeRekening('lain');
    }
  } catch (err) {
    console.error("Failed to fetch penarikan data:", err);
  }
};

const submitRequest = async () => {
  if (!isFormValid.value) return;
  
  isSubmitting.value = true;
  try {
    const res = await axios.post('/api/nasabah/request-penarikan', form.value);
    
    // Menangkap penarikan_id dari response data backend
    newPenarikanId.value = res.data.data.penarikan_id;
    
    showSuccessModal.value = true;
    // Refresh data saldo
    fetchData();
  } catch (err) {
    alert(err.response?.data?.message || "Gagal mengirim request penarikan.");
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
input::placeholder {
  color: #D1D5DB;
}
</style>