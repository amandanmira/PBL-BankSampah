<template>
  <Navbar2/>
  <div class="min-h-screen w-full bg-[#f6f7f6] font-['Inter'] relative pt-6 pb-12">
    <!-- Back Header -->
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 pt-1.5">
      <router-link
        to="/"
        class="text-[#8ba783] hover:text-[#4A7043] flex items-center gap-2 text-sm font-medium transition-colors w-fit"
      >
        <svg
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
          class="w-4 h-4"
        >
          <path d="M19 12H5M12 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Beranda
      </router-link>
    </div>

    <!-- Main Content -->
    <main class="flex flex-col items-center max-w-4xl mx-auto px-4 mt-8">
      <h1 class="text-[#5E6460] text-2xl md:text-[28px] font-medium mb-10 text-center tracking-wide">
        Bergabung sebagai nasabah atau pengepul
      </h1>

      <!-- Cards Container -->
      <div class="flex flex-col sm:flex-row gap-8 mb-16">
        <!-- Nasabah Card -->
        <button 
          @click="selectedRole = 'nasabah'"
          :class="[
            'w-[250px] h-[250px] rounded-[30px] flex flex-col items-center justify-center transition-all duration-300',
            selectedRole === 'nasabah' 
              ? 'bg-[#4A7043] shadow-[0_12px_30px_rgba(74,112,67,0.3)] border-transparent' 
              : 'bg-white shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-[#E8EAE8] hover:border-[#4A7043]/40 hover:-translate-y-1'
          ]"
        >
          <!-- Nasabah Icon (placeholder mimicking person with basket) -->
          <svg width="90" height="90" viewBox="0 0 100 100" fill="none" class="mb-5">
            <circle cx="28" cy="20" r="10" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6"/>
            <path d="M28 35 v25 m-15 0 h30" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M28 45 C45 45, 52 50, 52 65" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6" stroke-linecap="round" fill="none"/>
            <path d="M13 60 v15 M43 60 v15" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6" stroke-linecap="round"/>
            <rect x="52" y="55" width="28" height="20" rx="3" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6" fill="none"/>
            <path d="M57 55 v-5 a5 5 0 0 1 10 0 v5" :stroke="selectedRole === 'nasabah' ? '#fff' : '#5E6460'" stroke-width="6" fill="none" stroke-linecap="round"/>
          </svg>
          <span :class="['text-[26px] font-bold tracking-wide', selectedRole === 'nasabah' ? 'text-white' : 'text-[#5E6460]']">Nasabah</span>
        </button>

        <!-- Pengepul Card -->
        <button 
          @click="selectedRole = 'pengepul'"
          :class="[
            'w-[250px] h-[250px] rounded-[30px] flex flex-col items-center justify-center transition-all duration-300',
            selectedRole === 'pengepul' 
              ? 'bg-[#4A7043] shadow-[0_12px_30px_rgba(74,112,67,0.3)] border-transparent' 
              : 'bg-white shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-[#E8EAE8] hover:border-[#4A7043]/40 hover:-translate-y-1'
          ]"
        >
          <!-- Pengepul Icon (placeholder mimicking person with trash) -->
          <svg width="90" height="90" viewBox="0 0 100 100" fill="none" class="mb-5">
            <!-- person -->
            <circle cx="25" cy="18" r="9" :fill="selectedRole === 'pengepul' ? '#fff' : '#5E6460'"/>
            <path d="M25 32 v22" :stroke="selectedRole === 'pengepul' ? '#fff' : '#5E6460'" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 65 l10 -15 M35 65 l-10 -15" :stroke="selectedRole === 'pengepul' ? '#fff' : '#5E6460'" stroke-width="7" stroke-linecap="round"/>
            <path d="M15 45 l10 -10 l15 5" :stroke="selectedRole === 'pengepul' ? '#fff' : '#5E6460'" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
            
            <!-- bag -->
            <path d="M40 50 C40 40, 52 40, 52 50 C58 55, 58 65, 46 65 C34 65, 34 55, 40 50 Z" :fill="selectedRole === 'pengepul' ? '#fff' : '#5E6460'"/>
            
            <!-- bin -->
            <rect x="60" y="35" width="22" height="30" rx="2" :stroke="selectedRole === 'pengepul' ? '#fff' : '#5E6460'" stroke-width="7" fill="none"/>
            <line x1="56" y1="35" x2="86" y2="35" :stroke="selectedRole === 'pengepul' ? '#fff' : '#5E6460'" stroke-width="7" stroke-linecap="round"/>
          </svg>
          <span :class="['text-[26px] font-bold tracking-wide', selectedRole === 'pengepul' ? 'text-white' : 'text-[#5E6460]']">Pengepul</span>
        </button>
      </div>

      <!-- Continue Button -->
      <div class="flex flex-col items-center transition-all duration-300" :class="{ 'opacity-100 scale-100': selectedRole, 'opacity-0 scale-95 pointer-events-none': !selectedRole }">
        <router-link 
          :to="selectedRole === 'nasabah' ? '/register-nasabah' : '/register-pengepul'"
          class="bg-[#4A7043] hover:bg-[#3d5e37] text-white px-12 py-3.5 rounded-full font-bold text-[17px] transition-transform active:scale-95 shadow-md mb-4"
        >
          Jadi {{ selectedRole === 'nasabah' ? 'Nasabah' : 'Pengepul' }}
        </router-link>
        
        <p class="text-[13px] text-[#5E6460] font-medium tracking-wide">
          Sudah punya akun? <router-link to="/login" class="text-[#8ba783] hover:text-[#4A7043] transition-colors font-bold">Masuk</router-link>
        </p>
      </div>
    </main>
  </div>
  <Footer />
</template>

<script setup>
import Navbar2 from '@/components/public/Navbar2.vue'
import Footer from '@/components/public/Footer.vue'
import { ref } from 'vue'

const selectedRole = ref('nasabah') // Default state as shown in image
</script>
