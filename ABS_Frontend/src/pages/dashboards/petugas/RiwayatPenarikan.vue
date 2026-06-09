<template>
  <div class="container">
    <h1>Riwayat Penarikan (Petugas)</h1>
    <p>Daftar seluruh permintaan penarikan yang telah selesai diproses atau ditolak.</p>

    <div v-if="loading" class="loading">Memuat data...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-else-if="allPenarikans.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Nasabah</th>
          <th>Jumlah Penarikan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in allPenarikans" :key="p.penarikan_id">
          <td>{{ p.penarikan_id }}</td>
          <td>{{ p.nasabah?.nama || "N/A" }}</td>
          <td>{{ formatRupiah(p.jumlah) }}</td>
          <td>
            <span :class="`status-${p.status}`">{{ p.status }}</span>
          </td>
          <td>
            <button @click="openDetail(p.penarikan_id)" class="btn-show">
              Lihat Detail
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else-if="!loading">Belum ada riwayat penarikan.</div>

    <!-- Modal untuk menampilkan detail -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Rincian Penarikan #{{ detail.penarikan_id }}</h2>
          <button @click="closeModal" class="close-x">&times;</button>
        </div>

        <div v-if="loadingDetail" class="loading">Mengambil rincian...</div>

        <div v-else class="modal-body">
          <div class="section-info">
            <p><strong>Nasabah:</strong> {{ detail.nasabah?.nama }}</p>
            <p>
              <strong>Status:</strong> <span :class="`status-${detail.status}`">{{ detail.status }}</span>
            </p>
            <p><strong>Jumlah:</strong> {{ formatRupiah(detail.jumlah) }}</p>
            <p><strong>Bank:</strong> {{ detail.nasabah?.nama_bank }} - {{ detail.nasabah?.no_rekening }}</p>
            <p v-if="detail.petugas"><strong>Petugas Pemroses:</strong> {{ detail.petugas?.nama }}</p>
            <p v-if="detail.petugas?.gudang"><strong>Asal Gudang:</strong> {{ detail.petugas?.gudang?.alamat }}</p>
            <p v-if="detail.status === 'tolak'"><strong>Alasan Ditolak:</strong> {{ detail.ket_status || "-" }}</p>
          </div>

          <div v-if="detail.status === 'selesai'" class="transaction-area">
            <hr />
            <h3>Bukti Transfer</h3>
            <img v-if="detail.bukti_tf" :src="`http://localhost:8000/storage/${detail.bukti_tf}`" alt="Bukti Transfer" class="bukti-tf-img" />
            <p v-else>Bukti transfer tidak ditemukan.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";

checkRole("petugas");

const allPenarikans = ref([]);
const loading = ref(true);
const error = ref(null);

// State untuk Modal
const showModal = ref(false);
const detail = ref({});
const loadingDetail = ref(false);

const fetchData = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/riwayat-penarikan", { headers });
    allPenarikans.value = response.data.data;
  } catch (err) {
    error.value = "Gagal memuat data riwayat penarikan.";
  } finally {
    loading.value = false;
  }
};

const openDetail = async (id) => {
  showModal.value = true;
  loadingDetail.value = true;
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get(`http://localhost:8000/api/petugas/riwayat-penarikan/${id}`, { headers });
    detail.value = response.data.data;
  } catch (err) {
    alert("Gagal memuat detail penarikan.");
  } finally {
    loadingDetail.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  detail.value = {};
};

const formatRupiah = (val) => {
  if (val === null || val === undefined) return "Rp 0";
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(val);
};

onMounted(fetchData);
</script>

<style scoped>
.container {
  padding: 2rem;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
th,
td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}
th {
  background-color: #f4f4f4;
}

.btn-show {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-show:hover {
  background-color: #2980b9;
}

/* Modal CSS */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}
.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  margin-bottom: 1rem;
}
.close-x {
  border: none;
  background: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.section-info p {
  margin: 8px 0;
}
.transaction-area h3 {
  color: #333;
  margin-top: 1rem;
}
.bukti-tf-img {
  max-width: 100%;
  border-radius: 5px;
  margin-top: 10px;
  border: 1px solid #ddd;
}

/* Status Colors */
.status-selesai {
  background-color: #2ecc71;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}
.status-tolak {
  background-color: #e74c3c;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}
</style>
