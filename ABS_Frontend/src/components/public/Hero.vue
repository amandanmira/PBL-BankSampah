<script setup></script>

<template>
  <section
    class="bg-[#4A7043] text-[#F5F5F0] py-16 [@media(min-width:375px)_and_(max-width:768px)]:py-25 md:py-24 lg:py-32 px-6 md:px-12 lg:px-20 overflow-hidden relative">
    <div class="max-w-screen-2xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-8">

      <!-- Text Content -->
      <div class="flex-1 flex flex-col gap-6 lg:gap-8 text-center lg:text-left z-10 w-full pt-8 lg:pt-0">
        <h1 class="text-4xl sm:text-5xl lg:text-[64px] font-extrabold leading-[1.2] lg:leading-[1.1] tracking-wide">
          Kelola Sampah,<br />
          Raih Manfaat
        </h1>

        <p
          class="text-base sm:text-lg lg:text-[22px] font-medium leading-relaxed max-w-xl mx-auto lg:mx-0 text-white/95">
          Kelola Sampah Menjadi Tabungan Digital<br class="hidden lg:block" />
          yang Transparan dan Terintegrasi
        </p>

        <div
          class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 lg:gap-5 mt-4 text-[15px] font-bold tracking-wide">
          <RouterLink to="/register-nasabah"
            class="w-full sm:w-auto bg-[#F5F5F0] text-[#4A7043] px-8 py-3.5 rounded-full hover:bg-gray-50 hover:scale-105 active:scale-95 transition-all shadow-md flex justify-center items-center">
            Jadi Nasabah
          </RouterLink>
          <RouterLink to="/register-pengepul"
            class="w-full sm:w-auto bg-transparent border border-[#F5F5F0] text-[#F5F5F0] px-8 py-3.5 rounded-full hover:bg-white/10 hover:scale-105 active:scale-95 transition-all flex justify-center items-center">
            Jadi Pengepul
          </RouterLink>
        </div>
      </div>

      <!-- Image Content -->
      <div class="flex-1 w-full max-w-lg lg:max-w-none relative z-10 flex justify-center lg:justify-end">
        <img src="/hero.svg" alt="Ilustrasi Kelola Sampah"
          class="w-full h-auto max-h-[400px] md:max-h-[500px] lg:max-h-[600px] object-contain drop-shadow-xl hover:scale-[1.02] transition-transform duration-500" />
      </div>

    </div>
  </section>
</template>

<style scoped></style>