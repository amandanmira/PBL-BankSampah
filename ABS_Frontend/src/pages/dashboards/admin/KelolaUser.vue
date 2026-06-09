<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { cn } from "@/lib/utils";

// Security check
checkRole("admin");

const axios = inject('axios');
const router = useRouter();

// State
const allAccounts = ref([]);
const gudangList = ref([]);
const isLoading = ref(true);
const error = ref(null);
const searchQuery = ref("");
const selectedRole = ref("Semua Akun");
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Modal State
const isModalOpen = ref(false);
const isManagerModalOpen = ref(false);
const isSubmitting = ref(false);
const form = ref({
  nama: "",
  username: "",
  email: "",
  password: "",
  password_confirmation: "",
  no_telp: "",
  gudang_id: "",
});

const roles = [
  { name: "Semua Akun", endpoint: null },
  { name: "Petugas", endpoint: "petugas", idField: "petugas_id" },
  { name: "Manager", endpoint: "manager", idField: "manager_id" },
  { name: "Nasabah", endpoint: "nasabah", idField: "nasabah_id" },
  { name: "Pengepul", endpoint: "pengepul", idField: "pengepul_id" },
];

// Fetch Data
const fetchData = async () => {
  try {
    isLoading.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    // Fetch all roles except "Semua Akun"
    const roleEndpoints = [
      { name: "Petugas", endpoint: "petugas", idField: "petugas_id" },
      { name: "Manager", endpoint: "manager", idField: "manager_id" },
      { name: "Nasabah", endpoint: "nasabah", idField: "nasabah_id" },
      { name: "Pengepul", endpoint: "pengepul", idField: "pengepul_id" },
      { name: "Admin", endpoint: "admin", idField: "admin_id" },
    ];

    const responses = await Promise.all(
      roleEndpoints.map((r) => axios.get(`/api/admin/${r.endpoint}`, { headers }))
    );

    allAccounts.value = responses.flatMap((res, index) => {
      const r = roleEndpoints[index];
      return res.data.data.map((item) => ({
        ...item,
        id: item[r.idField],
        role: r.name,
        // Normalize status and date
        status: r.name === 'Petugas' 
          ? (item.active ? 'aktif' : 'non-aktif') 
          : (r.name === 'Admin' ? 'aktif' : (item.status === 'aktif' ? 'aktif' : 'non-aktif')),
        joined_at: item.created_at || new Date().toISOString(),
      }));
    });

    // Fetch Gudang for modal
    const gudangRes = await axios.get('/api/admin/gudang', { headers });
    gudangList.value = gudangRes.data.data;

  } catch (err) {
    error.value = "Gagal memuat data: " + (err.response?.data?.message || err.message);
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchData);

// Filtering and Pagination
const filteredAccounts = computed(() => {
  let result = allAccounts.value;

  // Filter by Role
  if (selectedRole.value !== "Semua Akun") {
    result = result.filter((a) => a.role === selectedRole.value);
  }

  // Filter by Search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(
      (a) =>
        a.nama?.toLowerCase().includes(q) ||
        a.username?.toLowerCase().includes(q) ||
        a.email?.toLowerCase().includes(q)
    );
  }

  return result;
});

const totalPages = computed(() => Math.ceil(filteredAccounts.value.length / itemsPerPage.value));

const paginatedAccounts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredAccounts.value.slice(start, end);
});

// Actions
const toggleStatus = async (account) => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const isPetugas = account.role === "Petugas";
    const currentStatus = isPetugas ? account.active : account.status === "aktif";
    const action = currentStatus ? "deactivate" : "activate";
    
    let endpoint = "";
    if (account.role === "Petugas") endpoint = `/api/admin/petugas/${account.id}/${action}`;
    else if (account.role === "Manager") endpoint = `/api/admin/manager/${account.id}/${action}`;
    else if (account.role === "Nasabah") endpoint = `/api/admin/nasabah/${account.id}/${action}`;
    else if (account.role === "Pengepul") endpoint = `/api/admin/pengepul/${account.id}/${action}`;

    if (!endpoint) return;

    await axios.put(endpoint, {}, { headers });
    
    // Update local state
    const index = allAccounts.value.findIndex(a => a.id === account.id && a.role === account.role);
    if (index !== -1) {
      if (isPetugas) {
        allAccounts.value[index].active = !allAccounts.value[index].active;
        allAccounts.value[index].status = allAccounts.value[index].active ? 'aktif' : 'non-aktif';
      } else {
        // Toggle between 'aktif' and 'non-aktif' (normalizing from DB 'nonaktif' if needed)
        const current = allAccounts.value[index].status === 'aktif';
        allAccounts.value[index].status = current ? 'non-aktif' : 'aktif';
      }
    }
  } catch (err) {
    alert("Gagal mengubah status: " + (err.response?.data?.message || err.message));
  }
};

const handleAddPetugas = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    alert("Konfirmasi password tidak cocok!");
    return;
  }
  try {
    isSubmitting.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    await axios.post("/api/admin/buatPetugas", form.value, { headers });
    
    alert("Akun petugas berhasil dibuat!");
    isModalOpen.value = false;
    // Reset form
    form.value = { nama: "", username: "", email: "", password: "", password_confirmation: "", no_telp: "", gudang_id: "" };
    fetchData(); // Refresh list
  } catch (err) {
    alert("Gagal membuat petugas: " + (err.response?.data?.message || err.message));
  } finally {
    isSubmitting.value = false;
  }
};

const handleAddManager = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    alert("Konfirmasi password tidak cocok!");
    return;
  }
  try {
    isSubmitting.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    await axios.post("/api/admin/buatManager", {
      nama: form.value.nama,
      username: form.value.username,
      email: form.value.email,
      password: form.value.password
    }, { headers });
    
    alert("Akun manager berhasil dibuat!");
    isManagerModalOpen.value = false;
    // Reset form
    form.value = { nama: "", username: "", email: "", password: "", password_confirmation: "", no_telp: "", gudang_id: "" };
    fetchData(); // Refresh list
  } catch (err) {
    alert("Gagal membuat manager: " + (err.response?.data?.message || err.message));
  } finally {
    isSubmitting.value = false;
  }
};

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(date);
};
</script>

<template>
  <DashboardLayout title="Kelola Akun & Data">
    <div class="space-y-6 animate-in fade-in duration-500">
      <!-- Role Filters & Add Button -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap gap-4">
          <button
            v-for="role in roles"
            :key="role.name"
            @click="selectedRole = role.name; currentPage = 1"
            :class="
              cn(
                'px-6 py-2.5 rounded-xl font-medium transition-all duration-200 shadow-sm',
                selectedRole === role.name
                  ? 'bg-[#4A7043] text-white shadow-md transform scale-105'
                  : 'bg-white text-gray-600 hover:bg-gray-50'
              )
            "
          >
            {{ role.name }}
          </button>
        </div>

        <button
          v-if="selectedRole === 'Petugas'"
          @click="isModalOpen = true"
          class="flex items-center gap-2 bg-[#4A7043] text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-[#3d5d37] transition-colors shadow-md shrink-0 self-end md:self-auto"
        >
          <Icon icon="material-symbols:add" class="w-5 h-5" />
          Tambah Petugas
        </button>

        <button
          v-if="selectedRole === 'Manager'"
          @click="isManagerModalOpen = true"
          class="flex items-center gap-2 bg-[#4A7043] text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-[#3d5d37] transition-colors shadow-md shrink-0 self-end md:self-auto"
        >
          <Icon icon="material-symbols:add" class="w-5 h-5" />
          Tambah Manager
        </button>
      </div>

      <!-- Search Bar -->
      <div class="relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <Icon icon="material-symbols:search" class="w-5 h-5 text-gray-400 group-focus-within:text-[#4A7043] transition-colors" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama atau email..."
          class="w-full bg-white border-none rounded-2xl py-4 pl-12 pr-4 shadow-sm focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-400"
        />
      </div>

      <!-- Table Container -->
      <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#4A7043] text-white">
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider">Identitas</th>
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider">Role</th>
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider">Kontak</th>
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider">Status</th>
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider">Bergabung</th>
                <th class="px-6 py-4 font-bold uppercase text-xs tracking-wider text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="isLoading">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                  Memuat data akun...
                </td>
              </tr>
              <tr v-else-if="paginatedAccounts.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                  Tidak ada data akun yang ditemukan.
                </td>
              </tr>
              <tr 
                v-for="account in paginatedAccounts" 
                :key="account.id + account.role"
                class="hover:bg-gray-50/50 transition-colors group"
              >
                <!-- Identitas -->
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="font-bold text-gray-800">{{ account.nama }}</span>
                    <span class="text-xs text-[#4A7043] font-medium">@{{ account.username }}</span>
                  </div>
                </td>
                
                <!-- Role -->
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600">{{ account.role }}</span>
                </td>
                
                <!-- Kontak -->
                <td class="px-6 py-4">
                  <div class="flex flex-col text-sm">
                    <span class="text-gray-700">{{ account.email }}</span>
                    <span class="text-gray-500">{{ account.no_telp || '-' }}</span>
                  </div>
                </td>
                
                <!-- Status -->
                <td class="px-6 py-4">
                  <span 
                    :class="cn(
                      'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm',
                      account.status === 'aktif' ? 'bg-[#4A7043] text-white' : 'bg-[#C23B22] text-white'
                    )"
                  >
                    {{ account.status }}
                  </span>
                </td>
                
                <!-- Bergabung -->
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600">{{ formatDate(account.joined_at) }}</span>
                </td>
                
                <!-- Aksi -->
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-3">
                    <button 
                      v-if="account.role === 'Petugas'"
                      @click="router.push(`/dashboard-admin/edit-petugas/${account.id}`)"
                      class="p-2 text-gray-400 hover:text-[#4A7043] hover:bg-[#4A7043]/10 rounded-lg transition-all"
                      title="Edit Petugas"
                    >
                      <Icon icon="material-symbols:edit-outline" class="w-5 h-5" />
                    </button>
                    <button 
                      @click="toggleStatus(account)"
                      :class="cn(
                        'p-2 rounded-lg transition-all',
                        account.status === 'aktif' ? 'text-gray-400 hover:text-[#C23B22] hover:bg-[#C23B22]/10' : 'text-[#4A7043] hover:bg-[#4A7043]/10'
                      )"
                      :title="account.status === 'aktif' ? 'Ban/Nonaktifkan' : 'Aktifkan'"
                    >
                      <Icon icon="material-symbols:power-settings-new" class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination & View Settings -->
      <div class="bg-white rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-500 font-medium">Tampilkan</span>
          <div class="relative">
            <select 
              v-model="itemsPerPage"
              class="appearance-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 pr-10 text-sm font-semibold outline-none focus:ring-2 focus:ring-[#4A7043] transition-all"
            >
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="15">15</option>
              <option :value="20">20</option>
              <option :value="25">25</option>
            </select>
            <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600"
          >
            Sebelumnya
          </button>
          
          <div class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm">
            {{ currentPage }} / {{ totalPages || 1 }}
          </div>
          
          <button 
            @click="currentPage++"
            :disabled="currentPage === totalPages || totalPages === 0"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Add Petugas Modal -->
    <div 
      v-if="isModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
    >
      <div class="bg-white w-full max-w-lg rounded-[32px] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-8 space-y-6">
          <h2 class="text-2xl font-bold text-gray-800">Tambah Petugas Baru</h2>
          
          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
              <input 
                v-model="form.nama"
                type="text" 
                placeholder="Masukkan nama lengkap"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Username</label>
              <input 
                v-model="form.username"
                type="text" 
                placeholder="Username untuk login"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Password</label>
              <input 
                v-model="form.password"
                type="password" 
                placeholder="Masukkan password"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Konfirmasi Password</label>
              <input 
                v-model="form.password_confirmation"
                type="password" 
                placeholder="Konfirmasi password"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Email</label>
              <input 
                v-model="form.email"
                type="email" 
                placeholder="email@example.com"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Telepon</label>
              <input 
                v-model="form.no_telp"
                type="text" 
                placeholder="08123456789"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Gudang Penugasan</label>
              <div class="relative">
                <select 
                  v-model="form.gudang_id"
                  class="w-full appearance-none bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all text-sm"
                >
                  <option value="" disabled selected>Pilih gudang penugasan</option>
                  <option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id">
                    {{ gudang.alamat }}
                  </option>
                </select>
                <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
              </div>
            </div>
          </div>

          <div class="flex gap-4 pt-4">
            <button 
              @click="isModalOpen = false"
              class="flex-1 bg-gray-500 text-white py-4 rounded-2xl font-bold hover:bg-gray-600 transition-colors shadow-sm"
            >
              Batal
            </button>
            <button 
              @click="handleAddPetugas"
              :disabled="isSubmitting"
              class="flex-1 bg-[#4A7043] text-white py-4 rounded-2xl font-bold hover:bg-[#3d5d37] transition-all shadow-md disabled:opacity-50"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Manager Modal -->
    <div 
      v-if="isManagerModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
    >
      <div class="bg-white w-full max-w-lg rounded-[32px] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-8 space-y-6">
          <h2 class="text-2xl font-bold text-gray-800">Tambah Manager Baru</h2>
          
          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Nama Lengkap</label>
              <input 
                v-model="form.nama"
                type="text" 
                placeholder="Masukkan nama lengkap"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Username</label>
              <input 
                v-model="form.username"
                type="text" 
                placeholder="Username untuk login"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Password</label>
              <input 
                v-model="form.password"
                type="password" 
                placeholder="Masukkan password"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Konfirmasi Password</label>
              <input 
                v-model="form.password_confirmation"
                type="password" 
                placeholder="Konfirmasi password"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
            
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Email</label>
              <input 
                v-model="form.email"
                type="email" 
                placeholder="email@example.com"
                class="w-full bg-gray-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-300 text-sm"
              />
            </div>
          </div>

          <div class="flex gap-4 pt-4">
            <button 
              @click="isManagerModalOpen = false"
              class="flex-1 bg-gray-500 text-white py-4 rounded-2xl font-bold hover:bg-gray-600 transition-colors shadow-sm"
            >
              Batal
            </button>
            <button 
              @click="handleAddManager"
              :disabled="isSubmitting"
              class="flex-1 bg-[#4A7043] text-white py-4 rounded-2xl font-bold hover:bg-[#3d5d37] transition-all shadow-md disabled:opacity-50"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}
.fade-in {
  animation-name: fadeIn;
}
.zoom-in-95 {
  animation-name: zoomIn95;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes zoomIn95 {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* Custom Select Styling */
select {
  background-image: none;
}
</style>
