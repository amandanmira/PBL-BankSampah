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
          
          <!-- State 1: Input New Password -->
          <div v-if="!isSuccess" class="flex flex-col items-center gap-10">
            <div class="flex flex-col items-center gap-2">
              <h1 class="text-[#4A7043] text-3xl font-bold text-center">Reset Password</h1>
              <p class="text-neutral-600 text-sm font-medium text-center">
                Ganti password untuk: <span class="text-[#4A7043] font-bold">{{ email }}</span>
              </p>
            </div>

            <form @submit.prevent="handleResetPassword" class="w-full flex flex-col gap-5">
              <!-- Password Baru -->
              <div class="flex flex-col gap-2">
                <label for="password" class="text-[#4A7043] text-base font-bold">Password Baru</label>
                <div class="relative flex items-center">
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    placeholder="Masukkan password baru"
                    required
                    class="w-full h-12 px-4 py-2.5 bg-gray-200/60 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 outline-none focus:bg-gray-200 transition-colors"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3.5 text-neutral-500 hover:text-neutral-700"
                  >
                    <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                      <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Konfirmasi Password Baru -->
              <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-[#4A7043] text-base font-bold">Konfirmasi Password Baru</label>
                <div class="relative flex items-center">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="form.password_confirmation"
                    placeholder="Konfirmasi password baru"
                    required
                    class="w-full h-12 px-4 py-2.5 bg-gray-200/60 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 outline-none focus:bg-gray-200 transition-colors"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3.5 text-neutral-500 hover:text-neutral-700"
                  >
                    <svg v-if="!showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                      <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                  </button>
                </div>
              </div>

              <p v-if="errorMessage" class="text-red-600 text-sm font-medium text-center">
                {{ errorMessage }}
              </p>

              <button
                type="submit"
                :disabled="isLoading"
                class="w-full h-14 bg-[#4A7043] hover:bg-[#3d5e37] disabled:opacity-60 text-white text-xl font-bold rounded-2xl transition-all shadow-md active:scale-[0.98] mt-4"
              >
                {{ isLoading ? 'Memproses...' : 'Reset Password' }}
              </button>
            </form>
          </div>

          <!-- State 2: Success Reset -->
          <div v-else class="flex flex-col items-center gap-8">
            <div class="flex flex-col items-center gap-2">
              <h1 class="text-[#4A7043] text-3xl font-bold text-center">Reset Password</h1>
              <p class="text-neutral-600 text-sm font-medium text-center">Password berhasil diperbarui</p>
            </div>

            <div class="w-full bg-[#E9EFE8] rounded-2xl p-10 flex flex-col items-center text-center gap-4">
              <div class="w-14 h-14 bg-[#4A7043] rounded-full flex items-center justify-center text-white shadow-lg">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              
              <div class="flex flex-col gap-2">
                <h2 class="text-[#4A7043] text-xl font-bold">Berhasil!</h2>
                <p class="text-neutral-600 text-sm leading-relaxed px-4">
                  Password Anda berhasil diganti. Silakan gunakan password baru Anda untuk masuk ke sistem.
                </p>
              </div>
            </div>

            <router-link
              to="/login"
              class="w-full h-14 bg-[#4A7043] hover:bg-[#3d5e37] text-white text-xl font-bold rounded-2xl transition-all shadow-md flex items-center justify-center active:scale-[0.98]"
            >
              Masuk ke Akun
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
import { ref, inject, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const axios = inject('axios')
const route = useRoute()
const router = useRouter()

const email = ref('')
const token = ref('')
const isLoading = ref(false)
const isSuccess = ref(false)
const errorMessage = ref('')

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = ref({
  password: '',
  password_confirmation: ''
})

onMounted(() => {
  token.value = route.params.token
  email.value = route.query.email
  
  if (!token.value || !email.value) {
    errorMessage.value = 'Data reset password tidak lengkap. Silakan klik link dari email kembali.'
  }
})

const handleResetPassword = async () => {
  try {
    if (form.value.password !== form.value.password_confirmation) {
      errorMessage.value = 'Konfirmasi password tidak cocok.'
      return
    }

    isLoading.value = true
    errorMessage.value = ''
    
    await axios.post('/api/password/reset', {
      token: token.value,
      email: email.value,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    isSuccess.value = true
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>
