<script setup>
import { RouterLink } from 'vue-router';
// Catatan: Library yang Anda install (@iconify-vue/material-symbols)
// secara resmi DARI GOOGLE memang TIDAK pernah menyertakan logo brand merek dagang
// (seperti Facebook, X, Twitter, YouTube). Jadi saya akan menggunakan SVG mentah (raw SVG)
// yang sudah persis bentuknya agar web Anda tidak error saat dicompile!
</script>

<template>
  <footer class="bg-[#4A7043] text-[#F5F5F0] py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-6 md:px-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">

      <!-- Column 1: Brand Info -->
      <div class="space-y-6">
        <h2 class="text-4xl md:text-[42px] font-extrabold tracking-wide mb-2">ABS</h2>
        <p class="text-[14px] leading-relaxed font-medium max-w-[220px]">
          Ubah Sampahmu Menjadi Berkah dan Bernilai
        </p>
        <div class="pt-4 space-y-1">
          <p class="font-bold text-[14px]">Alamat:</p>
          <p class="text-[14px] leading-relaxed max-w-[220px]">
            Jl. Surakarta No. 09, Surakarta,<br>
            Jawa Tengah
          </p>
        </div>
        <div class="pt-4 space-y-2">
          <p class="font-bold text-[14px]">Kontak:</p>
          <div class="flex items-center gap-2 text-[14px]">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
            </svg>
            <p>085679340192</p>
          </div>
          <div class="flex items-center gap-2 text-[14px]">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
            <p>abs@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Column 2: Navigasi -->
      <div class="lg:pl-8">
        <h3 class="font-bold text-[17px] mb-6 tracking-wide">Navigasi</h3>
        <ul class="space-y-4 text-[15px] font-medium text-[#BBBBBB]">
          <li>
            <RouterLink to="/" class="hover:text-white hover:underline transition-colors">Beranda</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ path: '/', hash: '#layanan' }"
              class="hover:text-white hover:underline transition-colors">Layanan</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ path: '/', hash: '#cara-kerja' }"
              class="hover:text-white hover:underline transition-colors">Cara Kerja</RouterLink>
          </li>
          <li>
            <RouterLink to="/blog" class="hover:text-white hover:underline transition-colors">Blog</RouterLink>
          </li>
        </ul>
      </div>

      <!-- Column 3: Tentang Kami -->
      <div>
        <h3 class="font-bold text-[17px] mb-6 tracking-wide">Tentang Kami</h3>
        <ul class="space-y-4 text-[15px] font-medium text-[#BBBBBB]">
          <li>
            <RouterLink to="/about" class="hover:text-white hover:underline transition-colors">Tentang</RouterLink>
          </li>
          <li>
            <RouterLink to="/terms-and-privacy" class="hover:text-white hover:underline transition-colors">Terms &
              Privacy</RouterLink>
          </li>
          <li>
            <RouterLink to="/faq" class="hover:text-white hover:underline transition-colors">FAQ</RouterLink>
          </li>
        </ul>
      </div>

      <!-- Column 4: Social -->
      <div>
        <h3 class="font-bold text-[17px] mb-6 tracking-wide">Ikuti Kami</h3>
        <div class="flex flex-wrap gap-4">
          <!-- Facebook -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z" />
            </svg>
          </a>

          <!-- Instagram -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" />
            </svg>
          </a>

          <!-- LinkedIn -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M19 3A2 2 0 0 1 21 5V19A2 2 0 0 1 19 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H19M18.5 18.5V13.2A3.26 3.26 0 0 0 15.24 9.94C14 9.94 13.4 10.61 13.06 11.14V10.12H10.66V18.5H13.06V13.83C13.06 12.58 13.3 11.96 14.17 11.96C15 11.96 15.17 12.72 15.17 13.92V18.5H18.5M6.88 8.56A1.68 1.68 0 0 0 8.56 6.88C8.56 5.95 7.81 5.19 6.88 5.19A1.69 1.69 0 0 0 5.19 6.88C5.19 7.81 5.95 8.56 6.88 8.56M8.1 18.5V10.12H5.66V18.5H8.1Z" />
            </svg>
          </a>

          <!-- YouTube -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm pl-[1px]">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
            </svg>
          </a>
        </div>
      </div>

    </div>

    <!-- Copyright -->
    <div class="max-w-6xl mx-auto px-6 md:px-12 mt-16 md:mt-24">
      <div class="border-t border-[#F5F5F0]/30 pt-8 text-center text-[13px] md:text-sm font-light tracking-wider">
        © Copyright ABS 2026
      </div>
    </div>
  </footer>
</template>

<style scoped></style>