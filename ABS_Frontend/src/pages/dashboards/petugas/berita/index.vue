<script setup>
import { ref, onMounted, inject } from "vue";
import { Icon } from "@iconify/vue";
import { Plus, Pencil, Trash2, Calendar, User, Search, Filter } from "lucide-vue-next";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import NewsModal from "./components/NewsModal.vue";
import Swal from "sweetalert2";
import dayjs from "dayjs";

const axios = inject('axios');
const beritaList = ref([]);
const loading = ref(false);
const isModalOpen = ref(false);
const selectedNews = ref(null);
const searchQuery = ref("");
const filterKategori = ref("Semua");

const fetchBerita = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/api/petugas/berita");
    // API returns paginated data (data.data) or simple array depending on controller
    beritaList.value = res.data.data || res.data;
  } catch (err) {
    console.error("Failed to fetch berita:", err);
    Swal.fire("Error", "Gagal mengambil data berita", "error");
  } finally {
    loading.value = false;
  }
};

const handleCreate = () => {
  selectedNews.value = null;
  isModalOpen.value = true;
};

const handleEdit = (berita) => {
  selectedNews.value = berita;
  isModalOpen.value = true;
};

const handleDelete = async (id) => {
  const result = await Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Berita yang dihapus tidak dapat dikembalikan!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#4A7043",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/petugas/berita/${id}`);
      Swal.fire("Terhapus!", "Berita telah berhasil dihapus.", "success");
      fetchBerita();
    } catch (err) {
      console.error("Delete error:", err);
      Swal.fire("Gagal!", "Gagal menghapus berita.", "error");
    }
  }
};

const handleSave = async (formData) => {
  try {
    if (selectedNews.value) {
      // Update
      // Laravel often needs _method: PUT for FormData
      formData.append('_method', 'PUT');
      await axios.post(`/api/petugas/berita/${selectedNews.value.berita_id}`, formData);
      Swal.fire("Berhasil!", "Berita diperbarui.", "success");
    } else {
      // Create
      await axios.post("/api/petugas/berita", formData);
      Swal.fire("Berhasil!", "Berita baru dibuat.", "success");
    }
    isModalOpen.value = false;
    fetchBerita();
  } catch (err) {
    console.error("Save error:", err);
    const msg = err.response?.data?.message || "Terjadi kesalahan saat menyimpan berita";
    Swal.fire("Gagal!", msg, "error");
  }
};

const getFilteredBerita = () => {
  return beritaList.value.filter(item => {
    const matchesSearch = item.judul.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesKategori = filterKategori.value === "Semua" || item.kategori === filterKategori.value;
    return matchesSearch && matchesKategori;
  });
};

const truncateText = (text, length) => {
  if (!text) return "";
  // Remove markdown tags for snippet
  const cleanText = text.replace(/[#*`_]/g, '');
  return cleanText.length > length ? cleanText.substring(0, length) + "..." : cleanText;
};

onMounted(() => {
  fetchBerita();
});
</script>

<template>
  <DashboardLayout title="Kelola Berita">
    <div class="space-y-8">
      <!-- Header Actions -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <p class="text-gray-500 font-medium">Buat dan kelola berita untuk nasabah</p>
        </div>
        <button 
          @click="handleCreate"
          class="flex items-center gap-2 px-6 py-3 bg-[#4A7043] text-white rounded-2xl font-bold shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37] transition-all transform hover:scale-[1.02]"
        >
          <Plus class="w-5 h-5" />
          Buat Berita Baru
        </button>
      </div>

      <!-- Search & Filter -->
      <div class="flex flex-col md:flex-row gap-4">
        <div class="relative flex-1">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari judul berita..."
            class="w-full pl-12 pr-6 py-3.5 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all shadow-sm"
          />
        </div>
        <div class="relative min-w-[180px]">
          <Filter class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <select 
            v-model="filterKategori"
            class="w-full pl-12 pr-6 py-3.5 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all shadow-sm appearance-none cursor-pointer font-medium"
          >
            <option value="Semua">Semua Kategori</option>
            <option value="Berita">Berita</option>
            <option value="Artikel">Artikel</option>
            <option value="Event">Event</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"></div>
        <p class="text-gray-500 font-medium">Memuat data berita...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="getFilteredBerita().length === 0" class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-[2rem] border border-dashed border-gray-200">
        <div class="bg-gray-50 p-6 rounded-full mb-4">
          <Icon icon="material-symbols:article-outline" class="w-12 h-12 text-gray-300" />
        </div>
        <h3 class="text-xl font-bold text-gray-800">Tidak Ada Berita</h3>
        <p class="text-gray-500 mt-2">Belum ada berita yang sesuai dengan pencarian Anda.</p>
        <button @click="handleCreate" class="mt-6 text-[#4A7043] font-bold hover:underline">
          Buat berita pertama sekarang
        </button>
      </div>

      <!-- News Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="item in getFilteredBerita()" 
          :key="item.berita_id"
          class="group bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col"
        >
          <!-- Thumbnail -->
          <div class="relative aspect-video overflow-hidden">
            <img 
              :src="item.thumbnail ? `${axios.defaults.baseURL}/storage/${item.thumbnail}` : 'https://placehold.co/600x400/f3f4f6/94a3b8?text=No+Image'" 
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
              alt="Thumbnail"
            />
            <div class="absolute top-4 left-4">
              <span class="px-4 py-1.5 bg-white/90 backdrop-blur-md text-[#4A7043] text-[10px] font-black uppercase tracking-wider rounded-full shadow-sm">
                {{ item.kategori }}
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6 flex-1 flex flex-col">
            <h3 class="text-lg font-extrabold text-gray-800 leading-snug group-hover:text-[#4A7043] transition-colors mb-2">
              {{ item.judul }}
            </h3>
            
            <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
              {{ truncateText(item.isi, 120) }}
            </p>

            <div class="mt-auto pt-6 border-t border-gray-50 flex items-center justify-between">
              <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 text-gray-700 font-bold text-xs">
                  <User class="w-3 h-3 text-gray-400" />
                  {{ item.petugas?.nama || 'Petugas' }}
                </div>
                <div class="flex items-center gap-2 text-gray-400 font-semibold text-[10px]">
                  <Calendar class="w-3 h-3" />
                  {{ dayjs(item.tanggal).format('D/M/YYYY') }}
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2">
                <button 
                  @click="handleEdit(item)"
                  class="p-2.5 bg-green-50 text-green-600 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-sm"
                  title="Edit Berita"
                >
                  <Pencil class="w-4 h-4" />
                </button>
                <button 
                  @click="handleDelete(item.berita_id)"
                  class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm"
                  title="Hapus Berita"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <NewsModal 
      :is-open="isModalOpen"
      :news-data="selectedNews"
      @close="isModalOpen = false"
      @saved="handleSave"
    />
  </DashboardLayout>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
