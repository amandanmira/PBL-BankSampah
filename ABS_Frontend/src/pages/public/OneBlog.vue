<script setup>
import { useRoute } from 'vue-router'
import { ref, onMounted } from 'vue'

const route = useRoute()
const slug = route.params.slug

// Dummy data simulasi: biasanya kita fetch API pake parameter `slug` ini 
const article = ref({
  title: "Program Daur Ulang Tingkatkan Partisipasi Warga",
  category: "Program",
  author: {
    name: "Siti Nurhaliza",
    role: "Program Manager"
  },
  date: "8 Maret 2026",
  readTime: "10 min read",
  image: "/sample.jpg",
  subtitle: "Inisiatif baru kami berhasil meningkatkan partisipasi masyarakat dalam daur ulang hingga 45% dalam 3 bulan terakhir.",
  content: `
    <p class="mb-5">Program daur ulang yang kami luncurkan tiga bulan lalu telah menunjukkan hasil yang sangat menggembirakan. Partisipasi masyarakat dalam kegiatan daur ulang meningkat drastis hingga 45%, melampaui target awal kami sebesar 30%.</p>
    <p class="mb-5">Keberhasilan ini tidak lepas dari berbagai inovasi yang kami terapkan, termasuk sistem poin reward yang menarik, edukasi rutin ke rumah-rumah warga, dan kemudahan akses melalui aplikasi mobile yang user-friendly.<br/><br/>Warga sangat antusias dengan program ini karena mereka bisa langsung merasakan manfaatnya. Selain berkontribusi untuk lingkungan, mereka juga mendapatkan poin yang bisa ditukar dengan berbagai hadiah menarik seperti voucher belanja, pulsa, dan produk ramah lingkungan.</p>
    <p class="mb-5">Kami juga bekerja sama dengan RT/RW setempat untuk membentuk bank sampah di setiap kelurahan. Hal ini memudahkan warga untuk menyetorkan sampah yang sudah dipilah tanpa harus pergi jauh dari rumah mereka.<br/><br/>Kedepannya, kami akan terus mengembangkan program ini dengan menambah lebih banyak titik pengumpulan dan jenis sampah yang bisa didaur ulang. Target kami adalah mencapai 70% partisipasi warga pada akhir tahun ini.<br/><br/>Kami mengajak seluruh masyarakat untuk terus mendukung program ini. Setiap kontribusi kecil dari kita akan memberikan dampak besar bagi kelestarian lingkungan untuk generasi mendatang.</p>
  `,
  tags: ["Program", "Lingkungan", "Daur Ulang", "Sustainability"]
});

onMounted(() => {
  // Disini nanti ditaruh fungsi axios fetch API ya! 
  window.scrollTo(0, 0); // scroll ke atas pas masuk halaman
})
</script>

<template>
  <div class="bg-[#F5F5F0] min-h-screen py-10 md:py-16 font-sans">
    <div class="max-w-4xl mx-auto px-6 md:px-12">
      
      <!-- Breadcrumb -->
      <nav class="text-[12px] md:text-[13px] font-bold text-[#777777] uppercase tracking-widest mb-8">
        Beranda <span class="mx-2 font-normal">/</span> Berita <span class="mx-2 font-normal">/</span> <span class="text-[#555555]">{{ article.category }}</span>
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
        <span class="bg-[#925733] text-white px-4 py-1.5 rounded-full text-[11px] md:text-xs font-bold uppercase tracking-widest shadow-sm">
          {{ article.category }}
        </span>
      </div>

      <!-- Title -->
      <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-[#111111] leading-snug lg:leading-tight mb-8 tracking-tight">
        {{ article.title }}
      </h1>

      <!-- Author and Meta -->
      <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-[#CCCCCC]/80 pb-6 mb-10 gap-6">
        <div class="flex items-center gap-4">
          <!-- Avatar Custom Budi/Siti -->
          <div class="w-[46px] h-[46px] md:w-12 md:h-12 rounded-full bg-[#4A7043] text-white flex items-center justify-center font-bold text-lg md:text-xl uppercase shadow-md">
            {{ article.author.name.charAt(0) }}
          </div>
          <div class="flex flex-col">
            <h4 class="font-bold text-[#555555] text-[14.5px] leading-snug mb-1">{{ article.author.name }}</h4>
            <div class="flex items-center text-[12px] md:text-[12.5px] font-semibold text-[#777777] gap-2 md:gap-3">
              <span>{{ article.author.role }}</span>
              <span class="hidden md:inline">&bull;</span>
              <span class="flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> {{ article.date }}</span>
              <span class="hidden md:inline">&bull;</span>
              <span class="flex items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ article.readTime }}</span>
            </div>
          </div>
        </div>

        <div class="flex gap-4 text-[#777777]">
          <button class="hover:text-[#4A7043] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-[22px] w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg></button>
          <button class="hover:text-[#4A7043] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-[22px] w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg></button>
        </div>
      </div>

      <!-- Main Valid Image Banner -->
      <div class="w-full aspect-video md:aspect-[21/10] bg-gray-200 rounded-[1.25rem] md:rounded-[1.5rem] overflow-hidden mb-12 shadow-md relative">
        <img :src="article.image" :alt="article.title" class="absolute inset-0 w-full h-full object-cover" />
      </div>

      <!-- Subtitle -->
      <p class="text-lg md:text-xl lg:text-[22px] text-[#555555] font-semibold leading-relaxed mb-8 tracking-tight">
        {{ article.subtitle }}
      </p>

      <!-- Content text Injection -->
      <div class="prose prose-lg max-w-none text-[#555555] leading-relaxed mb-16 text-[15px] md:text-[16.5px]" v-html="article.content">
      </div>

      <!-- Tags Pills -->
      <div class="flex flex-wrap items-center gap-3 mb-16">
        <span class="text-[12px] font-extrabold text-[#777777] uppercase tracking-widest mr-2">Tags:</span>
        <button v-for="tag in article.tags" :key="tag" class="bg-white border border-[#E0E0E0] rounded-full px-4 py-1.5 text-[12.5px] font-bold text-[#555555] hover:border-[#4A7043] hover:text-[#4A7043] transition-colors shadow-sm">
          {{ tag }}
        </button>
      </div>

      <!-- Share Block Bawah -->
      <div class="bg-gradient-to-br from-[#4A7043] to-[#365230] rounded-[1.5rem] p-8 md:p-10 shadow-lg text-white">
        <h3 class="text-2xl font-bold mb-2">Bagikan artikel ini</h3>
        <p class="text-white/80 font-medium text-[14.5px] mb-8">Bantu sebarkan informasi ini kepada teman dan keluarga Anda.</p>
        
        <div class="flex flex-wrap gap-4">
          <button class="bg-white/10 hover:bg-white/20 border border-white/20 px-5 py-2.5 rounded-lg text-[13px] font-bold transition-all shadow-sm">Facebook</button>
          <button class="bg-white/10 hover:bg-white/20 border border-white/20 px-5 py-2.5 rounded-lg text-[13px] font-bold transition-all shadow-sm">Twitter</button>
          <button class="bg-white/10 hover:bg-white/20 border border-white/20 px-5 py-2.5 rounded-lg text-[13px] font-bold transition-all shadow-sm">WhatsApp</button>
          <button class="bg-white/10 hover:bg-white/20 border border-white/20 px-5 py-2.5 rounded-lg text-[13px] font-bold transition-all shadow-sm">LinkedIn</button>
          <button class="bg-[#F5F5F0] text-[#4A7043] hover:bg-white border px-5 py-2.5 rounded-lg text-[13px] font-bold transition-all shadow-sm mr-auto md:mr-0 ml-auto flex items-center gap-2">Copy Link</button>
        </div>
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