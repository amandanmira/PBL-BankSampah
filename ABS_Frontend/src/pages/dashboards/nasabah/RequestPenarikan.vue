<template>
  <DashboardLayout title="Penarikan Uang">
    <div class="p-6 max-w-5xl mx-auto space-y-6 font-sans antialiased text-stone-800">
      
      <!-- Main Content Card -->
      <div class="bg-white rounded-3xl p-8 shadow-sm border border-stone-200/60 space-y-6">
        
        <!-- Saldo Tersedia Card -->
        <div class="bg-[#5D7A56] rounded-3xl p-6 text-white flex justify-between items-center relative overflow-hidden shadow-sm">
          <div class="space-y-1">
            <p class="text-white/80 text-sm font-medium">Saldo Tersedia</p>
            <h2 class="text-3xl font-bold tracking-tight">
              {{ formatRupiahShort(saldoTersedia) }}
            </h2>
          </div>
          <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center border border-white/20">
            <Icon icon="material-symbols:account-balance-wallet-outline" class="w-6 h-6 text-white" />
          </div>
        </div>

        <!-- Jumlah Penarikan -->
        <div class="space-y-3">
          <label class="text-sm font-semibold text-stone-500">
            Jumlah Penarikan
          </label>
          
          <div class="relative flex items-center border border-stone-200 rounded-2xl px-4 py-4 bg-white focus-within:border-[#4A7043] focus-within:ring-1 focus-within:ring-[#4A7043] transition-all">
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

          <!-- Preset Amounts Horizontal Flex -->
          <div class="flex flex-wrap gap-3">
            <button 
              v-for="amount in presetAmounts" 
              :key="amount"
              @click="setQuickAmount(amount)"
              type="button"
              :class="cn(
                'py-2 px-5 rounded-xl text-xs font-bold transition-all text-center',
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
        <div class="space-y-4">
          <label class="text-sm font-semibold text-stone-500">
            Pilih Rekening Tujuan
          </label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Rekening di Profil -->
            <button 
              @click="setTipeRekening('profil')"
              type="button"
              :class="cn(
                'w-full flex items-start gap-4 p-5 rounded-2xl border transition-all text-left relative overflow-hidden',
                tipeRekening === 'profil'
                  ? 'bg-[#EEF3ED]/40 border-[#4A7043] ring-1 ring-[#4A7043]'
                  : 'bg-white border-stone-200 hover:border-stone-300'
              )"
            >
              <div :class="cn(
                'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-all',
                tipeRekening === 'profil' ? 'bg-[#4A7043]/10 text-[#4A7043]' : 'bg-stone-100 text-stone-400'
              )">
                <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
              </div>
              <div class="flex-1">
                <h4 class="font-bold text-stone-800 text-sm">Rekening di Profil</h4>
                <p class="text-xs text-stone-400 leading-tight">Gunakan rekening tersimpan</p>
                
                <!-- Show profile details if complete -->
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
                'w-full flex items-start gap-4 p-5 rounded-2xl border transition-all text-left relative overflow-hidden',
                tipeRekening === 'lain'
                  ? 'bg-[#EEF3ED]/40 border-[#4A7043] ring-1 ring-[#4A7043]'
                  : 'bg-white border-stone-200 hover:border-stone-300'
              )"
            >
              <div :class="cn(
                'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-all',
                tipeRekening === 'lain' ? 'bg-[#4A7043]/10 text-[#4A7043]' : 'bg-stone-100 text-stone-400'
              )">
                <Icon icon="material-symbols:credit-card-outline" class="w-6 h-6" />
              </div>
              <div class="flex-1">
                <h4 class="font-bold text-stone-800 text-sm">Rekening Lain</h4>
                <p class="text-xs text-stone-400 leading-tight">Input rekening baru</p>
              </div>
            </button>
          </div>

          <!-- Profile Incomplete Alert -->
          <div v-if="tipeRekening === 'profil' && !isProfileComplete" class="bg-[#FFFBEB] border border-amber-200 rounded-2xl p-4 flex flex-col gap-1 text-xs">
            <span class="text-amber-800 font-medium">Anda belum mengisi data rekening di profil.</span>
            <router-link to="/dashboard-nasabah/edit-profile" class="text-amber-800 font-bold underline decoration-1">Lengkapi profil Anda</router-link>
          </div>

          <!-- Rekening Lain Input Fields Container -->
          <div v-if="tipeRekening === 'lain'" class="bg-[#F7F7F5] rounded-2xl p-6 space-y-4 border border-stone-200/50">
            <!-- Nama Bank Input -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-stone-600 flex items-center gap-1.5">
                <Icon icon="material-symbols:domain" class="w-4 h-4 text-stone-400" />
                Nama Bank
              </label>
              <div class="relative">
                <select 
                  v-model="form.nama_bank" 
                  class="w-full bg-white border border-stone-200 rounded-xl py-3 pl-4 pr-10 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043] focus:ring-1 focus:ring-[#4A7043] appearance-none"
                >
                  <option value="" disabled>Pilih nama bank / e-wallet</option>
                  <option v-for="bank in listBank" :key="bank" :value="bank">{{ bank }}</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-400">
                  <Icon icon="material-symbols:keyboard-arrow-down" class="w-5 h-5" />
                </div>
              </div>
            </div>

            <!-- Nomor Rekening Input -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-stone-600 flex items-center gap-1.5">
                <Icon icon="material-symbols:credit-card-outline" class="w-4 h-4 text-stone-400" />
                Nomor Rekening
              </label>
              <input 
                v-model="form.no_rekening" 
                type="text" 
                placeholder="Masukkan nomor rekening" 
                class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043] focus:ring-1 focus:ring-[#4A7043]" 
              />
            </div>

            <!-- Nama Pemilik Rekening Input -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-stone-600 flex items-center gap-1.5">
                <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-stone-400" />
                Nama Pemilik Rekening
              </label>
              <input 
                v-model="form.nama_rek" 
                type="text" 
                placeholder="Sesuai dengan rekening bank" 
                class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043] focus:ring-1 focus:ring-[#4A7043]" 
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
            'w-full py-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.99] shadow-sm',
            isFormValid 
              ? 'bg-[#5D7A56] text-white hover:bg-[#4E6748]' 
              : 'bg-[#D2D6DC] text-white cursor-not-allowed shadow-none'
          )"
        >
          <Icon v-if="isSubmitting" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
          <span>Tarik Saldo</span>
        </button>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-[2px] animate-in fade-in duration-300">
      <div class="bg-white rounded-[2rem] w-full max-w-md p-8 flex flex-col items-center text-center shadow-2xl relative animate-in zoom-in-95 duration-300">
        
        <!-- Close Button (x) -->
        <button 
          @click="showSuccessModal = false"
          class="absolute right-6 top-6 text-stone-400 hover:text-stone-600 p-1.5 bg-stone-100 hover:bg-stone-200/70 rounded-full transition-all"
        >
          <Icon icon="material-symbols:close" class="w-4 h-4" />
        </button>

        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-6 relative">
          <div class="w-16 h-16 bg-green-100/80 rounded-full flex items-center justify-center">
            <Icon icon="material-symbols:check-circle-rounded" class="w-10 h-10 text-green-600" />
          </div>
          <!-- Tiny floating wallet badge -->
          <div class="absolute bottom-0 right-0 w-7 h-7 bg-teal-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
            <Icon icon="material-symbols:account-balance-wallet" class="w-3.5 h-3.5 text-white" />
          </div>
        </div>
        
        <h3 class="text-xl font-bold text-stone-800 mb-2">Request Penarikan Berhasil! 💰</h3>
        <p class="text-xs text-stone-500 mb-6 px-4 leading-relaxed">
          Permintaan Anda akan segera ditangani oleh petugas kami. Untuk memantau status penarikan Anda, silakan klik tombol di bawah ini.
        </p>
        
        <div class="w-full bg-[#F9F9F7] rounded-2xl p-4 border border-stone-200/50 mb-6">
           <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-1">Tracking Penarikan</p>
           <p class="text-lg font-black text-[#5D7A56] tracking-wider">
             #WD-{{ newPenarikanId || '252238780' }}
           </p>
           <p class="text-[10px] text-stone-400 mt-1">Simpan ID ini untuk melacak status request Anda</p>
        </div>
        
        <div class="flex flex-col w-full gap-2.5">
          <button 
            @click="showSuccessModal = false; router.push('/dashboard-nasabah/penarikan-saya')" 
            class="w-full py-3.5 rounded-xl bg-[#5D7A56] text-white font-bold text-xs shadow-md hover:bg-[#4E6748] flex items-center justify-center gap-2 transition-all"
          >
            <Icon icon="material-symbols:description-outline" class="w-4 h-4" />
            <span>Lihat Tracking</span>
          </button>
          <button 
            @click="showSuccessModal = false; router.push('/dashboard-nasabah')" 
            class="w-full py-3.5 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50 transition-all"
          >
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
import { checkRole } from '@/utils';
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";

const axios = inject('axios');
const router = useRouter();

checkRole('nasabah');
const listBank = [
  'BRI',
  'BCA',
  'DANA',
  'Bank Jago',
  'Bank Mandiri',
  'BNI',
  'OVO',
  'GoPay',
  'LinkAja',
  'ShopeePay',
  'CIMB Niaga',
  'Bank Permata',
  'BSI'
];

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