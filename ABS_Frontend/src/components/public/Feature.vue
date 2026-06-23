<script setup></script>

<template>
  <section class="py-16 md:py-24 bg-[#FAFFF9]">
    <!-- Added tight container with horizontal padding to guarantee cards never touch screen edges -->
    <div class="max-w-5xl lg:max-w-6xl mx-auto px-6 md:px-12 lg:px-16">
      <h2
        class="text-center text-2xl md:text-3xl lg:text-[34px] font-extrabold text-[#555555] mb-12 md:mb-20 tracking-tight"
      >
        Kemudahan Kelola Sampah di Ujung Jari.
      </h2>

      <div class="flex flex-col gap-10 md:gap-14 lg:gap-16">
        <!-- Card 1 -->
        <div
          class="gsap-fade-up bg-[#925733] w-full md:w-[95%] mr-auto rounded-none rounded-r-[40px] md:rounded-r-[150px] lg:rounded-r-[200px] flex flex-col md:flex-row items-center gap-8 py-10 px-8 md:py-14 md:pl-12 lg:pl-16 md:pr-24 shadow-md"
        >
          <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl md:text-[22px] lg:text-2xl font-bold text-white mb-4 leading-snug">
              Konversi Timbangan ke Saldo Digital
            </h3>
            <p class="text-white/90 text-[15px] md:text-base leading-relaxed">
              Setiap sampah yang disetorkan akan ditimbang secara presisi dan dikonversi menjadi
              nilai Rupiah. Nasabah dapat menyimpan hasil konversi tersebut sebagai deposit di dalam
              aplikasi dan melakukan penarikan saldo secara fleksibel.
            </p>
          </div>
          <div class="flex-1 flex justify-center w-full">
            <img
              src="/fitur1.svg"
              alt="Ilustrasi Fitur 1"
              class="w-full aspect-square max-w-[200px] md:max-w-[260px] lg:max-w-[300px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500"
            />
          </div>
        </div>

        <!-- Card 2 -->
        <div
          class="gsap-fade-up bg-[#4A7043] w-full md:w-[95%] ml-auto rounded-none rounded-l-[40px] md:rounded-l-[150px] lg:rounded-l-[200px] flex flex-col-reverse md:flex-row items-center gap-8 py-10 px-8 md:py-14 md:pr-12 lg:pr-16 md:pl-24 shadow-md"
        >
          <div class="flex-1 flex justify-center w-full">
            <img
              src="/fitur2.svg"
              alt="Ilustrasi Fitur 2"
              class="w-full aspect-square max-w-[200px] md:max-w-[260px] lg:max-w-[300px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500"
            />
          </div>
          <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl md:text-[22px] lg:text-2xl font-bold text-white mb-4 leading-snug">
              Jemput sampah ke rumah
            </h3>
            <p class="text-white/90 text-[15px] md:text-base leading-relaxed">
              Tidak sempat datang ke bank sampah? Jadwalkan penjemputan langsung dari aplikasi, dan
              petugas kami akan datang ke lokasimu.
            </p>
          </div>
        </div>

        <!-- Card 3 -->
        <div
          class="gsap-fade-up bg-[#777777] w-full md:w-[95%] mr-auto rounded-none rounded-r-[40px] md:rounded-r-[150px] lg:rounded-r-[200px] flex flex-col md:flex-row items-center gap-8 py-10 px-8 md:py-14 md:pl-12 lg:pl-16 md:pr-24 shadow-md"
        >
          <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl md:text-[22px] lg:text-2xl font-bold text-white mb-4 leading-snug">
              Notifikasi Real-time
            </h3>
            <p class="text-white/90 text-[15px] md:text-base leading-relaxed">
              Dapatkan pemberitahuan langsung ke perangkatmu mengenai status transaksi dan
              penjemputan.
            </p>
          </div>
          <div class="flex-1 flex justify-center w-full">
            <img
              src="/fitur3.svg"
              alt="Ilustrasi Fitur 3"
              class="w-full aspect-square max-w-[200px] md:max-w-[260px] lg:max-w-[300px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500"
            />
          </div>
        </div>

        <!-- Card 4 -->
        <div
          class="gsap-fade-up bg-[#4A9D9D] w-full md:w-[95%] ml-auto rounded-none rounded-l-[40px] md:rounded-l-[150px] lg:rounded-l-[200px] flex flex-col-reverse md:flex-row items-center gap-8 py-10 px-8 md:py-14 md:pr-12 lg:pr-16 md:pl-24 shadow-md"
        >
          <div class="flex-1 flex justify-center w-full">
            <img
              src="/fitur4.svg"
              alt="Ilustrasi Fitur 4"
              class="w-full aspect-square max-w-[200px] md:max-w-[260px] lg:max-w-[300px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-500"
            />
          </div>
          <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl md:text-[22px] lg:text-2xl font-bold text-white mb-4 leading-snug">
              Pantau Saldo & Riwayat
            </h3>
            <p class="text-white/90 text-[15px] md:text-base leading-relaxed">
              Cek saldo poinmu dan lihat seluruh riwayat transaksi yang pernah kamu lakukan dengan
              bank sampah secara lengkap.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
