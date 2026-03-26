<script setup>
import { ref } from 'vue';

// Data statis (dummy) ini didesain serapi mungkin dalam struktur array of objects.
// Nantinya ketika backend/API sudah siap, Anda cukup mengganti blok array isi 'newsList' ini
// dengan hasil fetch (misal: newsList.value = await axios.get('/api/news')).
const newsList = ref([
  {
    id: 1,
    title: 'Program Daur Ulang Tingkatkan Partisipasi Warga',
    excerpt: 'Inisiatif baru kami berhasil meningkatkan partisipasi masyarakat dalam daur ulang hingga 45% dalam 3 bulan terakhir.',
    date: '8 Maret 2026',
    image: '/sample.jpg',
    slug: 'program-daur-ulang-tingkatkan-partisipasi'
  },
  {
    id: 2,
    title: 'Workshop Edukasi Lingkungan untuk Komunitas',
    excerpt: 'Lebih dari 200 peserta mengikuti workshop gratis tentang cara mengelola sampah rumah tangga dengan efektif.',
    date: '5 Maret 2026',
    image: '/sample.jpg',
    slug: 'workshop-edukasi-lingkungan'
  },
  {
    id: 3,
    title: 'Inovasi Teknologi Pengelolaan Sampah Digital',
    excerpt: 'Aplikasi baru kami memudahkan tracking sampah dan reward poin untuk setiap kontribusi daur ulang Anda.',
    date: '1 Maret 2026',
    image: '/sample.jpg',
    slug: 'inovasi-teknologi-pengelolaan-sampah'
  }
]);
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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

        <article v-for="news in newsList" :key="news.id"
          class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 flex flex-col h-full">
          <!-- Cover Gambar -->
          <div class="w-full aspect-[16/10] overflow-hidden">
            <img :src="news.image" :alt="news.title"
              class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
          </div>

          <!-- Konten Card -->
          <div class="p-6 md:p-8 flex flex-col flex-grow">

            <time class="text-[#888888] text-[13px] font-medium mb-3 block">
              {{ news.date }}
            </time>

            <h3 class="text-[#555555] text-[20px] lg:text-[22px] font-bold leading-snug mb-4">
              {{ news.title }}
            </h3>

            <!-- Teks excerpt dipaksa memanjang mengisi sisa ruang jika ada beda tinggi judul -->
            <p class="text-[#555555] text-[14px] leading-relaxed flex-grow">
              {{ news.excerpt }}
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
          class="bg-[#4A7043] text-white font-semibold text-[15px] px-8 py-3.5 rounded-md hover:bg-[#3d5d36] transition-colors shadow-sm cursor-pointer">
          Lihat Semua Berita
        </RouterLink>
      </div>

    </div>
  </section>
</template>

<style scoped></style>