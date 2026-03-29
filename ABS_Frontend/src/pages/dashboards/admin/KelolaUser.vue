<template>
  <div class="container">
    <h1>Kelola Semua Akun</h1>
    <p>Halaman ini hanya bisa diakses oleh admin.</p>

    <div class="filter-container">
      <label for="role-filter">Filter berdasarkan Role:</label>
      <select id="role-filter" v-model="selectedRole">
        <option value="all">Semua</option>
        <option value="Admin">Admin</option>
        <option value="Manager">Manager</option>
        <option value="Petugas">Petugas</option>
        <option value="Pengepul">Pengepul</option>
        <option value="Nasabah">Nasabah</option>
        <option value="Tukang">Tukang</option>
      </select>
    </div>

    <div v-if="loading" class="loading">
      Memuat data...
    </div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <table v-else-if="filteredAccounts.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="akun in filteredAccounts" :key="akun.id">
          <td>{{ akun.id }}</td>
          <td>{{ akun.nama }}</td>
          <td>{{ akun.username }}</td>
          <td>{{ akun.email }}</td>
          <td>{{ akun.role }}</td>
          <td>
            <div v-if="akun.role === 'Petugas'">
              <button @click="togglePetugasStatus(akun)">
                {{ akun.active ? "Nonaktifkan" : "Aktifkan" }}
              </button>
            </div>
            <div v-else-if="akun.role === 'Pengepul'">
              <button @click="togglePengepulStatus(akun)">
                {{ akun.status === "aktif" ? "Nonaktifkan" : "Aktifkan" }}
              </button>
            </div>
            <div v-else-if="akun.role === 'Nasabah'">
              <button @click="toggleNasabahStatus(akun)">
                {{ akun.status === "aktif" ? "Nonaktifkan" : "Aktifkan" }}
              </button>
            </div>
            <div v-else>
              <button disabled>
                N/A
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">
      Tidak ada data akun untuk ditampilkan.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";

// Periksa role saat komponen dimuat
checkRole("admin");

const allAccounts = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedRole = ref("all"); // Untuk menyimpan filter yang dipilih

// Computed property untuk memfilter akun berdasarkan selectedRole
const filteredAccounts = computed(() => {
  if (selectedRole.value === "all") {
    return allAccounts.value;
  }
  return allAccounts.value.filter((akun) => akun.role === selectedRole.value);
});

const togglePetugasStatus = async (petugas) => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }

    const headers = { Authorization: `Bearer ${token}` };
    const action = petugas.active ? "deactivate" : "activate";

    await axios.put(`http://localhost:8000/api/admin/petugas/${petugas.id}/${action}`, {}, { headers });

    // Update status di frontend secara langsung
    const index = allAccounts.value.findIndex((p) => p.id === petugas.id && p.role === "Petugas");
    if (index !== -1) {
      allAccounts.value[index].active = !allAccounts.value[index].active;
    }
  } catch (err) {
    error.value = "Gagal mengubah status petugas. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

const togglePengepulStatus = async (pengepul) => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }

    const headers = { Authorization: `Bearer ${token}` };
    const action = pengepul.status === "aktif" ? "deactivate" : "activate";

    await axios.put(`http://localhost:8000/api/admin/pengepul/${pengepul.id}/${action}`, {}, { headers });

    // Update status di frontend secara langsung
    const index = allAccounts.value.findIndex((p) => p.id === pengepul.id && p.role === "Pengepul");
    if (index !== -1) {
      allAccounts.value[index].status = allAccounts.value[index].status === "aktif" ? "nonaktif" : "aktif";
    }
  } catch (err) {
    error.value = "Gagal mengubah status pengepul. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

const toggleNasabahStatus = async (nasabah) => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }

    const headers = { Authorization: `Bearer ${token}` };
    const action = nasabah.status === "aktif" ? "deactivate" : "activate";

    await axios.put(`http://localhost:8000/api/admin/nasabah/${nasabah.id}/${action}`, {}, { headers });

    // Update status di frontend secara langsung
    const index = allAccounts.value.findIndex((n) => n.id === nasabah.id && n.role === "Nasabah");
    if (index !== -1) {
      allAccounts.value[index].status = allAccounts.value[index].status === "aktif" ? "nonaktif" : "aktif";
    }
  } catch (err) {
    error.value = "Gagal mengubah status nasabah. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

onMounted(async () => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }

    const headers = { Authorization: `Bearer ${token}` };

    // Definisikan endpoint dan peran
    const roles = [
      { name: "Petugas", endpoint: "petugas", idField: "petugas_id" },
      { name: "Pengepul", endpoint: "pengepul", idField: "pengepul_id" },
      { name: "Nasabah", endpoint: "nasabah", idField: "nasabah_id" },
      { name: "Tukang", endpoint: "tukang", idField: "tukang_id" },
      { name: "Admin", endpoint: "admin", idField: "admin_id" },
      { name: "Manager", endpoint: "manager", idField: "manager_id" },
    ];

    // Ambil semua data secara paralel
    const responses = await Promise.all(roles.map((role) => axios.get(`http://localhost:8000/api/admin/${role.endpoint}`, { headers })));

    // Proses dan gabungkan semua data
    const combinedData = responses.flatMap((response, index) => {
      const role = roles[index];
      return response.data.data.map((item) => ({
        ...item,
        id: item[role.idField],
        role: role.name,
      }));
    });

    allAccounts.value = combinedData;
  } catch (err) {
    error.value = "Gagal mengambil data akun. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.container {
  padding: 2rem;
}
.filter-container {
  margin-bottom: 1rem;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
th,
td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}
th {
  background-color: #f2f2f2;
}
.loading,
.error {
  margin-top: 1rem;
  color: #888;
}
.error {
  color: red;
}
</style>
