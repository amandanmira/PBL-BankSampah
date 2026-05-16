<script setup>
import { ref, onMounted, inject } from "vue";
import { useRoute, useRouter } from "vue-router";
import { MdPreview } from 'md-editor-v3';
import 'md-editor-v3/lib/preview.css';
import { ArrowLeft, Calendar, User, Clock } from "lucide-vue-next";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import dayjs from "dayjs";

const route = useRoute();
const router = useRouter();
const axios = inject('axios');

const beritaId = route.params.id;
const berita = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/petugas/berita/${beritaId}`);
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

<template>
  <DashboardLayout title="Detail Berita">
    <div class="max-w-4xl mx-auto space-y-8">
      <!-- Navigation -->
      <button 
        @click="goBack" 
        class="group flex items-center gap-2 text-gray-500 hover:text-[#4A7043] font-bold transition-all"
      >
        <div class="p-2 rounded-xl group-hover:bg-[#4A7043]/10 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </div>
        Kembali ke Daftar
      </button>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"></div>
        <p class="text-gray-500 font-medium">Memuat berita...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-100 p-8 rounded-[2rem] text-center">
        <p class="text-red-600 font-bold">{{ error }}</p>
        <button @click="fetchData" class="mt-4 text-red-600 underline">Coba lagi</button>
      </div>

      <!-- Article Content -->
      <article v-else-if="berita" class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100">
        <!-- Hero Thumbnail -->
        <div class="relative aspect-[21/9] w-full overflow-hidden">
          <img 
            :src="berita.thumbnail ? `${axios.defaults.baseURL}/storage/${berita.thumbnail}` : 'https://placehold.co/1200x600/f3f4f6/94a3b8?text=No+Thumbnail'" 
            class="w-full h-full object-cover" 
            alt="Thumbnail"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
          <div class="absolute bottom-8 left-8 right-8">
            <span class="px-4 py-1.5 bg-[#4A7043] text-white text-[10px] font-black uppercase tracking-wider rounded-full shadow-lg">
              {{ berita.kategori }}
            </span>
            <h1 class="text-3xl md:text-4xl font-black text-white mt-4 leading-tight">
              {{ berita.judul }}
            </h1>
          </div>
        </div>

        <!-- Meta Info -->
        <div class="px-8 py-6 bg-gray-50/50 border-b border-gray-100 flex flex-wrap items-center gap-6">
          <div class="flex items-center gap-2.5 text-gray-700 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-[#A86444] flex items-center justify-center text-white">
              <User class="w-4 h-4" />
            </div>
            {{ berita.petugas?.nama || 'Petugas' }}
          </div>
          <div class="flex items-center gap-2 text-gray-500 font-semibold text-sm">
            <Calendar class="w-4 h-4" />
            {{ dayjs(berita.tanggal).format('D MMMM YYYY') }}
          </div>
          <div class="flex items-center gap-2 text-gray-500 font-semibold text-sm">
            <Clock class="w-4 h-4" />
            {{ dayjs(berita.created_at).format('HH:mm') }} WIB
          </div>
        </div>

        <!-- Body -->
        <div class="p-8 md:p-12">
          <MdPreview 
            :model-value="berita.isi" 
            language="en-US"
            class="!bg-transparent"
          />
        </div>
      </article>
    </div>
  </DashboardLayout>
</template>

<style>
/* Adjusting MdPreview styling to match premium feel */
.md-editor-preview-wrapper {
  padding: 0 !important;
}
.md-editor-preview {
  font-family: 'Inter', sans-serif !important;
  font-size: 1.1rem !important;
  color: #374151 !important;
  line-height: 1.8 !important;
}
.md-editor-preview h1, .md-editor-preview h2, .md-editor-preview h3 {
  color: #111827 !important;
  font-weight: 800 !important;
  margin-top: 2rem !important;
  margin-bottom: 1rem !important;
}
.md-editor-preview p {
  margin-bottom: 1.5rem !important;
}
.md-editor-preview blockquote {
  border-left-color: #4A7043 !important;
  background-color: #f9fafb !important;
  padding: 1.5rem !important;
  border-radius: 0 1rem 1rem 0 !important;
  font-style: italic !important;
}
</style>
