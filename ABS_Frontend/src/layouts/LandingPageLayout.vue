<script setup>
import Footer from '@/components/public/Footer.vue';
import Navbar from '@/components/public/Navbar.vue';
import { onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { initScrollAnimations } from '@/lib/gsapInit.js';

const route = useRoute();

onMounted(() => {
  nextTick(() => {
    // Beri delay sedikit agar DOM merender stabil sebelum diikat GSAP
    setTimeout(() => initScrollAnimations(), 150);
  });
});

watch(
  () => route.path,
  () => {
    nextTick(() => {
      setTimeout(() => initScrollAnimations(), 150);
    });
  }
);
</script>

<template>
  <Navbar />
  <RouterView />
  <Footer />
</template>

<style scoped></style>