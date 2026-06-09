<template>
  <DashboardLayout title="Edit Petugas">
    <div class="max-w-md mx-auto mt-6">
      
      <!-- Card Container -->
      <div class="bg-white rounded-[2rem] p-6 border border-stone-100 shadow-sm space-y-6">
        
        <div class="flex items-center gap-3 pb-4 border-b border-stone-50">
          <div class="w-10 h-10 rounded-xl bg-[#4A7043]/10 text-[#4A7043] flex items-center justify-center">
            <Icon icon="material-symbols:edit-square-outline" class="w-5.5 h-5.5" />
          </div>
          <div>
            <h3 class="text-sm font-extrabold text-stone-800">Edit Akun Petugas</h3>
            <p class="text-[10px] text-stone-400 font-medium">Ubah informasi akun dan penugasan gudang</p>
          </div>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-10 gap-2">
          <Icon icon="line-md:loading-twotone-loop" class="w-8 h-8 text-[#4A7043]" />
          <p class="text-xs text-stone-400 font-bold">Memuat data petugas...</p>
        </div>
        
        <div v-else-if="error" class="bg-red-50 text-red-500 rounded-2xl p-4 text-xs font-bold text-center border border-red-100">
          {{ error }}
        </div>

        <form v-else @submit.prevent="submitForm" class="space-y-4">
          <!-- Nama -->
          <div class="space-y-1.5">
            <label for="nama" class="text-xs font-bold text-stone-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
            <input 
              id="nama" 
              v-model="form.nama" 
              placeholder="Masukkan nama lengkap" 
              class="w-full bg-[#F5F5F0] border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all text-sm font-semibold text-stone-700"
            />
            <div v-if="errors.nama" class="text-red-500 text-[10px] font-bold ml-1">{{ errors.nama[0] }}</div>
          </div>

          <!-- Username -->
          <div class="space-y-1.5">
            <label for="username" class="text-xs font-bold text-stone-500 uppercase tracking-wider ml-1">Username</label>
            <input 
              id="username" 
              v-model="form.username" 
              placeholder="Masukkan username" 
              class="w-full bg-[#F5F5F0] border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all text-sm font-semibold text-stone-700"
            />
            <div v-if="errors.username" class="text-red-500 text-[10px] font-bold ml-1">{{ errors.username[0] }}</div>
          </div>

          <!-- No Telp -->
          <div class="space-y-1.5">
            <label for="no_telp" class="text-xs font-bold text-stone-500 uppercase tracking-wider ml-1">No. Telepon</label>
            <input 
              id="no_telp" 
              v-model="form.no_telp" 
              placeholder="Masukkan nomor telepon" 
              class="w-full bg-[#F5F5F0] border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all text-sm font-semibold text-stone-700"
            />
            <div v-if="errors.no_telp" class="text-red-500 text-[10px] font-bold ml-1">{{ errors.no_telp[0] }}</div>
          </div>

          <!-- Gudang Penugasan -->
          <div class="space-y-1.5">
            <label for="gudang_id" class="text-xs font-bold text-stone-500 uppercase tracking-wider ml-1">Gudang Penugasan</label>
            <div class="relative">
              <select 
                id="gudang_id"
                v-model="form.gudang_id"
                class="w-full bg-[#F5F5F0] border-none rounded-2xl p-4 pr-12 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all text-sm font-bold text-stone-700 appearance-none"
              >
                <option value="" disabled>Pilih Gudang Penugasan</option>
                <option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id">
                  Gudang {{ gudang.alamat.split(',')[0] }} (GDG-{{ String(gudang.gudang_id).padStart(3, '0') }})
                </option>
              </select>
              <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
            </div>
            <div v-if="errors.gudang_id" class="text-red-500 text-[10px] font-bold ml-1">{{ errors.gudang_id[0] }}</div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-stone-50">
            <button 
              type="button" 
              @click="goBack" 
              class="flex-1 py-4 rounded-xl bg-stone-100 text-stone-500 font-bold text-xs hover:bg-stone-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="flex-1 py-4 rounded-xl bg-[#4A7043] text-white font-black text-xs hover:bg-[#3d5c37] active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter, useRoute } from "vue-router";
import { checkRole } from "@/utils";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";

checkRole("admin");

const router = useRouter();
const route = useRoute();

const form = ref({
  nama: "",
  username: "",
  no_telp: "",
  gudang_id: "",
});

const error = ref(null);
const errors = ref({});
const loading = ref(true);
const gudangList = ref([]);
const token = sessionStorage.getItem("token");
const petugasId = route.params.id;

if (!token) {
  router.push("/login");
  throw new Error("Otentikasi diperlukan.");
}

const headers = { Authorization: `Bearer ${token}` };

// Ambil list gudang
const fetchGudang = async () => {
  try {
    const res = await axios.get("/api/admin/gudang", { headers });
    gudangList.value = res.data.data;
  } catch (err) {
    console.error("Gagal mengambil data gudang:", err);
  }
};

// Ambil data awal petugas
const fetchPetugas = async () => {
  try {
    const res = await axios.get(`/api/admin/petugas/${petugasId}`, { headers });
    const petugasData = res.data.data;
    form.value.nama = petugasData.nama;
    form.value.username = petugasData.username;
    form.value.no_telp = petugasData.no_telp;
    form.value.gudang_id = petugasData.gudang_id || "";
  } catch (err) {
    error.value = "Gagal mengambil data petugas.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchGudang();
  await fetchPetugas();
});

// Submit update
const submitForm = async () => {
  errors.value = {};
  error.value = null;

  try {
    await axios.put(`/api/admin/edit-petugas/${petugasId}`, form.value, { headers });
    alert("Data petugas berhasil diperbarui!");
    router.push("/dashboard-admin/kelola-users");
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      error.value = "Terjadi kesalahan saat memperbarui data.";
      console.error(err);
    }
  }
};

const goBack = () => {
  router.push("/dashboard-admin/kelola-users");
};
</script>
