<template>
  <div>
    <h2>Edit Jenis Sampah</h2>

    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <!-- FORM JENIS -->
    <div>
      <label>Nama Jenis</label><br />
      <input v-model="form.nama" type="text" />
    </div>

    <div>
      <label>Stok Jenis</label><br />
      <input v-model="form.stok_jenis" type="number" />
    </div>

    <hr />

    <!-- KATEGORI -->
    <h3>Kategori</h3>

    <button @click="addKategori">+ Tambah Kategori</button>

    <div
      v-for="(k, index) in form.kategori_sampah"
      :key="k.kategori_id || index"
      style="border:1px solid #ccc; padding:10px; margin-top:10px;"
    >
      <div>
        <label>Nama</label><br />
        <input v-model="k.nama" type="text" />
      </div>

      <div>
        <label>Harga Beli</label><br />
        <input v-model="k.harga_beli" type="number" />
      </div>

      <div>
        <label>Harga Jual</label><br />
        <input v-model="k.harga_jual" type="number" />
      </div>

      <div>
        <label>Diskon</label><br />
        <input v-model="k.diskon" type="number" step="0.01" />
      </div>

      <div>
        <label>Stok</label><br />
        <input v-model="k.stok" type="number" />
      </div>

      <button @click="removeKategori(index)">
        Hapus
      </button>
    </div>

    <br />

    <button @click="submit" :disabled="loading">
      {{ loading ? "Menyimpan..." : "Update" }}
    </button>

    <button @click="router.push('/dashboard-admin/kelola-sampah')">
      Batal
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";
import { checkRole } from '@/utils'

checkRole('admin')

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const form = ref({
  nama: "",
  stok_jenis: 0,
  kategori_sampah: []
});

const loading = ref(false);
const error = ref(null);

const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.');
}

const headers = { 'Authorization': `Bearer ${token}` }

// ambil data existing
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/admin/jenis-sampah/${id}`, {headers});
    form.value = res.data;
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

// tambah kategori baru
const addKategori = () => {
  form.value.kategori_sampah.push({
    nama: "",
    harga_beli: 0,
    harga_jual: 0,
    diskon: 0,
    stok: 0
  });
};

// hapus kategori
const removeKategori = async (index) => {
  const k = form.value.kategori_sampah[index];

  // kalau sudah ada di database → hit API delete
  if (k.kategori_id) {
    if (!confirm("Hapus kategori ini?")) return;

    try {
      await axios.delete(`/api/admin/kategori-sampah/${k.kategori_id}`, {headers});
    } catch (err) {
      alert("Gagal hapus kategori");
      return;
    }
  }

  form.value.kategori_sampah.splice(index, 1);
};

// submit update
const submit = async () => {
  loading.value = true;
  error.value = null;

  try {
    await axios.put(`/api/admin/jenis-sampah/${id}`, form.value, {headers});
    router.push("/dashboard-admin/kelola-sampah");
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>