<script setup>
import { ref } from 'vue';

const team = ref([
  {
    isIntro: true,
    text: "Kenalan yuk sama tim di balik layar! Kami adalah sekumpulan individu yang antusias, inovatif, dan siap membantu mewujudkan ide-ide Anda menjadi kenyataan."
  },
  {
    name: "Amanda Namira T.",
    roles: ["Project Manager"],
    image: "/people.jpg",
    github: "https://github.com/amanda"
  },
  {
    name: "Raditya Hafid D.",
    roles: ["Backend Dev"],
    image: "/people.jpg",
    github: "https://github.com/raditya"
  },
  {
    name: "Hafiz Ahmad H.",
    roles: ["Backend Dev"],
    image: "/people.jpg",
    github: "https://github.com/hafiz"
  },
  {
    name: "Gilang Abdian A.",
    roles: ["Frontend Dev", "UI/UX"],
    image: "/people.jpg",
    github: "https://github.com/gilang"
  },
  {
    name: "Satrio Aji P.",
    roles: ["Frontend Dev", "UI/UX"],
    image: "/people.jpg",
    github: "https://github.com/satrio"
  },
]);
</script>

<template>
  <section class="py-16 md:py-24 w-full bg-transparent">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">

      <!-- Headers -->
      <div class="mb-10 md:mb-14">
        <p class="text-[#555555]/80 uppercase tracking-widest text-[12px] md:text-[13px] font-bold mb-2">
          OUR TEAM
        </p>
        <h2 class="text-3xl md:text-4xl lg:text-[42px] font-extrabold text-[#555555] tracking-tight">
          The Creative Minds
        </h2>
      </div>

      <!-- Grid Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-7 lg:gap-8 gsap-stagger-parent">

        <!-- Loop Card Anggota -->
        <div v-for="(member, index) in team" :key="index"
          class="gsap-stagger-item rounded-[1.25rem] overflow-hidden flex flex-col transition-all duration-300 transform hover:-translate-y-1"
          :class="member.isIntro ? 'bg-[#555555] shadow-md' : 'bg-transparent border border-[#555555]/15 shadow-sm'">
          <!-- Tipe Card 1: Intro (Abu-abu Gelap) -->
          <div v-if="member.isIntro"
            class="flex items-center justify-center p-8 md:p-10 h-full min-h-[300px] sm:min-h-[350px] md:min-h-[400px]">
            <p class="text-[#F5F5F0] text-[15.5px] md:text-[16.5px] leading-relaxed font-semibold">
              {{ member.text }}
            </p>
          </div>

          <!-- Tipe Card 2: Anggota Tim (Foto + Nama) -->
          <div v-else class="flex flex-col h-full bg-[#FAFFF9]">
            <!-- Foto dengan Aspect Ratio Statis -->
            <div class="w-full relative aspect-[3/4] sm:aspect-[4/5] bg-gray-200">
              <img :src="member.image" :alt="member.name" class="absolute inset-0 w-full h-full object-cover" />

              <!-- Ikon Tautan Sosial Media Tengah Bawah dengan Glassmorphism Blur -->
              <a v-if="member.github" :href="member.github" target="_blank"
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-[#555555]/40 backdrop-blur-md border border-white/20 text-[#F5F5F0] p-2.5 rounded-full shadow-lg hover:bg-[#4A7043]/90 hover:border-[#4A7043] hover:-translate-y-1 transition-all duration-300"
                title="Lihat Profil Github">
                <!-- SVG Ikon Github Asli (Kompabilitas Tertinggi) -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-5 md:h-5"
                  fill="currentColor">
                  <path
                    d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.268 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.293 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" />
                </svg>
              </a>
            </div>

            <!-- Area Nama dan Jabatan -->
            <div class="px-4 py-4 md:px-5 md:py-5 flex flex-col gap-2.5 bg-white md:bg-transparent mt-auto">
              <h4 class="text-[#4A7043] font-extrabold text-[15px] md:text-[16px] tracking-tight truncate w-full">
                {{ member.name }}
              </h4>
              <!-- Bungkus pill peran anggota agar aman seberapa banyak pun barisnya -->
              <div class="flex flex-wrap gap-1.5 md:gap-2">
                <span v-for="(role, rIdx) in member.roles" :key="rIdx"
                  class="border border-[#4A7043] text-[#4A7043] font-semibold text-[9.5px] md:text-[10px] px-2.5 py-[3px] rounded-full whitespace-nowrap">
                  {{ role }}
                </span>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </section>
</template>

<style scoped></style>