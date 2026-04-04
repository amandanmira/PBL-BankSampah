<template>
  <div>
    <h2>Edit Berita</h2>

    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <div>
      <label>Judul</label><br />
      <input v-model="form.judul" type="text" />
    </div>

    <div style="margin-top: 10px;">
      <label>Kategori</label><br />
      <select v-model="form.kategori">
        <option value="" disabled>Pilih Kategori</option>
        <option value="Berita">Berita</option>
        <option value="Artikel">Artikel</option>
        <option value="Event">Event</option>
      </select>
    </div>

    <div style="margin-top: 10px;">
      <label>Isi Berita</label><br />
      <textarea v-model="form.isi" rows="5" cols="40"></textarea>
    </div>

    <div style="margin-top: 10px;">
      <label>Thumbnail (Opsional)</label><br />
      
      <div v-if="previewUrl" style="margin-bottom: 10px;">
        <img :src="previewUrl" alt="Preview Thumbnail" width="150" />
      </div>

      <input type="file" @change="handleFileUpload" accept="image/*" />
      <br /><small>Biarkan kosong jika tidak ingin mengganti gambar.</small>
    </div>

    <br />
    <hr />
    <br />

    <button @click="submit" :disabled="loading">
      {{ loading ? "Menyimpan..." : "Update" }}
    </button>

    <button @click="router.push('/dashboard-petugas/kelola-berita')">
      Batal
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";
import { checkRole } from '@/utils';

// Sesuaikan role dengan halaman ini
checkRole('petugas');

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const form = ref({
  judul: "",
  kategori: "",
  isi: "",
  thumbnail: null, // Berisi file, bukan string teks
});

const previewUrl = ref(null);
const loading = ref(false);
const error = ref(null);

const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.');
}

const headers = { 'Authorization': `Bearer ${token}` };

// ambil data existing
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/petugas/berita/${id}`, { headers });
    
    // Isi data ke form
    form.value.judul = res.data.judul;
    form.value.kategori = res.data.kategori;
    form.value.isi = res.data.isi;

    // Tampilkan gambar lama jika ada
    if (res.data.thumbnail) {
      previewUrl.value = `http://localhost:8000/storage/${res.data.thumbnail}`;
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message;
  } finally {
    loading.value = false;
  }
};

// Handle file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.value.thumbnail = file;
    // Buat URL sementara untuk preview gambar baru
    previewUrl.value = URL.createObjectURL(file);
  } else {
    form.value.thumbnail = null;
  }
};

// submit update
const submit = async () => {
  loading.value = true;
  error.value = null;

  // KARENA ADA FILE GAMBAR, KITA WAJIB PAKAI FORMDATA
  const formData = new FormData();
  formData.append('judul', form.value.judul);
  formData.append('kategori', form.value.kategori);
  formData.append('isi', form.value.isi);
  
  // Masukkan file jika user memilih gambar baru
  if (form.value.thumbnail) {
    formData.append('thumbnail', form.value.thumbnail);
  }

  // PENTING: Beri tahu Laravel bahwa ini sebenarnya adalah request PUT
  formData.append('_method', 'PUT');

  try {
    // KITA MENGGUNAKAN POST, KARENA FORMDATA BERISI FILE BISA DITOLAK OLEH PUT BAWAAN SERVER
    const config = {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    };

    await axios.post(`/api/petugas/berita/${id}`, formData, config);
    
    alert('Berita berhasil diupdate!');
    router.push("/dashboard-petugas/kelola-berita");
  } catch (err) {
    error.value = err.response?.data?.message || err.message;
    alert("Gagal mengupdate berita. Cek form atau koneksi.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>