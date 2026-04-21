<script setup>
import { ref, onMounted, inject } from "vue";
import { Icon } from "@iconify/vue";

const axios = inject('axios');

const webConfig = ref({
  logo: null,
  no_telp: "085898212347",
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
  <footer class="bg-[#4A7043] text-white py-12 px-6 lg:px-20 mt-auto border-t border-white/10">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center md:items-start justify-between gap-10">
      <!-- Logo and Info -->
      <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-16 w-full">
        <div v-if="webConfig.logo" class="w-24 h-24 shrink-0 flex items-center justify-center bg-white/5 rounded-2xl p-2">
          <img :src="`${axios.defaults.baseURL}/storage/${webConfig.logo}`" class="max-w-full max-h-full object-contain" alt="Logo" />
        </div>
        <div v-else class="font-black text-6xl tracking-tighter shrink-0 uppercase">ABS</div>
        
        <!-- Divider (Vertical on desktop) -->
        <div class="hidden md:block w-px h-32 bg-white/30"></div>
        
        <div class="flex flex-col gap-4 text-center md:text-left text-sm text-white/80">
          <p class="leading-relaxed max-w-xs">
            {{ webConfig.alamat }}
          </p>
          <div class="space-y-2">
            <p class="flex items-center justify-center md:justify-start gap-2">
              <Icon icon="material-symbols:call" class="w-4 h-4" /> {{ webConfig.no_telp }}
            </p>
            <p class="flex items-center justify-center md:justify-start gap-2">
              <Icon icon="material-symbols:mail" class="w-4 h-4" /> {{ webConfig.email }}
            </p>
          </div>
        </div>
      </div>

      <!-- Social Icons -->
      <div class="flex gap-4 shrink-0">
        <a v-if="webConfig.facebook" :href="webConfig.facebook" target="_blank" class="hover:scale-110 transition-transform">
          <Icon icon="ri:facebook-circle-fill" class="w-10 h-10" />
        </a>
        <a v-if="webConfig.instagram" :href="webConfig.instagram" target="_blank" class="hover:scale-110 transition-transform">
          <Icon icon="ri:instagram-fill" class="w-10 h-10" />
        </a>
        <a v-if="webConfig.linkedin" :href="webConfig.linkedin" target="_blank" class="hover:scale-110 transition-transform">
          <Icon icon="ri:linkedin-box-fill" class="w-10 h-10" />
        </a>
        <a v-if="webConfig.youtube" :href="webConfig.youtube" target="_blank" class="hover:scale-110 transition-transform">
          <Icon icon="ri:youtube-fill" class="w-10 h-10" />
        </a>
      </div>
    </div>

    <!-- Copyright -->
    <div class="max-w-7xl mx-auto mt-12 pt-6 border-t border-white/10 text-center md:text-left">
      <p class="text-[10px] text-white/60 tracking-wider uppercase">
        © 2026 APLIKASI BANK SAMPAH. ALL RIGHTS RESERVED.
      </p>
    </div>
  </footer>
</template>
