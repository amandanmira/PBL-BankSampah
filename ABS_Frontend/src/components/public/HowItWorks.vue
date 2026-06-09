<script setup>
import { ref, computed } from 'vue';

const activeMode = ref('nasabah'); // 'nasabah' or 'pengepul'

// Mock Data for "Untuk Nasabah"
const nasabahSteps = [
  {
    title: 'Cek Katalog & Harga',
    desc: 'Daftar akunmu dan pantau katalog sampah yang tersedia. Harga per kilogram selalu transparan dan terupdate langsung di aplikasi.',
    image: '/untuk-nasabah/untuk_nasabah1.webp'
  },
  {
    title: 'Pilih Metode Setor',
    desc: 'Sesuaikan dengan waktumu. Buat jadwal request jemput dari rumah, atau pilih opsi setor manual ke gudang terdekat dari lokasimu.',
    image: '/untuk-nasabah/untuk_nasabah2.webp'
  },
  {
    title: 'Proses Penimbangan',
    desc: 'Serahkan sampahmu ke petugas atau kurir kami. Sampah akan ditimbang secara presisi dan nilainya langsung dikonversi.',
    image: '/untuk-nasabah/untuk_nasabah3.webp'
  },
  {
    title: 'Terima & Tarik Saldo',
    desc: 'Saldo Rupiah akan otomatis masuk ke akunmu setelah proses selesai. Pantau riwayat dan ajukan penarikan ke rekening bank kapan saja.',
    image: '/untuk-nasabah/untuk_nasabah4.webp'
  }
];

// Mock Data for "Untuk Pengepul"
const pengepulSteps = [
  {
    title: 'Registrasi & Validasi',
    desc: 'Buat akun khusus mitra pengepul. Tim admin kami akan memvalidasi pendaftaranmu untuk memastikan keamanan dan kelancaran transaksi.',
    image: '/untuk-pengepul/untuk_pengepul1.webp'
  },
  {
    title: 'Cari & Pesan Sampah',
    desc: 'Gunakan filter pintar untuk mencari ketersediaan jenis sampah di berbagai gudang. Masukkan ke keranjang dan atur pesananmu.',
    image: '/untuk-pengepul/untuk_pengepul2.webp'
  },
  {
    title: 'Bayar & Unggah Bukti',
    desc: 'Lakukan checkout dan bayar tagihan di luar sistem sebelum batas waktu habis. Unggah bukti transfer agar pesanan segera diproses petugas.',
    image: '/untuk-pengepul/untuk_pengepul3.webp'
  },
  {
    title: 'Ambil Pesanan',
    desc: 'Setelah pembayaran divalidasi, pantau status pesananmu. Datang ke gudang yang dipilih untuk mengambil stok sampah yang sudah disiapkan.',
    image: '/untuk-pengepul/untuk_pengepul4.webp'
  }
];

// Computed variable that switches automatically
const currentSteps = computed(() => {
  return activeMode.value === 'nasabah' ? nasabahSteps : pengepulSteps;
});
</script>

<template>
  <section class="py-16 md:py-24 bg-[#FAFFF9] overflow-hidden">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">

      <!-- Top Header Row -->
      <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-20 md:mb-32">
        <h2
          class="text-3xl md:text-4xl lg:text-[40px] font-extrabold text-[#555555] tracking-tight text-center md:text-left">
          Cara Kerja
        </h2>

        <!-- Custom Pill Toggle Switch -->
        <div class="inline-flex items-center border-[1.5px] border-[#999999] p-1 rounded-full bg-transparent">
          <button @click="activeMode = 'nasabah'" :class="activeMode === 'nasabah'
            ? 'bg-[#EAEAEA] text-[#555555] font-bold shadow-sm border border-gray-200/50'
            : 'bg-transparent text-[#777777] font-semibold hover:text-[#555555]'"
            class="px-5 py-2 md:px-7 md:py-2.5 rounded-full text-sm md:text-base transition-all duration-300 cursor-pointer">
            Daftar Nasabah
          </button>
          <button @click="activeMode = 'pengepul'" :class="activeMode === 'pengepul'
            ? 'bg-[#EAEAEA] text-[#555555] font-bold shadow-sm border border-gray-200/50'
            : 'bg-transparent text-[#777777] font-semibold hover:text-[#555555]'"
            class="px-5 py-2 md:px-7 md:py-2.5 rounded-full text-sm md:text-base transition-all duration-300 cursor-pointer">
            Daftar Pengepul
          </button>
        </div>
      </div>

      <!-- Logic Container For Alternating Steps layout -->
      <div class="flex flex-col gap-16 md:gap-28 relative">
        <!-- Abstract visual connecting line spanning vertical space on desktop -->
        <div class="hidden md:block absolute left-1/2 top-10 bottom-10 w-px bg-gray-200 -translate-x-1/2 -z-10"></div>

        <div v-for="(step, index) in currentSteps" :key="step.title"
          class="gsap-fade-up flex flex-col md:flex-row items-center gap-10 lg:gap-20 w-full">
          <!-- Text Box -->
          <!-- Using mobile-first unified order-1, and md logic toggles to match zig-zag reading behavior -->
          <div class="flex-1 w-full order-1"
            :class="index % 2 !== 0 ? 'md:order-2 md:pl-6 lg:pl-10 text-center md:text-left' : 'md:order-1 md:pr-6 lg:pr-10 text-center md:text-left'">
            <!-- Step Badge Number -->
            <div
              class="w-12 h-12 md:w-14 md:h-14 rounded-full border-[2.5px] border-[#4A7043] flex items-center justify-center text-[#4A7043] text-xl md:text-2xl font-bold mb-6 md:mb-8 mx-auto md:mx-0 shadow-sm">
              {{ index + 1 }}
            </div>

            <h3 class="text-[#4A7043] text-2xl md:text-[26px] lg:text-3xl font-bold mb-4 md:mb-5 leading-snug">
              {{ step.title }}
            </h3>

            <p class="text-[#555555] text-[15px] md:text-base leading-relaxed xl:max-w-md mx-auto md:mx-0">
              {{ step.desc }}
            </p>
          </div>

          <!-- Image Box -->
          <!-- Odd index defaults to top box mobile, Desktop handles zigzag order natively via md order hooks -->
          <div class="flex-1 w-full order-2 flex justify-center"
            :class="index % 2 !== 0 ? 'md:order-1 md:justify-end' : 'md:order-2 md:justify-start'">
            <img :src="step.image" :alt="'Langkah ' + (index + 1) + ' - ' + step.title"
                 class="w-full max-w-sm md:max-w-md h-auto object-contain rounded-2xl shadow-xl transition-transform duration-500 hover:scale-[1.02] border-4 border-white bg-white" />
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<style scoped></style>