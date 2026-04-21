<script setup>
import { ref, onMounted, inject } from 'vue';
import { RouterLink } from 'vue-router';
import { Icon } from "@iconify/vue";

const axios = inject('axios');

const webConfig = ref({
  logo: null,
  quote: "Ubah Sampahmu Menjadi Berkah dan Bernilai",
  no_telp: "085679340192",
  email: "abs@gmail.com",
  alamat: "Jl. Surakarta No. 09, Surakarta, Jawa Tengah",
  facebook: "#",
  instagram: "#",
  linkedin: "#",
  youtube: "#"
});

const fetchWebConfig = async () => {
  try {
    const res = await axios.get("/api/web-config");
    if (res.data) {
      webConfig.value = {
        ...webConfig.value,
        ...res.data
      };
    }
  } catch (err) {
    console.error("Failed to fetch web config:", err);
  }
};

onMounted(() => {
  fetchWebConfig();
});
</script>

<template>
  <footer class="bg-[#4A7043] text-[#F5F5F0] py-16 md:py-20 border-t border-white/10">
    <div class="max-w-6xl mx-auto px-6 md:px-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">

      <!-- Column 1: Brand Info -->
      <div class="space-y-6">
        <div v-if="webConfig.logo" class="w-32 h-20 shrink-0 flex items-center justify-start">
          <img :src="`${axios.defaults.baseURL}/storage/${webConfig.logo}`" class="max-w-full max-h-full object-contain" alt="Logo" />
        </div>
        <h2 v-else class="text-4xl md:text-[42px] font-extrabold tracking-wide mb-2 uppercase">ABS</h2>
        
        <p class="text-[14px] leading-relaxed font-medium max-w-[220px]">
          {{ webConfig.quote }}
        </p>
        
        <div class="pt-4 space-y-1">
          <p class="font-bold text-[14px]">Alamat:</p>
          <p class="text-[14px] leading-relaxed max-w-[220px]">
            {{ webConfig.alamat }}
          </p>
        </div>
        
        <div class="pt-4 space-y-2">
          <p class="font-bold text-[14px]">Kontak:</p>
          <div class="flex items-center gap-2 text-[14px]">
            <Icon icon="material-symbols:call" class="w-[18px] h-[18px]" />
            <p>{{ webConfig.no_telp }}</p>
          </div>
          <div class="flex items-center gap-2 text-[14px]">
            <Icon icon="material-symbols:mail" class="w-[18px] h-[18px]" />
            <p>{{ webConfig.email }}</p>
          </div>
        </div>
      </div>

      <!-- Column 2: Navigasi -->
      <div class="lg:pl-8">
        <h3 class="font-bold text-[17px] mb-6 tracking-wide uppercase">Navigasi</h3>
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
        <h3 class="font-bold text-[17px] mb-6 tracking-wide uppercase">Tentang Kami</h3>
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
        <h3 class="font-bold text-[17px] mb-6 tracking-wide uppercase">Ikuti Kami</h3>
        <div class="flex flex-wrap gap-4">
          <!-- Facebook -->
          <a v-if="webConfig.facebook" :href="webConfig.facebook" target="_blank"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <Icon icon="ri:facebook-fill" class="w-[22px] h-[22px]" />
          </a>

          <!-- Instagram -->
          <a v-if="webConfig.instagram" :href="webConfig.instagram" target="_blank"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <Icon icon="ri:instagram-line" class="w-[20px] h-[20px]" />
          </a>

          <!-- LinkedIn -->
          <a v-if="webConfig.linkedin" :href="webConfig.linkedin" target="_blank"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm">
            <Icon icon="ri:linkedin-fill" class="w-[20px] h-[20px]" />
          </a>

          <!-- YouTube -->
          <a v-if="webConfig.youtube" :href="webConfig.youtube" target="_blank"
            class="w-10 h-10 rounded-full bg-[#F5F5F0] text-[#4A7043] flex items-center justify-center hover:scale-110 hover:bg-white transition-all shadow-sm pl-[1px]">
            <Icon icon="ri:youtube-fill" class="w-[22px] h-[22px]" />
          </a>
        </div>
      </div>

    </div>

    <!-- Copyright -->
    <div class="max-w-6xl mx-auto px-6 md:px-12 mt-16 md:mt-24">
      <div class="border-t border-[#F5F5F0]/30 pt-8 text-center text-[13px] md:text-sm font-light tracking-wider uppercase">
        © Copyright ABS 2026. All Rights Reserved.
      </div>
    </div>
  </footer>
</template>

<style scoped></style>