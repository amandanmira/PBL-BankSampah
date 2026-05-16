<script setup>
import { useRoute, useRouter } from 'vue-router'
import { ref, onMounted, inject, watch } from 'vue'
import { MdPreview } from 'md-editor-v3';
import 'md-editor-v3/lib/preview.css';
import dayjs from 'dayjs';

const route = useRoute()
const router = useRouter()
const axios = inject('axios')

const article = ref(null);
const relatedNews = ref([]);
const loading = ref(true);

const fetchArticle = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/berita/${route.params.id}`);
    article.value = res.data;
    
    // Fetch related news (limit 3, exclude current)
    const relatedRes = await axios.get(`/api/berita?limit=4`);
    relatedNews.value = relatedRes.data.filter(n => n.berita_id != route.params.id).slice(0, 3);
  } catch (error) {
    console.error('Failed to fetch article:', error);
  } finally {
    loading.value = false;
  }
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchArticle();
    window.scrollTo(0, 0);
  }
});

onMounted(() => {
  fetchArticle();
  window.scrollTo(0, 0);
})
</script>


<template>
  <div class="bg-[#F5F5F0] min-h-screen py-10 md:py-16 font-sans">
    <div class="max-w-4xl mx-auto px-6 md:px-12">
      
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"></div>
        <p class="text-gray-500 font-medium tracking-widest uppercase text-xs">Membuka Berita...</p>
      </div>

      <template v-else-if="article">
        <!-- Breadcrumb -->
        <nav class="text-[12px] md:text-[13px] font-bold text-[#777777] uppercase tracking-widest mb-8">
          Beranda <span class="mx-2 font-normal">/</span> Berita <span class="mx-2 font-normal">/</span> <span class="text-[#555555]">{{ article.kategori }}</span>
        </nav>

        <!-- Back Link -->
        <div class="mb-10">
          <RouterLink to="/blog" class="inline-flex items-center gap-2 text-[#4A7043] font-bold text-[14.5px] hover:text-[#385532] transition-colors group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Berita
          </RouterLink>
        </div>

        <!-- Category Pill -->
        <div class="mb-5">
          <span class="bg-[#4A7043] text-white px-4 py-1.5 rounded-full text-[11px] md:text-xs font-bold uppercase tracking-widest shadow-sm">
            {{ article.kategori }}
          </span>
        </div>

        <!-- Title -->
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-[#111111] leading-snug lg:leading-tight mb-8 tracking-tight">
          {{ article.judul }}
        </h1>

        <!-- Author and Meta -->
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-[#CCCCCC]/80 pb-6 mb-10 gap-6">
          <div class="flex items-center gap-4">
            <div class="w-[46px] h-[46px] md:w-12 md:h-12 rounded-full bg-[#4A7043] text-white flex items-center justify-center font-bold text-lg md:text-xl uppercase shadow-md">
              {{ article.petugas?.nama?.charAt(0) || 'P' }}
            </div>
            <div class="flex flex-col">
              <h4 class="font-bold text-[#555555] text-[14.5px] leading-snug mb-1">{{ article.petugas?.nama || 'Petugas ABS' }}</h4>
              <div class="flex items-center text-[12px] md:text-[12.5px] font-semibold text-[#777777] gap-2 md:gap-3">
                <span class="flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ dayjs(article.created_at).format('D MMMM YYYY') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Image Banner -->
        <div class="w-full aspect-video md:aspect-[21/10] bg-gray-200 rounded-[1.25rem] md:rounded-[1.5rem] overflow-hidden mb-12 shadow-md relative">
          <img :src="article.thumbnail ? `${axios.defaults.baseURL}/storage/${article.thumbnail}` : 'https://placehold.co/1200x600/f3f4f6/94a3b8?text=No+Image'" :alt="article.judul" class="absolute inset-0 w-full h-full object-cover" />
        </div>

        <!-- Content rendering -->
        <div class="prose prose-lg max-w-none text-[#333333] leading-relaxed mb-16 gsap-fade-up">
          <MdPreview :model-value="article.isi" language="en-US" class="!bg-transparent !p-0" />
        </div>

        <!-- Tags Pills -->
        <div v-if="article.tags?.length" class="flex flex-wrap items-center gap-3 mb-16 gsap-fade-up">
          <span class="text-[12px] font-extrabold text-[#777777] uppercase tracking-widest mr-2">Tags:</span>
          <button v-for="tag in article.tags" :key="tag" class="bg-white border border-[#E0E0E0] rounded-full px-4 py-1.5 text-[12.5px] font-bold text-[#555555] hover:border-[#4A7043] hover:text-[#4A7043] transition-colors shadow-sm">
            {{ tag }}
          </button>
        </div>

        <!-- Recommended Articles Section -->
        <div v-if="relatedNews.length" class="mb-20 gsap-fade-up">
          <h3 class="text-2xl md:text-3xl font-extrabold text-[#111111] mb-8">Rekomendasi Untuk Anda</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="news in relatedNews" :key="news.berita_id" @click="router.push(`/blog/${news.berita_id}`)" class="flex flex-col cursor-pointer group">
              <div class="aspect-[16/10] rounded-xl overflow-hidden mb-4 bg-gray-200">
                <img :src="news.thumbnail ? `${axios.defaults.baseURL}/storage/${news.thumbnail}` : 'https://placehold.co/600x400/f3f4f6/94a3b8?text=No+Image'" :alt="news.judul" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              </div>
              <h4 class="font-bold text-[#111111] leading-snug group-hover:text-[#4A7043] transition-colors line-clamp-2">{{ news.judul }}</h4>
              <span class="text-xs font-bold text-[#777777] uppercase mt-2">{{ dayjs(news.created_at).format('D MMM YYYY') }}</span>
            </div>
          </div>
        </div>

      </template>

      <div v-else class="text-center py-20">
        <p class="text-gray-500 font-bold">Berita tidak ditemukan.</p>
        <RouterLink to="/blog" class="text-[#4A7043] underline mt-4 inline-block">Kembali ke Blog</RouterLink>
      </div>


    </div>
  </div>
</template>

<style scoped>
/* Inject safe internal formatting specifically for user generated v-html */
:deep(.prose p) {
  margin-bottom: 1.5rem;
  line-height: 1.8;
  color: #555555;
}
</style>