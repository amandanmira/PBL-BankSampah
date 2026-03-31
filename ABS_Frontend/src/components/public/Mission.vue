<script setup>
// Mengimpor icon dari package yang sudah Anda install
import EcoIcon from "@iconify-vue/material-symbols/eco";
import RecyclingIcon from "@iconify-vue/material-symbols/recycling";
import GroupsIcon from "@iconify-vue/material-symbols/groups";
</script>

<template>
  <section class="py-16 md:py-24 w-full bg-transparent">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">

      <!-- Bagian Judul -->
      <div class="text-center mb-16 md:mb-20">
        <h2 class="text-3xl md:text-4xl lg:text-[42px] font-extrabold text-[#555555] mb-4 tracking-tight">
          Misi Utama
        </h2>
        <p class="text-[15px] md:text-[16.5px] text-[#555555] font-medium max-w-2xl mx-auto">
          Kami berkomitmen menciptakan dampak pelestarian lingkungan jangka panjang sekaligus memberdayakan roda ekonomi
          masyarakat sekitar setiap harinya.
        </p>
      </div>

      <!-- Tiga Kolom Misi -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-14 mb-16 md:mb-24 gsap-stagger-parent">

        <!-- Kolom 1 -->
        <div class="gsap-stagger-item flex flex-col text-center md:text-left items-center md:items-start">
          <div class="flex items-center gap-3 mb-5">
            <div
              class="w-[42px] h-[42px] md:w-[48px] md:h-[48px] rounded-full bg-black text-white flex items-center justify-center flex-shrink-0 shadow-sm">
              <EcoIcon class="w-6 h-6 md:w-7 md:h-7" />
            </div>
            <h3 class="text-[#4A7043] text-2xl md:text-[26px] font-extrabold tracking-wide">
              Pelestarian Alam
            </h3>
          </div>
          <p class="text-[#555555] text-[15px] leading-relaxed font-medium">
            Kami mendedikasikan platform modern ini secara eksklusif untuk menekan laju pembuangan limbah ke Tempat
            Pembuangan Akhir, menekan polusi material terurai lama, serta setia menjaga keasrian lingkungan hidup demi
            keberlangsungan generasi masa depan.
          </p>
        </div>

        <!-- Kolom 2 -->
        <div class="gsap-stagger-item flex flex-col text-center md:text-left items-center md:items-start">
          <div class="flex items-center gap-3 mb-5">
            <div
              class="w-[42px] h-[42px] md:w-[48px] md:h-[48px] rounded-full bg-black text-white flex items-center justify-center flex-shrink-0 shadow-sm">
              <RecyclingIcon class="w-6 h-6 md:w-7 md:h-7" />
            </div>
            <h3 class="text-[#4A7043] text-2xl md:text-[26px] font-extrabold tracking-wide">
              Sirkular Ekonomi
            </h3>
          </div>
          <p class="text-[#555555] text-[15px] leading-relaxed font-medium">
            Kami bertekat merombak cara pandang konvensional masyarakat luas terhadap sampah harian. Tumpukan rongsokan
            bukanlah sebuah akhir yang merugikan, melainkan fase awal dari siklus ekonomi baru yang memiliki daya jual
            dan kebermanfaatan tinggi jika dikelola.
          </p>
        </div>

        <!-- Kolom 3 -->
        <div class="gsap-stagger-item flex flex-col text-center md:text-left items-center md:items-start">
          <div class="flex items-center gap-3 mb-5">
            <div
              class="w-[42px] h-[42px] md:w-[48px] md:h-[48px] rounded-full bg-black text-white flex items-center justify-center flex-shrink-0 shadow-sm">
              <GroupsIcon class="w-6 h-6 md:w-7 md:h-7" />
            </div>
            <h3 class="text-[#4A7043] text-2xl md:text-[26px] font-extrabold tracking-wide">
              Pemberdayaan
            </h3>
          </div>
          <p class="text-[#555555] text-[15px] leading-relaxed font-medium">
            Kami membekali agen pengepul lokal dengan instrumen teknologi pencatatan yang tersistematis, serta sabar
            mendidik ribuan masyarakat sipil agar secara proaktif mendulang imbal finansial langsung dari kebudayaan
            disiplin memilah rongsokan dari rumah.
          </p>
        </div>

      </div>

      <!-- Garis Bawah Hijau (Penutup Section) -->
      <hr class="border-t-[1.5px] border-[#4A7043]/60 w-full" />

    </div>
  </section>
</template>

<style scoped></style>