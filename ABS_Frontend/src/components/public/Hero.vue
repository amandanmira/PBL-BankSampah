<script setup>
import { ref, onMounted, inject, computed } from 'vue';

const axios = inject('axios');
const config = ref({
  hero_quote_top: "Kelola Sampah, Raih Manfaat",
  hero_quote_bottom: "Kelola Sampah Menjadi Tabungan Digital yang Transparan dan Terintegrasi"
});

const fetchConfig = async () => {
  try {
    const res = await axios.get('/api/web-config'); // Using public endpoint
    if (res.data) {
      // Hanya ambil data dari DB yang tidak null/kosong
      // agar teks default tidak hilang tertimpa null
      const validData = Object.fromEntries(
        Object.entries(res.data).filter(([_, v]) => v !== null && v !== '')
      );

      config.value = {
        ...config.value,
        ...validData
      };
    }
  } catch (err) {
    console.error("Failed to fetch hero config:", err);
  }
};

const headingFontSize = computed(() => {
  const text = config.value.hero_quote_top || "";
  const length = text.length;

  if (length > 60) return "text-2xl sm:text-3xl lg:text-4xl";
  if (length > 40) return "text-3xl sm:text-4xl lg:text-5xl";
  if (length > 25) return "text-4xl sm:text-5xl lg:text-6xl";
  return "text-4xl sm:text-5xl lg:text-[64px]";
});

const subHeadingFontSize = computed(() => {
  const text = config.value.hero_quote_bottom || "";
  const length = text.length;

  if (length > 150) return "text-sm sm:text-base lg:text-lg";
  if (length > 100) return "text-base sm:text-lg lg:text-xl";
  return "text-base sm:text-lg lg:text-[22px]";
});

onMounted(() => {
  fetchConfig();
});
</script>

<template>
  <section
    class="bg-[#4A7043] text-[#F5F5F0] py-16 [@media(min-width:375px)_and_(max-width:768px)]:py-25 md:py-24 lg:py-32 px-6 md:px-12 lg:px-20 overflow-hidden relative">
    <div class="max-w-screen-2xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-8">

      <!-- Text Content -->
      <div class="flex-1 flex flex-col gap-6 lg:gap-8 text-center lg:text-left z-10 w-full pt-8 lg:pt-0">
        <h1
          class="font-extrabold leading-[1.2] lg:leading-[1.1] tracking-wide whitespace-pre-line"
          :class="headingFontSize"
        >
          {{ config.hero_quote_top }}
        </h1>

        <p
          class="font-medium leading-relaxed max-w-xl mx-auto lg:mx-0 text-white/95 whitespace-pre-line"
          :class="subHeadingFontSize"
        >
          {{ config.hero_quote_bottom }}
        </p>

        <div
          class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 lg:gap-5 mt-4 text-[15px] font-bold tracking-wide">
          <RouterLink to="/register-pengepul"
            class="w-full sm:w-auto bg-[#F5F5F0] text-[#4A7043] px-8 py-3.5 rounded-full hover:bg-gray-50 hover:scale-105 active:scale-95 transition-all shadow-md flex justify-center items-center">
            Beli Sampah
          </RouterLink>
          <RouterLink to="/register-nasabah"
            class="w-full sm:w-auto bg-transparent border border-[#F5F5F0] text-[#F5F5F0] px-8 py-3.5 rounded-full hover:bg-white/10 hover:scale-105 active:scale-95 transition-all flex justify-center items-center">
            Setor Sampah
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