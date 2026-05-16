<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { useRouter } from 'vue-router';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

const axios = inject('axios');
const router = useRouter();
const newsList = ref([]);
const loading = ref(true);

const goToBlog = (id) => {
  if(id) router.push('/blog/' + id);
};

// Kategori Filter
const categories = ["Semua", "Berita", "Artikel", "Event"];
const activeCategory = ref("Semua");
const currentPage = ref(1);
const lastPage = ref(1);

const fetchNews = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/berita?page=${page}`);
    newsList.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
  } catch (error) {
    console.error('Failed to fetch news:', error);
  } finally {
    loading.value = false;
  }
};

const selectCategory = (kat) => {
  activeCategory.value = kat;
  currentPage.value = 1;
}

const nextPage = () => {
  if (currentPage.value < lastPage.value) fetchNews(currentPage.value + 1);
}
const prevPage = () => {
  if (currentPage.value > 1) fetchNews(currentPage.value - 1);
}
const goToPage = (page) => {
  fetchNews(page);
}

// Logic to distribute news
const filteredNews = computed(() => {
  if (activeCategory.value === "Semua") return newsList.value;
  return newsList.value.filter(n => n.kategori === activeCategory.value);
});

const featuredPost = computed(() => filteredNews.value[0] || null);
const latestPosts = computed(() => filteredNews.value.slice(1, 5));
const allRelatedPosts = computed(() => filteredNews.value.slice(5));

const relatedPage = ref(1);
const maxRelatedPage = computed(() => Math.ceil(allRelatedPosts.value.length / 3));

const relatedPosts = computed(() => {
  const start = (relatedPage.value - 1) * 3;
  return allRelatedPosts.value.slice(start, start + 3);
});

const nextRelated = () => {
  if (relatedPage.value < maxRelatedPage.value) relatedPage.value++;
}
const prevRelated = () => {
  if (relatedPage.value > 1) relatedPage.value--;
}

onMounted(() => {
  fetchNews();
});
</script>

<template>
  <div class="bg-[#F5F5F0] min-h-screen font-sans flex flex-col">
    
    <!-- Hero/Header Section Berita -->
    <div class="bg-[#4A7043] w-full pt-12 pb-14 md:pt-16 md:pb-20 px-6 md:px-10 lg:px-16 text-[#F5F5F0] relative overflow-hidden">
      <!-- Ornamen Dekorasi Halus -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
      
      <div class="max-w-[1240px] mx-auto relative z-10">
        <RouterLink to="/" class="inline-flex items-center gap-2 text-[13px] md:text-[14.5px] font-medium hover:text-white/80 transition-colors mb-8 md:mb-10">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Kembali ke Beranda
        </RouterLink>
        <h1 class="text-4xl md:text-5xl lg:text-[56px] font-extrabold tracking-tight mb-5 leading-tight">
          Berita & Update
        </h1>
        <p class="text-[15px] md:text-[17px] max-w-2xl leading-relaxed text-[#F5F5F0]/90">
          Ikuti perkembangan terbaru tentang program pengelolaan sampah dan dampak positif yang kita ciptakan bersama untuk lingkungan yang lebih baik.
        </p>
      </div>
    </div>

    <!-- Category Filter Navigation Bar -->
    <div class="w-full bg-white border-b border-[#CCCCCC]/60 overflow-x-auto shadow-sm">
      <!-- Menggunakan flex-nowrap agar bisa d-scroll horizontal di mobile -->
      <div class="max-w-[1240px] mx-auto px-6 md:px-10 lg:px-16 py-4 flex items-center gap-3 w-max md:w-full md:flex-wrap">
        <button 
          v-for="cat in categories" 
          :key="cat"
          @click="selectCategory(cat)"
          :class="cat === activeCategory 
            ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-md' 
            : 'bg-transparent text-[#777777] border-[#CCCCCC] hover:border-[#4A7043] hover:text-[#4A7043]'"
          class="border rounded-full px-5 py-2 text-[13.5px] md:text-[14.5px] font-bold transition-all duration-300"
        >
          {{ cat }}
        </button>
      </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="max-w-[1240px] mx-auto px-6 md:px-10 lg:px-16 py-12 md:py-16 lg:py-20 w-full flex-grow">
      
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"></div>
        <p class="text-gray-500 font-medium tracking-widest uppercase text-xs">Menyelaraskan Berita...</p>
      </div>

      <!-- Top Section: Featured & Latest -->
      <div v-else-if="featuredPost" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 mb-16 md:mb-24">
        
        <!-- Featured Post (Kiri) -->
        <div @click="goToBlog(featuredPost.berita_id)" class="lg:col-span-8 cursor-pointer group gsap-fade-up">
          <div class="relative rounded-[1.5rem] overflow-hidden aspect-[16/10] shadow-sm bg-gray-200">
            <img :src="featuredPost.thumbnail ? `${axios.defaults.baseURL}/storage/${featuredPost.thumbnail}` : 'https://placehold.co/1200x800/f3f4f6/94a3b8?text=No+Image'" :alt="featuredPost.judul" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            
            <!-- Category Badge Kiri Atas Melayang -->
            <div class="absolute top-5 left-5 md:top-6 md:left-6 z-20">
              <span class="bg-[#4A7043] text-white px-3.5 py-1.5 rounded-full shadow-md text-[11px] md:text-xs font-bold tracking-widest uppercase">
                {{ featuredPost.kategori }}
              </span>
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            
            <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full z-10">
              <h2 class="text-[22px] md:text-3xl lg:text-[32px] font-bold text-[#F5F5F0] leading-snug lg:leading-tight mb-3 group-hover:text-white transition-colors">
                {{ featuredPost.judul }}
              </h2>
              <div class="flex items-center text-[#F5F5F0]/80 text-[12.5px] md:text-sm font-medium tracking-wide gap-2">
                <span class="font-bold text-white">{{ featuredPost.petugas?.nama || 'Petugas' }}</span> 
                <span>&bull;</span>
                <span>{{ dayjs(featuredPost.created_at).format('D MMM YYYY') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Posts (Kanan) -->
        <div class="lg:col-span-4 flex flex-col gsap-fade-up">
          <h3 class="text-[20px] md:text-[22px] font-black text-[#111111] mb-6 md:mb-8 tracking-wide border-b-4 border-[#4A7043] pb-2 inline-block w-fit uppercase">
            Artikel Terbaru
          </h3>
          <div class="flex flex-col">
            <div v-for="(post, index) in latestPosts" :key="index" @click="goToBlog(post.berita_id)" 
              class="flex gap-4 items-start cursor-pointer group py-5 border-b border-gray-200 last:border-0 hover:bg-white/50 transition-colors rounded-lg px-2 -mx-2">
              <div class="w-[85px] h-[85px] flex-shrink-0 rounded-lg overflow-hidden bg-gray-200 shadow-sm">
                <img :src="post.thumbnail ? `${axios.defaults.baseURL}/storage/${post.thumbnail}` : 'https://placehold.co/200x200/f3f4f6/94a3b8?text=No+Image'" :alt="post.judul" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
              </div>
              <div class="flex flex-col flex-grow min-w-0">
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="w-5 h-5 bg-[#4A7043] text-white flex items-center justify-center rounded-sm text-[10px] font-bold">B</span>
                  <span class="text-[#4A7043] font-bold text-[11px] uppercase tracking-wider">{{ post.kategori }}</span>
                </div>
                <h4 class="text-[#111111] font-bold text-[15px] md:text-[16px] leading-[1.3] mb-2 group-hover:text-[#4A7043] transition-colors line-clamp-3">
                  {{ post.judul }}
                </h4>
                <div class="flex items-center text-[#777777] text-[12px] font-medium gap-2">
                  <span class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ dayjs(post.created_at).fromNow() }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>

      <div v-else class="text-center py-20">
        <p class="text-gray-500 font-bold">Belum ada berita yang tersedia.</p>
      </div>

      <!-- Pemisah / Divider -->
      <hr class="border-t border-[#CCCCCC] mb-12 md:mb-16" />

      <!-- Artikel Terkait Section -->
      <div v-if="relatedPosts.length > 0" class="mb-4">
        <div class="flex justify-between items-center mb-8 md:mb-10 gsap-fade-up">
          <h3 class="text-2xl md:text-[28px] font-bold text-[#555555] tracking-tight">Artikel Terkait</h3>
          
          <!-- Arrows Navigation -->
          <div class="flex gap-3">
            <button @click="prevRelated" :disabled="relatedPage === 1" class="w-9 h-9 md:w-10 md:h-10 rounded-full border border-[#CCCCCC] flex items-center justify-center hover:border-[#4A7043] hover:text-[#4A7043] transition-colors bg-white shadow-sm disabled:opacity-40 disabled:hover:border-[#CCCCCC] disabled:hover:text-[#555555]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </button>
            <button @click="nextRelated" :disabled="relatedPage === maxRelatedPage" class="w-9 h-9 md:w-10 md:h-10 rounded-full border border-[#CCCCCC] flex items-center justify-center hover:border-[#4A7043] hover:text-[#4A7043] transition-colors bg-white shadow-sm disabled:opacity-40 disabled:hover:border-[#CCCCCC] disabled:hover:text-[#555555]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-10 gsap-stagger-parent">
          <div v-for="item in relatedPosts" :key="item.berita_id" @click="goToBlog(item.berita_id)" class="flex flex-col cursor-pointer group gsap-stagger-item">
            <div class="w-full aspect-[16/10] rounded-[1.25rem] overflow-hidden bg-gray-200 mb-5 md:mb-6 shadow-sm relative">
              <img :src="item.thumbnail ? `${axios.defaults.baseURL}/storage/${item.thumbnail}` : 'https://placehold.co/600x400/f3f4f6/94a3b8?text=No+Image'" :alt="item.judul" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
              <!-- Category Badge Kiri Atas Melayang -->
              <div class="absolute top-4 left-4 z-10">
                <span class="bg-[#4A7043] text-white px-3 py-1 rounded-full shadow-md text-[10px] md:text-[11px] font-bold uppercase tracking-widest">
                  {{ item.kategori }}
                </span>
              </div>
            </div>
            
            <div class="flex flex-col flex-grow">
              <h4 class="text-[#555555] text-[18px] md:text-[19px] font-bold mb-3 leading-snug group-hover:text-[#4A7043] transition-colors">
                {{ item.judul }}
              </h4>
              <p class="text-[#777777] text-[13.5px] md:text-[14px] leading-relaxed mb-4 line-clamp-3">
                {{ item.isi.replace(/[#*`_]/g, '') }}
              </p>
              <div class="flex items-center text-[#777777] text-[11px] md:text-[12px] tracking-wide mt-auto font-medium gap-1.5">
                <span class="font-bold border-b border-[#CCCCCC] pb-0.5">{{ item.petugas?.nama || 'Petugas' }}</span>
                <span>&bull;</span>
                <span>{{ dayjs(item.created_at).format('D MMM YYYY') }}</span>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Pagination Footer System -->
      <div v-if="lastPage > 1" class="flex justify-between items-center pt-8 border-t border-[#CCCCCC] mt-12 md:mt-16 gsap-fade-up">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="text-[#777777] hover:text-[#4A7043] p-2 transition-colors disabled:opacity-30"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        
        <div class="flex items-center gap-1.5 md:gap-2">
          <button 
             v-for="page in lastPage" :key="page"
             @click="goToPage(page)"
             :class="currentPage === page ? 'bg-[#555555] text-[#F5F5F0] shadow-sm transform scale-110' : 'text-[#777777] hover:bg-white hover:text-[#4A7043]'"
             class="w-[32px] h-[32px] md:w-9 md:h-9 rounded-full text-[13px] md:text-[14.5px] font-bold transition-all duration-300"
          >
            {{ page }}
          </button>
        </div>

        <button 
          @click="nextPage"
          :disabled="currentPage === lastPage"
          class="text-[#777777] hover:text-[#4A7043] p-2 transition-colors disabled:opacity-30"
        >
           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
      </div>


    </div>
  </div>
</template>

<style scoped>
/* Menyembunyikan Scrollbar default di browser Chromium dan Firefox pada navigation pill */
.custom-scrollbar::-webkit-scrollbar {
  display: none;
}
.custom-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}
</style>
