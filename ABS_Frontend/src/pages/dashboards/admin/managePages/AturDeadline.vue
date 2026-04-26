<template>
  <DashboardLayout title="Atur Deadline Pembayaran">
    <div class="space-y-8 max-w-4xl mx-auto pb-10">
      <!-- Card: Atur Deadline -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-10 h-10 rounded-xl bg-[#4A7043]/10 flex items-center justify-center">
            <Icon icon="material-symbols:schedule-outline" class="w-6 h-6 text-[#4A7043]" />
          </div>
          <h2 class="text-xl font-bold text-gray-800">Atur Deadline</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
          <!-- Input: Jumlah Hari -->
          <div class="space-y-3">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Jumlah Hari</label>
            <div class="relative group">
              <input 
                v-model.number="days"
                type="number" 
                min="0"
                max="30"
                placeholder="0"
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-bold text-lg focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all"
              />
              <div class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 font-bold">Hari</div>
            </div>
            <p class="text-[10px] text-gray-400 font-medium px-1">Maksimal 30 hari</p>
          </div>

          <!-- Input: Jumlah Jam -->
          <div class="space-y-3">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Jumlah Jam</label>
            <div class="relative group">
              <input 
                v-model.number="hours"
                type="number" 
                min="0"
                max="23"
                placeholder="24"
                class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-gray-700 font-bold text-lg focus:outline-none focus:ring-4 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all"
              />
              <div class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 font-bold">Jam</div>
            </div>
            <p class="text-[10px] text-gray-400 font-medium px-1">0-23 jam</p>
          </div>
        </div>

        <!-- Total Deadline Display -->
        <div class="bg-gradient-to-r from-[#4A7043] to-[#6A9A61] rounded-[2rem] p-8 text-white shadow-lg shadow-green-900/10 mb-8 relative overflow-hidden group">
          <!-- Decorative Icon -->
          <Icon icon="material-symbols:info-outline" class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5 rotate-12 transition-transform group-hover:scale-110" />
          
          <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2 text-white/80">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5" />
              <span class="text-sm font-bold uppercase tracking-wider">Total Deadline</span>
            </div>
            <div class="text-4xl md:text-5xl font-black mb-1">{{ totalHours }} Jam</div>
            <div class="text-lg font-medium text-white/70">= {{ days }} Hari {{ hours }} Jam</div>
          </div>
        </div>

        <!-- Save Button -->
        <button 
          @click="saveDeadline"
          :disabled="loading"
          class="w-full bg-[#4A7043] text-white font-bold py-5 rounded-3xl flex items-center justify-center gap-3 transition-all duration-300 shadow-xl shadow-green-900/20 hover:bg-[#3D5C37] active:scale-[0.98] disabled:opacity-50 group"
        >
          <Icon :icon="loading ? 'line-md:loading-twotone-loop' : 'material-symbols:save-outline'" class="w-6 h-6 transition-transform group-hover:scale-110" />
          <span class="tracking-wide text-lg">{{ loading ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
        </button>
      </div>

      <!-- Card: Contoh Countdown -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-10 h-10 rounded-xl bg-[#A86444]/10 flex items-center justify-center">
            <Icon icon="material-symbols:timer-outline" class="w-6 h-6 text-[#A86444]" />
          </div>
          <h2 class="text-xl font-bold text-gray-800">Contoh Countdown</h2>
        </div>

        <div class="bg-[#FDFBF7] rounded-[2rem] p-10 border border-[#F5E6D3] flex flex-col items-center">
          <p class="text-xs font-bold text-[#A86444]/60 uppercase tracking-[0.2em] mb-8">Sisa Waktu Pembayaran</p>
          
          <div class="flex gap-4 md:gap-6">
            <!-- Hour Box -->
            <div class="flex flex-col items-center gap-3">
              <div class="w-20 h-24 md:w-28 md:h-32 bg-[#A86444] rounded-2xl flex items-center justify-center shadow-lg shadow-orange-900/10 border-b-4 border-black/10">
                <span class="text-3xl md:text-4xl font-black text-white leading-none">{{ previewHours }}</span>
              </div>
              <span class="text-[10px] font-bold text-[#A86444] uppercase tracking-widest">Jam</span>
            </div>

            <!-- Minute Box -->
            <div class="flex flex-col items-center gap-3">
              <div class="w-20 h-24 md:w-28 md:h-32 bg-[#A86444] rounded-2xl flex items-center justify-center shadow-lg shadow-orange-900/10 border-b-4 border-black/10">
                <span class="text-3xl md:text-4xl font-black text-white leading-none">{{ previewMinutes }}</span>
              </div>
              <span class="text-[10px] font-bold text-[#A86444] uppercase tracking-widest">Menit</span>
            </div>

            <!-- Second Box -->
            <div class="flex flex-col items-center gap-3">
              <div class="w-20 h-24 md:w-28 md:h-32 bg-[#A86444] rounded-2xl flex items-center justify-center shadow-lg shadow-orange-900/10 border-b-4 border-black/10">
                <span class="text-3xl md:text-4xl font-black text-white leading-none">{{ previewSeconds }}</span>
              </div>
              <span class="text-[10px] font-bold text-[#A86444] uppercase tracking-widest">Detik</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, inject } from "vue";
import { useRouter } from 'vue-router';
import { checkRole } from '@/utils';
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";

checkRole('admin');

const router = useRouter();
const axios = inject('axios');
const token = sessionStorage.getItem('token');

if (!token) {
  router.push('/login');
}

const days = ref(0);
const hours = ref(24);
const loading = ref(false);

// Countdown Preview Logic
const previewHours = ref(23);
const previewMinutes = ref(59);
const previewSeconds = ref(58);
let timer = null;

const startPreviewTimer = () => {
  timer = setInterval(() => {
    if (previewSeconds.value > 0) {
      previewSeconds.value--;
    } else {
      previewSeconds.value = 59;
      if (previewMinutes.value > 0) {
        previewMinutes.value--;
      } else {
        previewMinutes.value = 59;
        if (previewHours.value > 0) {
          previewHours.value--;
        } else {
          previewHours.value = 23;
        }
      }
    }
  }, 1000);
};

const totalHours = computed(() => {
  return (parseInt(days.value || 0) * 24) + parseInt(hours.value || 0);
});

const fetchConfig = async () => {
  try {
    loading.value = true;
    const res = await axios.get("/api/admin/web-config", {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    const deadline = res.data.lama_deadline || 24;
    days.value = Math.floor(deadline / 24);
    hours.value = deadline % 24;
  } catch (err) {
    console.error("Failed to fetch config:", err);
  } finally {
    loading.value = false;
  }
};

const saveDeadline = async () => {
  try {
    loading.value = true;
    const formData = new FormData();
    formData.append("lama_deadline", totalHours.value);
    
    await axios.post("/api/admin/web-config?_method=PUT", formData, {
      headers: { 
        Authorization: `Bearer ${token}`,
        "Content-Type": "multipart/form-data"
      }
    });
    
    alert("Deadline pembayaran berhasil diperbarui!");
  } catch (err) {
    console.error("Failed to save deadline:", err);
    alert("Gagal memperbarui deadline pembayaran.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchConfig();
  startPreviewTimer();
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
