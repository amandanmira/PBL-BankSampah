<template>
  <Navbar />
  <div class="min-h-screen w-full bg-stone-100 font-['Inter']">
    <!-- Back button -->
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 pt-3">
      <router-link 
        to="/login" 
        class="text-[#8ba783] hover:text-[#4A7043] flex items-center gap-2 text-sm font-medium transition-colors w-fit"
      >
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="w-4 h-4">
          <path d="M19 12H5M12 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Masuk
      </router-link>
    </div>

    <!-- Container -->
    <main class="flex items-center justify-center min-h-[calc(100vh-120px)] px-4">
      <div class="w-full max-w-lg">
        <!-- Card -->
        <div class="bg-stone-50 rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.08)] px-10 py-12">
          
          <!-- State 1: Input Email -->
          <div v-if="!isSent" class="flex flex-col items-center gap-10">
            <div class="flex flex-col items-center gap-2">
              <h1 class="text-[#4A7043] text-3xl font-bold text-center">Lupa Password</h1>
              <p class="text-neutral-600 text-sm font-medium text-center">Masukkan email untuk reset password</p>
            </div>

            <form @submit.prevent="handleForgotPassword" class="w-full flex flex-col gap-6">
              <div class="flex flex-col gap-2">
                <label for="email" class="text-[#4A7043] text-base font-bold">Email</label>
                <input
                  id="email"
                  type="email"
                  v-model="email"
                  placeholder="Masukkan email"
                  required
                  class="w-full h-12 px-4 py-2.5 bg-gray-200/60 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 outline-none focus:bg-gray-200 transition-colors"
                />
              </div>

              <p v-if="errorMessage" class="text-red-600 text-sm font-medium text-center">
                {{ errorMessage }}
              </p>

              <button
                type="submit"
                :disabled="isLoading"
                class="w-full h-14 bg-[#4A7043] hover:bg-[#3d5e37] disabled:opacity-60 text-white text-xl font-bold rounded-2xl transition-all shadow-md active:scale-[0.98]"
              >
                {{ isLoading ? 'Mengirim...' : 'Kirim Link Reset' }}
              </button>

              <div class="text-center">
                <router-link to="/login" class="text-neutral-600 text-sm font-medium hover:text-[#4A7043] transition-colors">
                  Kembali ke <span class="text-[#4A7043] font-bold">Masuk</span>
                </router-link>
              </div>
            </form>
          </div>

          <!-- State 2: Success Sent -->
          <div v-else class="flex flex-col items-center gap-8">
            <div class="flex flex-col items-center gap-2">
              <h1 class="text-[#4A7043] text-3xl font-bold text-center">Lupa Password</h1>
              <p class="text-neutral-600 text-sm font-medium text-center">Link reset password telah dikirim</p>
            </div>

            <div class="w-full bg-[#E9EFE8] rounded-2xl p-10 flex flex-col items-center text-center gap-4">
              <div class="w-14 h-14 bg-[#4A7043] rounded-full flex items-center justify-center text-white shadow-lg">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              
              <div class="flex flex-col gap-2">
                <h2 class="text-[#4A7043] text-xl font-bold">Email Terkirim!</h2>
                <p class="text-neutral-600 text-sm leading-relaxed px-4">
                  Kami telah mengirimkan link reset password ke <span class="text-[#4A7043] font-bold">{{ email }}</span>. Silakan cek email Anda.
                </p>
              </div>
            </div>

            <router-link to="/login" class="text-neutral-600 text-sm font-medium hover:text-[#4A7043] transition-colors">
              Kembali ke <span class="text-[#4A7043] font-bold">Masuk</span>
            </router-link>
          </div>

        </div>
      </div>
    </main>
  </div>
  <Footer />
</template>

<script setup>
import Navbar from '@/components/public/Navbar2.vue'
import Footer from '@/components/public/Footer.vue'
import { ref, inject } from 'vue'

const axios = inject('axios')
const email = ref('')
const isLoading = ref(false)
const isSent = ref(false)
const errorMessage = ref('')

const handleForgotPassword = async () => {
  try {
    isLoading.value = true
    errorMessage.value = ''
    
    const response = await axios.post('/api/password/email', { email: email.value })
    
    isSent.value = true
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>
