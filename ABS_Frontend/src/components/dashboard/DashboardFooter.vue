<script setup>
import { Icon } from "@iconify/vue";
</script>

<template>
  <footer class="bg-[#4A7043] text-white py-12 px-6 lg:px-20 mt-auto">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center md:items-start justify-between gap-10">
      <!-- Logo and Info -->
      <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-16 w-full">
        <div class="font-black text-6xl tracking-tighter shrink-0">LOGO</div>
        
        <!-- Divider (Vertical on desktop) -->
        <div class="hidden md:block w-px h-32 bg-white/30"></div>
        
        <div class="flex flex-col gap-4 text-center md:text-left text-sm text-white/80">
          <p class="leading-relaxed max-w-xs">
            Jl. Surakarta No. 09, Surakarta, Jawa Tengah
          </p>
          <div class="space-y-2">
            <p class="flex items-center justify-center md:justify-start gap-2">
              <Icon icon="material-symbols:call" class="w-4 h-4" /> 085898212347
            </p>
            <p class="flex items-center justify-center md:justify-start gap-2">
              <Icon icon="material-symbols:mail" class="w-4 h-4" /> abs@gmail.com
            </p>
          </div>
        </div>
      </div>

      <!-- Social Icons -->
      <div class="flex gap-4 shrink-0">
        <Icon icon="ri:facebook-circle-fill" class="w-10 h-10 cursor-pointer hover:scale-110 transition-transform" />
        <Icon icon="ri:twitter-fill" class="w-10 h-10 cursor-pointer hover:scale-110 transition-transform" />
        <Icon icon="ri:twitter-x-line" class="w-10 h-10 cursor-pointer hover:scale-110 transition-transform" />
        <Icon icon="ri:youtube-fill" class="w-10 h-10 cursor-pointer hover:scale-110 transition-transform" />
      </div>
    </div>

    <!-- Copyright -->
    <div class="max-w-7xl mx-auto mt-12 pt-6 border-t border-white/10 text-center md:text-left">
      <p class="text-[10px] text-white/60 tracking-wider">
        © 2026 APLIKASI BANK SAMPAH. ALL RIGHTS RESERVED.
      </p>
    </div>
  </footer>
</template>
