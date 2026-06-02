<script setup>
defineProps({
  name: { type: String, default: "Nasabah" },
});
</script>

<template>
  <div class="bg-[#4A7043] rounded-3xl p-8 text-white relative overflow-hidden shadow-lg">
    <div class="relative z-10">
      <h1 class="text-xl md:text-2xl font-bold mb-1.5">Selamat Datang, {{ name }}! 👋</h1>
      <p class="text-white/80 text-xs md:text-sm">Mari berkontribusi untuk lingkungan yang lebih bersih</p>
    </div>
    <!-- Decorative background elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-10 -mb-10"></div>
  </div>
</template>
