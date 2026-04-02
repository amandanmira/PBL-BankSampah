<template>
  <div>
    <h2>Tambah Jenis Sampah</h2>

    <div v-if="error">{{ error }}</div>

    <!-- FORM JENIS -->
    <div>
      <label>Nama Jenis</label><br />
      <input v-model="form.nama" type="text" />
    </div>

    <hr />

    <!-- KATEGORI -->
    <h3>Kategori Sampah</h3>

    <button @click="addKategori">+ Tambah Kategori</button>

    <div
      v-for="(k, index) in form.kategori"
      :key="index"
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

      <button @click="removeKategori(index)">
        Hapus Kategori
      </button>
    </div>

    <br />

    <button @click="submit" :disabled="loading">
      {{ loading ? "Menyimpan..." : "Simpan" }}
    </button>

    <button @click="router.push('/dashboard-admin/kelola-sampah')">
      Batal
    </button>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { checkRole } from '@/utils'

checkRole('admin')

const router = useRouter();

// state utama
const form = ref({
  nama: "",
  kategori: []
});

const loading = ref(false);
const error = ref(null);

// token
const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

// tambah kategori
const addKategori = () => {
  form.value.kategori.push({
    nama: "",
    harga_beli: 0,
    harga_jual: 0,
    diskon: 0,
  });
};

// hapus kategori
const removeKategori = (index) => {
  form.value.kategori.splice(index, 1);
};

// submit
const submit = async () => {
  loading.value = true;
  error.value = null;

  try {
    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.post("/api/admin/jenis-sampah", form.value, {headers});
    router.push("/dashboard-admin/kelola-sampah");
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};
</script>