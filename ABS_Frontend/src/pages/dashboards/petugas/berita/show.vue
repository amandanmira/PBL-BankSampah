<template>
  <div class="container mx-auto p-4">
    <div v-if="loading" class="text-center text-gray-500">
      Memuat berita...
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <article v-else-if="berita" class="bg-white shadow-lg rounded-lg overflow-hidden">
      <img v-if="berita.thumbnail" :src="`http://localhost:8000/storage/${berita.thumbnail}`" alt="Thumbnail Berita" class="w-full h-64 object-cover" />

      <div class="p-6">
        <div class="mb-4">
          <span class="text-sm font-semibold text-blue-600 bg-blue-100 rounded-full px-3 py-1">{{ berita.kategori }}</span>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ berita.judul }}</h1>

        <div class="text-sm text-gray-600 mb-4">
          <span>Diterbitkan pada: {{ new Date(berita.tanggal).toLocaleDateString("id-ID", { year: "numeric", month: "long", day: "numeric" }) }}</span>
          <span v-if="berita.petugas"> | Oleh: {{ berita.petugas.nama }}</span>
        </div>

        <div class="prose max-w-none text-gray-800" v-html="berita.isi"></div>
      </div>
    </article>

    <div class="mt-6">
      <button @click="goBack" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        Kembali ke Daftar Berita
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const beritaId = route.params.id;
const token = sessionStorage.getItem("token");

const berita = ref(null);
const loading = ref(true);
const error = ref(null);

if (!token) {
  error.value = "Otentikasi diperlukan. Silakan login kembali.";
  loading.value = false;
}

const fetchData = async () => {
  if (!token) return;

  loading.value = true;
  try {
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get(`/api/petugas/berita/${beritaId}`, { headers });
    berita.value = response.data;
  } catch (err) {
    console.error("Gagal memuat berita:", err);
    error.value = err.response?.data?.message || "Gagal mengambil data dari server.";
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.push("/dashboard-petugas/kelola-berita");
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
/* Styling tambahan jika diperlukan, misalnya untuk konten dari v-html */
.prose :deep(p) {
  margin-bottom: 1rem;
}
.prose :deep(h2) {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 2rem;
  margin-bottom: 1rem;
}
</style>
