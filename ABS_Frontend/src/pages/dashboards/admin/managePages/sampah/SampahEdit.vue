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

    <hr />

    <!-- KATEGORI -->
    <h3>Kategori</h3>

    <button @click="addKategori">+ Tambah Kategori</button>

    <div
v-for="(k, index) in form.item_sampah" :key="k.item_id || index"
      style="border:1px solid #ccc; padding:10px; margin-top:10px;"
    >
      <div>
        <label>Foto</label>
        <input type="file" @change="handleFile($event, index)" />

        <!-- preview -->
        <div>
          <img v-if="k.foto || k.foto_baru" :src="previewFile(k)" width="120" />
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

      <div>
        <input v-model="k.checkBox" type="checkbox" /> Item Aktif
      </div>

      <button v-if="k.active === null" @click="removeKategori(index)">
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
  item_sampah: []
});

const loading = ref(false);
const error = ref(null);

const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.');
}

const headers = {
  'Authorization': `Bearer ${token}`,
  "Content-Type": "multipart/form-data"
}

// ambil data existing
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/admin/kategori-sampah/${id}`, { headers });
    form.value = res.data;

    for (const item of form.value.item_sampah) {
      item.checkBox = item.active === 1;
    }
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

// tambah kategori baru
const addKategori = () => {
  form.value.item_sampah.push({
    foto: null,
    foto_baru: null,
    nama: "",
    harga_beli: 0,
    harga_jual: 0,
    diskon: 0,
    active: null,
    checkBox: true
  });
};

const handleFile = (e, index) => {
  const file = e.target.files[0];
  form.value.item_sampah[index].foto_baru = file;
};

const previewFile = (k) => {
  if (k.foto_baru) {
    return URL.createObjectURL(k.foto_baru);
  }
  return `http://localhost:8000/storage/${k.foto}`;
};

// hapus kategori
const removeKategori = async (index) => {
  form.value.item_sampah.splice(index, 1);
};

// submit update
const submit = async () => {
  loading.value = true;
  error.value = null;

  const formData = new FormData();

  formData.append("nama", form.value.nama);

  let i = 0;
  for (const k of form.value.item_sampah) {
    if (k.kategori_id) {
      formData.append(`item[${i}][item_id]`, k.item_id);
    }

    formData.append(`item[${i}][nama]`, k.nama);
    formData.append(`item[${i}][harga_beli]`, k.harga_beli);
    formData.append(`item[${i}][harga_jual]`, k.harga_jual);
    formData.append(`item[${i}][diskon]`, k.diskon);
    formData.append(`item[${i}][active]`, k.checkBox ? 1 : 0);

    // hanya kirim kalau ada file baru
    if (k.foto_baru) {
      formData.append(`item[${i}][foto]`, k.foto_baru);
    }

    i++;
  };

  try {
    await axios.post(`/api/admin/kategori-sampah/${id}?_method=PUT`, formData, { headers });
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