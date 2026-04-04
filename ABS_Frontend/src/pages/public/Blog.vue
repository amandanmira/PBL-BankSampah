<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const goToBlog = (slug) => {
  if(slug) router.push('/blog/' + slug);
};

// Kategori Filter
const categories = ["Semua", "Edukasi", "Sosialisasi", "Berita"];
const activeCategory = ref("Semua");
const currentPage = ref(1);

const selectCategory = (kat) => {
  activeCategory.value = kat;
  currentPage.value = 1; // Reset halaman ke 1 setiap kali ganti filter
}

const nextPage = () => {
  if (currentPage.value < 5) currentPage.value++;
}
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
}
const goToPage = (page) => {
  currentPage.value = page;
}

// Data Dummy Utama (Semua sudah relevan dengan Bank Sampah)
const featuredPost = ref({
  title: "Meningkatkan Partisipasi Warga: Bagaimana Aplikasi Bank Sampah Merubah Pola Pikir",
  slug: "meningkatkan-partisipasi-warga",
  category: "Program",
  color: "#925733",
  date: "10 Agt",
  readTime: "10 min read",
  author: "Siti Nurhaliza",
  image: "/sample.jpg"
});

const latestPosts = ref([
  { 
    title: "Mengenal Jenis-Jenis Sampah Plastik dan Cara Mendaur Ulangnya", 
    slug: "mengenal-jenis-plastik",
    date: "12 Agt", 
    category: "Edukasi",
    readTime: "8 min read", 
    author: "Budi Santoso",
    image: "/sample.jpg" 
  },
  { 
    title: "Kolaborasi dengan Pemulung Lokal: Membangun Ekosistem Berkelanjutan", 
    slug: "kolaborasi-pemulung-lokal",
    date: "15 Agt", 
    category: "Kemitraan",
    readTime: "6 min read", 
    author: "Rina Wijaya",
    image: "/sample.jpg" 
  },
  { 
    title: "Teknologi IoT pada Timbangan Sampah Digital di Bank Sampah ABS", 
    slug: "iot-timbangan-sampah",
    date: "18 Agt", 
    category: "Teknologi",
    readTime: "12 min read", 
    author: "Ahmad Hakim",
    image: "/sample.jpg" 
  },
  { 
    title: "Kampanye 'Satu Rumah Satu Karung': Target Pengurangan Emisi Karbon 2026", 
    slug: "kampanye-satu-rumah",
    date: "20 Agt", 
    category: "Kampanye",
    readTime: "5 min read", 
    author: "Siti Nurhaliza",
    image: "/sample.jpg" 
  },
]);

// Artikel Terkait (Dulu Founders Corner)
const relatedPage = ref(1);
const allRelatedPosts = ref([
  { 
    title: "Pencapaian: 100 Ton Sampah Berhasil Dicegah Menuju TPA Bulan Ini", 
    slug: "pencapaian-100-ton",
    category: "Pencapaian",
    excerpt: "Berkat kerja keras para nasabah dan pengepul, bulan ini kita memecahkan rekor pengumpulan material anorganik terbanyak sepanjang sejarah operasional.",
    date: "22 Agt", 
    readTime: "7 min read", 
    author: "Rina Wijaya",
    image: "/sample.jpg" 
  },
  { 
    title: "Pentingnya Mengedukasi Anak Usia Dini Tentang Pemilahan Sampah", 
    slug: "edukasi-anak-usia-dini",
    category: "Edukasi",
    excerpt: "Memperkenalkan konsep daur ulang sejak dini adalah investasi terbaik bagi lingkungan. Bank Sampah ABS baru saja merilis modul edukasi untuk SD.",
    date: "24 Agt", 
    readTime: "9 min read", 
    author: "Siti Nurhaliza",
    image: "/sample.jpg" 
  },
  { 
    title: "Masa Depan Sirkular Ekonomi Melalui Sistem Tabungan Digital Terintegrasi", 
    slug: "ekonomi-sirkular-digital",
    category: "Teknologi",
    excerpt: "Dengan aplikasi terbaru kita, nasabah kini dapat melihat riwayat harga komposit harian. Kepercayaan dan transparansi adalah fondasi layanan kami.",
    date: "25 Agt", 
    readTime: "8 min read", 
    author: "Ahmad Hakim",
    image: "/sample.jpg" 
  },
  { 
    title: "Kolaborasi Strategis Bersama Startup Hijau Global", 
    slug: "kolaborasi-startup-hijau",
    category: "Kemitraan",
    excerpt: "Langkah konkrit kami mencocokkan teknologi terbaru untuk efisiensi distribusi sampah kembali ke industri sirkuler secara lebih transparan.",
    date: "28 Agt", 
    readTime: "5 min read", 
    author: "Budi Santoso",
    image: "/sample.jpg" 
  },
  { 
    title: "Tips Mengurangi Limbah Makanan Harian Keluarga Anda", 
    slug: "tips-limbah-makanan",
    category: "Edukasi",
    excerpt: "Hal simpel yang bisa dilakukan mulai dari meja makan berpotensi menyelamatkan ribuan ton gas metana penyebab efek rumah kaca tiap tahunnya.",
    date: "01 Sep", 
    readTime: "11 min read", 
    author: "Rina Wijaya",
    image: "/sample.jpg" 
  },
  { 
    title: "Evaluasi Tahunan Kebijakan Tata Kelola Hijau Bank Sampah", 
    slug: "evaluasi-tahunan-tata-kelola",
    category: "Berita",
    excerpt: "Laporan transparansi kami kepada seluruh nasabah mengenai pembukuan dampak karbon serta pendanaan program pengelolaan selama periode tahun ini.",
    date: "05 Sep", 
    readTime: "6 min read", 
    author: "Pimpinan ABS",
    image: "/sample.jpg" 
  },
]);

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
      
      <!-- Top Section: Featured & Latest -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 mb-16 md:mb-24">
        
        <!-- Featured Post (Kiri) -->
        <div @click="goToBlog(featuredPost.slug)" class="lg:col-span-8 cursor-pointer group gsap-fade-up">
          <div class="relative rounded-[1.5rem] overflow-hidden aspect-[16/10] shadow-sm bg-gray-200">
            <img :src="featuredPost.image" :alt="featuredPost.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            
            <!-- Category Badge Kiri Atas Melayang -->
            <div class="absolute top-5 left-5 md:top-6 md:left-6 z-20">
              <span class="bg-[#4A7043] text-white px-3.5 py-1.5 rounded-full shadow-md text-[11px] md:text-xs font-bold tracking-widest uppercase">
                {{ featuredPost.category }}
              </span>
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            
            <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full z-10">
              <h2 class="text-[22px] md:text-3xl lg:text-[32px] font-bold text-[#F5F5F0] leading-snug lg:leading-tight mb-3 group-hover:text-white transition-colors">
                {{ featuredPost.title }}
              </h2>
              <div class="flex items-center text-[#F5F5F0]/80 text-[12.5px] md:text-sm font-medium tracking-wide gap-2">
                <span class="font-bold text-white">{{ featuredPost.author }}</span> 
                <span>&bull;</span>
                <span>{{ featuredPost.date }}</span>
                <span>&bull;</span>
                <span>{{ featuredPost.readTime }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Posts (Kanan) -->
        <div class="lg:col-span-4 flex flex-col gsap-fade-up">
          <h3 class="text-[22px] md:text-2xl font-bold text-[#555555] mb-6 md:mb-7 tracking-tight">Postingan Terbaru</h3>
          <div class="flex flex-col gap-6 md:gap-7">
            <div v-for="(post, index) in latestPosts" :key="index" @click="goToBlog(post.slug)" class="flex gap-4 items-center cursor-pointer group">
              <div class="w-[84px] h-[84px] md:w-24 md:h-24 flex-shrink-0 rounded-[14px] overflow-hidden bg-gray-200 shadow-sm relative">
                <img :src="post.image" :alt="post.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
              </div>
              <div class="flex flex-col pr-2">
                <span class="text-[#4A7043] font-bold text-[10px] md:text-[11px] uppercase tracking-wider mb-1">{{ post.category }}</span>
                <h4 class="text-[#555555] font-bold text-[14.5px] md:text-[15px] leading-[1.35] mb-2 group-hover:text-[#4A7043] transition-colors line-clamp-2">
                  {{ post.title }}
                </h4>
                <div class="flex flex-wrap items-center text-[#777777] text-[11px] md:text-[12px] tracking-wide font-medium gap-1.5">
                  <span class="font-bold">{{ post.author }}</span>
                  <span>&bull;</span>
                  <span>{{ post.date }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Pemisah / Divider -->
      <hr class="border-t border-[#CCCCCC] mb-12 md:mb-16" />

      <!-- Artikel Terkait Section (Pewaris Founders Corner) -->
      <div class="mb-4">
        <div class="flex justify-between items-center mb-8 md:mb-10 gsap-fade-up">
          <h3 class="text-2xl md:text-[28px] font-bold text-[#555555] tracking-tight">Artikel Terkait</h3>
          
          <!-- Arrows Navigation (Selanjutnya / Sebelumnya) -->
          <div class="flex gap-3">
            <button @click="prevRelated" :disabled="relatedPage === 1" class="w-9 h-9 md:w-10 md:h-10 rounded-full border border-[#CCCCCC] flex items-center justify-center hover:border-[#4A7043] hover:text-[#4A7043] transition-colors bg-white shadow-sm disabled:opacity-40 disabled:hover:border-[#CCCCCC] disabled:hover:text-[#555555]" title="Geser Sebelumnya">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </button>
            <button @click="nextRelated" :disabled="relatedPage === maxRelatedPage" class="w-9 h-9 md:w-10 md:h-10 rounded-full border border-[#CCCCCC] flex items-center justify-center hover:border-[#4A7043] hover:text-[#4A7043] transition-colors bg-white shadow-sm disabled:opacity-40 disabled:hover:border-[#CCCCCC] disabled:hover:text-[#555555]" title="Berita Selanjutnya">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-10 gsap-stagger-parent">
          <div v-for="(item, idx) in relatedPosts" :key="item.title" @click="goToBlog(item.slug)" class="flex flex-col cursor-pointer group gsap-stagger-item">
            <div class="w-full aspect-[16/10] rounded-[1.25rem] overflow-hidden bg-gray-200 mb-5 md:mb-6 shadow-sm relative">
              <img :src="item.image" :alt="item.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
              <!-- Category Badge Kiri Atas Melayang -->
              <div class="absolute top-4 left-4 z-10">
                <span class="bg-[#4A7043] text-white px-3 py-1 rounded-full shadow-md text-[10px] md:text-[11px] font-bold uppercase tracking-widest">
                  {{ item.category }}
                </span>
              </div>
            </div>
            
            <div class="flex flex-col flex-grow">
              <h4 class="text-[#555555] text-[18px] md:text-[19px] font-bold mb-3 leading-snug group-hover:text-[#4A7043] transition-colors">
                {{ item.title }}
              </h4>
              <p class="text-[#777777] text-[13.5px] md:text-[14px] leading-relaxed mb-4 line-clamp-3">
                {{ item.excerpt }}
              </p>
              <div class="flex items-center text-[#777777] text-[11px] md:text-[12px] tracking-wide mt-auto font-medium gap-1.5">
                <span class="font-bold border-b border-[#CCCCCC] pb-0.5">{{ item.author }}</span>
                <span>&bull;</span>
                <span>{{ item.date }}</span>
                <span>&bull;</span>
                <span>{{ item.readTime }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Footer System Terhubung State -->
      <div class="flex justify-between items-center pt-8 border-t border-[#CCCCCC] mt-12 md:mt-16 gsap-fade-up">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="text-[#777777] hover:text-[#4A7043] p-2 transition-colors disabled:opacity-30 disabled:hover:text-[#777777]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        
        <div class="flex items-center gap-1.5 md:gap-2">
          <!-- Halaman Angka Looping Berdasarkan State -->
          <button 
             v-for="page in 5" :key="page"
             @click="goToPage(page)"
             :class="currentPage === page ? 'bg-[#555555] text-[#F5F5F0] shadow-sm transform scale-110' : 'text-[#777777] hover:bg-white hover:text-[#4A7043]'"
             class="w-[32px] h-[32px] md:w-9 md:h-9 rounded-full text-[13px] md:text-[14.5px] font-bold transition-all duration-300"
          >
            {{ page }}
          </button>
        </div>

        <button 
          @click="nextPage"
          :disabled="currentPage === 5"
          class="text-[#777777] hover:text-[#4A7043] p-2 transition-colors disabled:opacity-30 disabled:hover:text-[#777777]"
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
