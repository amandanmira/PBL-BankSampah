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
        <label>Foto</label>
        <input type="file" @change="handleFile($event, index)" />

        <!-- preview -->
        <div>
          <img v-if="k.foto" :src="previewFile(k.foto)" width="120" />
        </div>
      </div>

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
const fotoFile = ref(null);
const preview = ref(null);

// token
const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

// tambah kategori
const addKategori = () => {
  form.value.kategori.push({
    foto: null,
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

const handleFile = (e, index) => {
  const file = e.target.files[0];
  form.value.kategori[index].foto = file;
};

const previewFile = (file) => {
  return URL.createObjectURL(file);
};

// submit
const submit = async () => {
  loading.value = true;
  error.value = null;

  const formData = new FormData();
  formData.append("nama", form.value.nama);

  let i = 0;
  for (const k of form.value.kategori) {
    formData.append(`kategori[${i}][nama]`, k.nama);
    formData.append(`kategori[${i}][harga_beli]`, k.harga_beli);
    formData.append(`kategori[${i}][harga_jual]`, k.harga_jual);
    formData.append(`kategori[${i}][diskon]`, k.diskon);

    if (k.foto) {
      formData.append(`kategori[${i}][foto]`, k.foto);
    }

    i++;
  }

  try {
    const headers = {
      'Authorization': `Bearer ${token}`,
      "Content-Type": "multipart/form-data"
    }

    await axios.post("/api/admin/jenis-sampah", formData, { headers });
    router.push("/dashboard-admin/kelola-sampah");
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};
</script>