<script setup>
import { ref } from 'vue';

const activeIndex = ref(0); // Secara otomatis menampilkan jawaban dari pertanyaan pertama

const faqs = ref([
  {
    question: "Bagaimana cara kerja penjemputan sampah di ABS?",
    answer: "Setelah Anda mengumpulkan dan memilah sampah, Anda dapat masuk ke aplikasi ABS dan menjadwalkan penjemputan. Mitra pengepul terdekat kami akan menerima notifikasi, datang ke lokasi penjemputan, menimbang sampah Anda, dan memberikan saldo poin yang masuk secara langsung ke akun Anda."
  },
  {
    question: "Jenis sampah apa saja yang bisa disetorkan?",
    answer: "Kami menerima berbagai bentuk rongsokan dan sampah kering bernilai daur ulang. Ini meliputi botol plastik, kertas, bungkus kemasan, kardus, besi bekas, hingga alat elektronik rusak. Semakin rapi dan terpilah kondisi sampah Anda, semakin tinggi harga komisisnya."
  },
  {
    question: "Apakah dikenakan biaya administrasi atau biaya antar jemput?",
    answer: "Sama sekali tidak. Penggunaan palikasi dan penjemputan oleh tim pengepul bersifat 100% gratis tanpa potongan! Kami justru yang akan membayar Anda berdasarkan berat dan kategori material yang Anda setorkan."
  },
  {
    question: "Bagaimana cara mencairkan saldo poin dari aplikasi?",
    answer: "Setiap transaksi akan menambahkan poin ke saldo utama di akun Anda. Anda bisa kapan pun mencairkannya lewat menu 'Tarik Saldo'. Uang komisi dapat ditarik secara bebas menuju berbagai jalur seperti rekening bank hingga E-Wallet."
  },
  {
    question: "Apa syarat untuk menjadi Mitra Pengepul?",
    answer: "Syaratnya cukup mudah! Pilih 'Daftar sebagai Pengepul' saat proses pendaftaran awal. Daftarkan dan verifikasi data identitas resmi sesuai KTP Anda. Pastikan Anda punya timbangan yang fungsional dan kendaraan angkut untuk mempermudah pengerjaan misi penjemputan di wilayah sekitar."
  }
]);

const toggleFaq = (index) => {
  activeIndex.value = activeIndex.value === index ? null : index;
};
</script>

<template>
  <main class="py-20  w-full min-h-screen">
    <div class="max-w-3xl mx-auto px-6 md:px-12">

      <!-- Judul -->
      <h1
        class="gsap-fade-up text-3xl md:text-4xl lg:text-[40px] font-bold text-center text-[#555555] mb-14 md:mb-20 tracking-tight">
        Pertanyaan yang Sering Diajukan
      </h1>

      <!-- Container FAQ (Sistem Accordion) -->
      <div class="gsap-fade-up text-[#555555]">
        <div v-for="(faq, index) in faqs" :key="index" class="border-b border-[#CCCCCC]/80">
          <!-- Tombol Pertanyaan -->
          <button @click="toggleFaq(index)"
            class="w-full flex justify-between items-center py-6 md:py-7 text-left hover:text-[#4A7043] transition-colors gap-4 cursor-pointer">
            <span class="text-[16px] md:text-lg font-bold" :class="activeIndex === index ? 'text-[#4A7043]' : ''">
              {{ faq.question }}
            </span>

            <!-- Ikon Panah (Chevron) -->
            <span class="transition-transform duration-300 transform flex-shrink-0"
              :class="activeIndex === index ? 'rotate-180 text-[#4A7043]' : 'text-[#888888]'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
              </svg>
            </span>
          </button>

          <!-- Box Jawaban Tersembunyi -->
          <div v-show="activeIndex === index" class="pb-7 overflow-hidden">
            <p class="text-[15px] text-[#666666] leading-relaxed pr-6 md:pr-12 block">
              {{ faq.answer }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </main>
</template>

<style scoped></style>