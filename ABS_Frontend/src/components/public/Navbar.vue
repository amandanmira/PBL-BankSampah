<script setup>
import { ref } from 'vue';

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};
</script>

<template>
  <!-- Menambahkan fitur Sticky dari atas agar navigasi mengikut ke bawah -->
  <nav
    class="bg-[#4A7043] text-white py-4 px-6 md:px-12 lg:px-20 sticky top-0 z-[100] border-b border-[#73A36B] shadow-sm">
    <div class="max-w-screen-2xl mx-auto flex justify-between items-center">

      <!-- Left Section: Hamburger & Logo -->
      <div class="flex items-center gap-4">
        <!-- Mobile Hamburger Button -->
        <button @click="toggleMenu"
          class="lg:hidden text-white focus:outline-none hover:text-white/80 transition-colors">
          <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Logo -->
        <RouterLink to="/" class="text-3xl md:text-4xl font-bold tracking-wide">
          ABS
        </RouterLink>
      </div>

      <!-- Desktop Navigation Links -->
      <div class="hidden lg:flex items-center gap-6 lg:gap-10">
        <RouterLink to="/about" class="text-[15px] font-semibold tracking-wide hover:text-white/80 transition-colors">
          Tentang</RouterLink>
        <RouterLink :to="{ path: '/', hash: '#layanan' }"
          class="text-[15px] font-semibold tracking-wide hover:text-white/80 transition-colors">Layanan</RouterLink>
        <RouterLink :to="{ path: '/', hash: '#cara-kerja' }"
          class="text-[15px] font-semibold tracking-wide hover:text-white/80 transition-colors">Cara Kerja</RouterLink>
        <RouterLink to="/faq" class="text-[15px] font-semibold tracking-wide hover:text-white/80 transition-colors">FAQ
        </RouterLink>
      </div>

      <!-- Right Section: Auth Action -->
      <div class="flex items-center gap-6">
        <RouterLink to="/choose-role"
          class="hidden lg:block text-[15px] font-bold tracking-wide hover:text-white/80 transition-colors">Daftar
        </RouterLink>
        <RouterLink to="/login"
          class="bg-white text-[#4A7043] text-[15px] font-bold tracking-wide px-7 py-2.5 rounded-full hover:bg-gray-50 hover:scale-105 active:scale-95 transition-all shadow-sm">
          Masuk
        </RouterLink>
      </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div v-show="isMenuOpen"
      class="lg:hidden absolute top-full left-0 w-full bg-[#4A7043] shadow-lg flex flex-col px-6 py-5 gap-4 border-t border-white/10">
      <RouterLink to="/about" @click="isMenuOpen = false"
        class="text-base font-semibold tracking-wide hover:text-white/80 transition-colors py-1">Tentang</RouterLink>
      <RouterLink :to="{ path: '/', hash: '#layanan' }" @click="isMenuOpen = false"
        class="text-base font-semibold tracking-wide hover:text-white/80 transition-colors py-1">Layanan</RouterLink>
      <RouterLink :to="{ path: '/', hash: '#cara-kerja' }" @click="isMenuOpen = false"
        class="text-base font-semibold tracking-wide hover:text-white/80 transition-colors py-1">Cara Kerja</RouterLink>
      <RouterLink to="/faq" @click="isMenuOpen = false"
        class="text-base font-semibold tracking-wide hover:text-white/80 transition-colors py-1">FAQ</RouterLink>

      <hr class="border-white/20 my-2" />

      <div class="flex flex-col gap-3">
        <RouterLink to="/choose-role"
          class="text-base font-bold tracking-wide text-center hover:text-white/80 transition-colors py-2">Daftar
        </RouterLink>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Scoped styles omitted in favor of Tailwind CSS per request */
</style>