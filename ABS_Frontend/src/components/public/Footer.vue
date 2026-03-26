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
      </div>

      <!-- Column 2: Navigasi -->
      <div class="lg:pl-8">
        <h3 class="font-bold text-[17px] mb-6 tracking-wide">Navigasi</h3>
        <ul class="space-y-4 text-[15px] font-medium">
          <li>
            <RouterLink to="/" class="hover:text-white hover:underline transition-colors">Beranda</RouterLink>
          </li>
          <li>
            <RouterLink to="/about" class="hover:text-white hover:underline transition-colors">Tentang</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ path: '/', hash: '#layanan' }"
              class="hover:text-white hover:underline transition-colors">Layanan</RouterLink>
          </li>
          <li>
            <RouterLink to="/blog" class="hover:text-white hover:underline transition-colors">Blog</RouterLink>
          </li>
        </ul>
      </div>

      <!-- Column 3: Tentang Kami -->
      <div>
        <h3 class="font-bold text-[17px] mb-6 tracking-wide">Tentang Kami</h3>
        <ul class="space-y-4 text-[15px] font-medium">
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

          <!-- Twitter (Bird) -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
            </svg>
          </a>

          <!-- X Logo -->
          <a href="#"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
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