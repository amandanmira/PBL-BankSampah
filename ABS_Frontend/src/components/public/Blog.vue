<script setup>
import { ref, onMounted, inject } from 'vue';
import { useRouter } from 'vue-router';
import dayjs from 'dayjs';

const axios = inject('axios');
const router = useRouter();
const newsList = ref([]);
const loading = ref(true);

const fetchNews = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/berita?limit=3');
    newsList.value = res.data;
  } catch (error) {
    console.error('Failed to fetch news:', error);
  } finally {
    loading.value = false;
  }
};

const goToDetail = (id) => {
  router.push(`/blog/${id}`);
};

onMounted(() => {
  fetchNews();
});
</script>


<template>
  <section class="py-16 md:py-24 bg-[#FAFFF9] relative">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">

      <!-- Headline & Deskripsi -->
      <div class="text-center mb-14 md:mb-16">
        <h2 class="text-3xl md:text-4xl lg:text-[38px] font-extrabold text-[#555555] tracking-tight mb-5">
          Berita & Update Terkini
        </h2>
        <p class="text-[#555555] text-[15px] md:text-[16px] leading-relaxed max-w-2xl mx-auto">
          Ikuti perkembangan terbaru program pengelolaan sampah dan dampak
          positif yang kita ciptakan bersama
        </p>
      </div>

      <!-- Container List Berita (Grid) -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <div v-for="i in 3" :key="i" class="bg-white rounded-xl h-[400px] animate-pulse"></div>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <article v-for="news in newsList" :key="news.berita_id"
          class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 flex flex-col h-full cursor-pointer"
          @click="goToDetail(news.berita_id)"
        >
          <!-- Cover Gambar -->
          <div class="w-full aspect-[16/10] overflow-hidden">
            <img :src="news.thumbnail ? `${axios.defaults.baseURL}/storage/${news.thumbnail}` : 'https://placehold.co/600x400/f3f4f6/94a3b8?text=No+Image'" :alt="news.judul"
              class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
          </div>

          <!-- Konten Card -->
          <div class="p-6 md:p-8 flex flex-col flex-grow">
            <time class="text-[#888888] text-[13px] font-medium mb-3 block">
              {{ dayjs(news.created_at).format('D MMMM YYYY') }}
            </time>

            <h3 class="text-[#555555] text-[20px] lg:text-[22px] font-bold leading-snug mb-4 line-clamp-2">
              {{ news.judul }}
            </h3>

            <p class="text-[#555555] text-[14px] leading-relaxed flex-grow line-clamp-3">
              {{ news.isi.replace(/[#*`_]/g, '') }}
            </p>

            <div class="mt-8">
              <button
                class="inline-flex items-center gap-1.5 text-[#4A7043] font-extrabold text-[14.5px] hover:opacity-80 transition-opacity group">
                Baca Selengkapnya
                <span class="transform group-hover:translate-x-1 transition-transform">→</span>
              </button>
            </div>
          </div>
        </article>
      </div>


      <!-- Tombol Lihat Semua -->
      <div class="text-center mt-12 md:mt-16">
        <RouterLink to="/blog"
          class="bg-[#4A7043] text-white font-bold text-[15px] px-10 py-3.5 rounded-lg hover:bg-[#3d5d36] hover:scale-105 active:scale-95 transition-all shadow-md cursor-pointer inline-block">
          Lihat Semua Berita
        </RouterLink>
      </div>

    </div>
  </section>
</template>

<style scoped></style>