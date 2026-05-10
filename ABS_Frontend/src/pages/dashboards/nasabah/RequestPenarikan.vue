<template>
  <DashboardLayout title="Penarikan Uang">
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
      
      <!-- Saldo Tersedia Card -->
      <div class="relative overflow-hidden bg-gradient-to-br from-[#4A7043] to-[#3D5C37] rounded-[2.5rem] p-8 text-white shadow-2xl shadow-[#4A7043]/20">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-black/5 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl"></div>
        
        <div class="relative flex justify-between items-center">
          <div class="space-y-2">
            <p class="text-white/70 text-sm font-bold uppercase tracking-widest">Saldo Tersedia</p>
            <h2 class="text-4xl md:text-5xl font-black tracking-tight">
              {{ formatRupiah(saldoTersedia) }}
            </h2>
          </div>
          <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 shadow-inner">
            <Icon icon="material-symbols:account-balance-wallet-outline" class="w-8 h-8 text-white" />
          </div>
        </div>
      </div>

      <!-- Main Container -->
      <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-stone-100 space-y-10">
        
        <!-- Input Jumlah Section -->
        <div class="space-y-6">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-[0.2em]">
            <Icon icon="material-symbols:payments-outline" class="w-4 h-4" />
            Jumlah Penarikan
          </label>
          
          <div class="relative group">
            <div class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-black text-stone-300 group-focus-within:text-[#4A7043] transition-colors">Rp</div>
            <input 
              v-model="displayJumlah"
              @input="handleJumlahInput"
              type="text" 
              placeholder="0"
              class="w-full bg-[#F9F9F7] border-2 border-transparent focus:border-[#4A7043] rounded-3xl py-6 pl-16 pr-8 text-3xl font-black text-stone-800 transition-all outline-none"
            />
          </div>

          <!-- Quick Amounts -->
          <div class="flex flex-wrap gap-3">
            <button 
              v-for="amount in presetAmounts" 
              :key="amount"
              @click="setQuickAmount(amount)"
              :class="cn(
                'px-6 py-3 rounded-2xl text-sm font-black transition-all border-2',
                form.jumlah === amount 
                  ? 'bg-[#4A7043] border-[#4A7043] text-white shadow-lg shadow-[#4A7043]/20 scale-105' 
                  : 'bg-stone-50 border-stone-100 text-stone-500 hover:border-[#4A7043]/30 hover:bg-white'
              )"
            >
              {{ formatRupiahShort(amount) }}
            </button>
          </div>
          <p class="text-[10px] font-bold text-stone-400 italic px-2">Minimal penarikan: Rp 50.000</p>
        </div>

        <hr class="border-stone-100" />

        <!-- Pilih Rekening Section -->
        <div class="space-y-6">
          <label class="flex items-center gap-2 text-xs font-black text-stone-400 uppercase tracking-[0.2em]">
            <Icon icon="material-symbols:account-balance-outline" class="w-4 h-4" />
            Pilih Rekening Tujuan
          </label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Rekening Profil Card -->
            <button 
              @click="setTipeRekening('profil')"
              :class="cn(
                'flex items-start gap-4 p-6 rounded-3xl border-2 transition-all text-left group relative overflow-hidden',
                tipeRekening === 'profil'
                  ? 'bg-[#E8F0E6] border-[#4A7043] shadow-md'
                  : 'bg-white border-stone-100 hover:border-stone-200'
              )"
            >
              <div :class="cn(
                'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 transition-all',
                tipeRekening === 'profil' ? 'bg-[#4A7043] text-white' : 'bg-stone-100 text-stone-400 group-hover:bg-stone-200'
              )">
                <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
              </div>
              <div class="flex-1">
                <h4 class="font-black text-stone-800 text-sm">Rekening di Profil</h4>
                <p class="text-[10px] font-bold text-stone-400 mt-0.5 leading-tight">Gunakan data rekening yang tersimpan di profil Anda</p>
                
                <div v-if="tipeRekening === 'profil' && isProfileComplete" class="mt-4 p-3 bg-white/50 rounded-xl border border-[#4A7043]/10 space-y-1">
                   <p class="text-[10px] font-black text-[#4A7043] uppercase tracking-wider">{{ rekeningProfil.nama_bank }}</p>
                   <p class="text-xs font-black text-stone-700">{{ rekeningProfil.no_rekening }}</p>
                   <p class="text-[10px] font-bold text-stone-500">{{ rekeningProfil.nama_rek }}</p>
                </div>
              </div>
              
              <!-- Completion Check Overlay for Profile -->
              <div v-if="tipeRekening === 'profil' && !isProfileComplete" class="absolute inset-0 bg-white/40 backdrop-blur-[1px] flex flex-col items-center justify-center p-4 text-center">
                 <Icon icon="material-symbols:lock-outline" class="w-6 h-6 text-amber-600 mb-2" />
              </div>
            </button>

            <!-- Rekening Lain Card -->
            <button 
              @click="setTipeRekening('lain')"
              :class="cn(
                'flex items-start gap-4 p-6 rounded-3xl border-2 transition-all text-left group',
                tipeRekening === 'lain'
                  ? 'bg-[#E8F0E6] border-[#4A7043] shadow-md'
                  : 'bg-white border-stone-100 hover:border-stone-200'
              )"
            >
              <div :class="cn(
                'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 transition-all',
                tipeRekening === 'lain' ? 'bg-[#4A7043] text-white' : 'bg-stone-100 text-stone-400 group-hover:bg-stone-200'
              )">
                <Icon icon="material-symbols:account-balance-wallet" class="w-6 h-6" />
              </div>
              <div class="flex-1">
                <h4 class="font-black text-stone-800 text-sm">Rekening Lain</h4>
                <p class="text-[10px] font-bold text-stone-400 mt-0.5 leading-tight">Gunakan rekening lain untuk tujuan penarikan ini</p>
              </div>
            </button>
          </div>

          <!-- Profile Warning Alert -->
          <div v-if="tipeRekening === 'profil' && !isProfileComplete" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4 animate-in slide-in-from-top-2 duration-300">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-amber-600" />
            </div>
            <div class="flex-1 space-y-1">
              <p class="text-sm font-black text-amber-900 leading-tight">Profil Belum Lengkap ({{ completionPercentage }}%)</p>
              <p class="text-xs font-bold text-amber-700 leading-relaxed">
                Anda harus melengkapi profil 100% untuk menggunakan rekening tersimpan. 
                <router-link to="/dashboard-nasabah/edit-profile" class="underline decoration-2 underline-offset-2 hover:text-amber-900">Lengkapi profil Anda sekarang</router-link>
              </p>
            </div>
          </div>

          <!-- Rekening Lain Form -->
          <div v-if="tipeRekening === 'lain'" class="bg-[#FDF8F6] border border-[#A86444]/10 rounded-[2rem] p-8 space-y-6 animate-in zoom-in-95 duration-500">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest px-1">Nama Bank</label>
                  <input v-model="form.nama_bank" type="text" placeholder="Contoh: Bank BRI" class="w-full bg-white border border-stone-100 rounded-2xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" />
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest px-1">Nomor Rekening</label>
                  <input v-model="form.no_rekening" type="text" placeholder="Masukkan nomor rekening" class="w-full bg-white border border-stone-100 rounded-2xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" />
                </div>
                <div class="md:col-span-2 space-y-2">
                  <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest px-1">Nama Pemilik Rekening</label>
                  <input v-model="form.nama_rek" type="text" placeholder="Sesuai dengan di buku tabungan" class="w-full bg-white border border-stone-100 rounded-2xl py-3.5 px-5 text-sm font-bold text-stone-700 focus:outline-none focus:border-[#4A7043] transition-all" />
                </div>
             </div>
          </div>
        </div>

        <!-- Action Button -->
        <button 
          @click="submitRequest"
          :disabled="isSubmitting || !isFormValid"
          :class="cn(
            'w-full py-5 rounded-[2rem] font-black text-lg flex items-center justify-center gap-3 transition-all active:scale-[0.98]',
            isFormValid 
              ? 'bg-[#4A7043] text-white shadow-xl shadow-[#4A7043]/20 hover:bg-[#3D5C37]' 
              : 'bg-stone-300 text-stone-500 cursor-not-allowed shadow-none'
          )"
        >
          <Icon v-if="isSubmitting" icon="line-md:loading-twotone-loop" class="w-6 h-6" />
          <Icon v-else icon="material-symbols:send-outline" class="w-6 h-6" />
          {{ isSubmitting ? 'Mengirim Request...' : 'Kirim Request Penarikan' }}
        </button>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[3rem] w-full max-w-md p-10 flex flex-col items-center text-center shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-8">
          <Icon icon="material-symbols:check-circle-outline" class="w-16 h-16 text-green-600" />
        </div>
        
        <h3 class="text-2xl font-black text-stone-800 mb-2">Request Berhasil! 💰</h3>
        <p class="text-sm font-medium text-stone-500 mb-8 px-4 leading-relaxed">
          Permintaan penarikan Anda telah dikirim ke petugas. Kami akan segera memprosesnya.
        </p>
        
        <div class="w-full bg-[#F9F9F7] rounded-3xl p-6 border border-stone-100 mb-8">
           <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Tracking ID</p>
           <p class="text-xl font-black text-[#4A7043] tracking-wider">#WD-{{ Math.floor(Math.random() * 100000000) }}</p>
        </div>
        
        <div class="flex flex-col w-full gap-3">
          <button @click="showSuccessModal = false; router.push('/dashboard-nasabah/riwayatpenjemputan')" class="w-full py-4 rounded-2xl bg-[#4A7043] text-white font-black text-sm shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37] transition-all">
            Lihat Tracking
          </button>
          <button @click="showSuccessModal = false; router.push('/dashboard-nasabah')" class="w-full py-4 rounded-2xl border border-stone-200 text-stone-500 font-black text-sm hover:bg-stone-50 transition-all">
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
    await axios.post('/api/nasabah/request-penarikan', form.value);
    showSuccessModal.value = true;
    // Refresh data
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
