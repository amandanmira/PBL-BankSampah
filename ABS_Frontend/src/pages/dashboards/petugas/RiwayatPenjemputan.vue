<template>
  <div class="container">
    <h1>Riwayat Penjemputan (Petugas)</h1>
    <p>Daftar seluruh tugas penjemputan yang telah atau sedang diproses.</p>

    <div v-if="loading" class="loading">Memuat data...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-else-if="filteredPenjemputans.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Nasabah</th>
          <th>Alamat</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in filteredPenjemputans" :key="p.penjemputan_id">
          <td>{{ p.penjemputan_id }}</td>
          <td>{{ p.nasabah?.nama || "N/A" }}</td>
          <td>{{ p.alamat }}</td>
          <td>
            <span :class="`status-${p.status}`">{{ p.status }}</span>
          </td>
          <td>
            <button @click="openDetail(p.penjemputan_id)" class="btn-show">Lihat Detail</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else-if="!loading">Belum ada riwayat penjemputan.</div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Rincian Penjemputan #{{ detail.penjemputan_id }}</h2>
          <button @click="closeModal" class="close-x">&times;</button>
        </div>

        <div v-if="loadingDetail" class="loading">Mengambil rincian...</div>

        <div v-else class="modal-body">
          <div class="section-info">
            <p><strong>Nasabah:</strong> {{ detail.nasabah?.nama }}</p>
            <p>
              <strong>Status:</strong> <span :class="`status-${detail.status}`">{{ detail.status }}</span>
            </p>
            <p><strong>Alamat:</strong> {{ detail.alamat }}</p>
          </div>

          <div v-if="detail.status === 'selesai' && detail.penimbangan?.length > 0" class="transaction-area">
            <hr />
            <h3>Data Penimbangan & Transaksi</h3>

            <div class="card-transaksi">
              <p><strong>ID Transaksi:</strong> {{ detail.penimbangan[0].transaksi?.transaksi_id }}</p>
              <p><strong>Tipe:</strong> {{ detail.penimbangan[0].transaksi?.tipe_transaksi }}</p>
            </div>

            <table class="table-mini">
              <thead>
                <tr>
                  <th>Jenis Sampah</th>
                  <th>Berat</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in detail.penimbangan" :key="item.penimbangan_id">
                  <td>{{ item.sampah?.item_sampah?.nama }}</td>
                  <td>{{ item.berat_timbang }} kg</td>
                  <td>{{ formatRupiah(item.berat_timbang * (item.sampah?.item_sampah?.harga_beli || 0)) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2">Total Nilai:</th>
                  <th>{{ formatRupiah(calculateTotal(detail.penimbangan)) }}</th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div v-else-if="detail.status === 'proses'" class="alert-proses">
            <p>Status masih <strong>Proses</strong>. Data transaksi dan timbangan belum tersedia sampai penimbangan dilakukan.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";

// Hak akses Petugas
checkRole("petugas");

const allPenjemputans = ref([]);
const loading = ref(true);
const error = ref(null);

// State Modal
const showModal = ref(false);
const detail = ref({});
const loadingDetail = ref(false);

const filteredPenjemputans = computed(() => {
  return allPenjemputans.value.filter((p) => p.status === "selesai" || p.status === "tolak");
});

const fetchData = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/riwayat-penjemputan", { headers });
    allPenjemputans.value = response.data.data;
  } catch (err) {
    error.value = "Gagal memuat data.";
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
    const response = await axios.get(`http://localhost:8000/api/petugas/riwayat-penjemputan/${id}`, { headers });
    detail.value = response.data.data;
  } catch (err) {
    alert("Gagal memuat detail");
  } finally {
    loadingDetail.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  detail.value = {};
};

const calculateTotal = (items) => {
  return items.reduce((sum, i) => sum + i.berat_timbang * (i.sampah?.item_sampah?.harga_beli || 0), 0);
};

const formatRupiah = (val) => {
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
  background-color: #4a6b46;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
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
  width: 500px;
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
  margin: 5px 0;
}
.transaction-area h3 {
  color: #4a6b46;
  margin-top: 1rem;
}
.card-transaksi {
  background: #f9f9f9;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 10px;
  border-left: 4px solid #4a6b46;
}
.table-mini {
  font-size: 0.9em;
  margin-top: 10px;
}
.alert-proses {
  background: #e3f2fd;
  color: #1976d2;
  padding: 1rem;
  border-radius: 5px;
  margin-top: 1rem;
}

/* Status Colors */
.status-proses {
  color: #3498db;
  font-weight: bold;
}
.status-selesai {
  color: #2ecc71;
  font-weight: bold;
}
</style>
