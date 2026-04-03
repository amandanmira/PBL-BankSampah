<template>
  <div>
    <h2>Tambah Berita</h2>

    <form @submit.prevent="createBerita">
      <div>
        <label for="judul">Judul</label>
        <input type="text" id="judul" v-model="judul" required />
      </div>
      <div>
        <label for="kategori">Kategori</label>
        <select id="kategori" v-model="kategori" required>
          <option value="Berita">Berita</option>
          <option value="Artikel">Artikel</option>
          <option value="Event">Event</option>
        </select>
      </div>
      <div>
        <label for="isi">Isi</label>
        <textarea id="isi" v-model="isi" required></textarea>
      </div>
      <div>
        <label for="thumbnail">Thumbnail</label>
        <input type="file" id="thumbnail" @change="handleFileChange" />
      </div>
      <button type="submit">Simpan</button>
    </form>
    <div v-if="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { checkRole } from "@/utils";

checkRole("petugas");

const judul = ref("");
const isi = ref("");
const thumbnail = ref(null);
const kategori = ref("Berita");
const error = ref(null);
const router = useRouter();
const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error("Otentikasi diperlukan.");
}

const handleFileChange = (e) => {
  thumbnail.value = e.target.files[0];
};

const createBerita = async () => {
  const formData = new FormData();
  formData.append("judul", judul.value);
  formData.append("kategori", kategori.value);
  formData.append("isi", isi.value);
  if (thumbnail.value) {
    formData.append("thumbnail", thumbnail.value);
  }

  try {
    const headers = {
      Authorization: `Bearer ${token}`,
      "Content-Type": "multipart/form-data",
    };
    await axios.post("/api/petugas/berita", formData, { headers });
    router.push("/dashboard-petugas/kelola-berita");
  } catch (err) {
    error.value = err.response?.data?.message || err.message;
  }
};
</script>
