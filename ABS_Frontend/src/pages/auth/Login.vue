<template>
  <Navbar />
  <div class="min-h-screen w-full bg-stone-100 font-['Inter'] relative">
    <!-- Login Container -->
    <main class="flex justify-center items-center min-h-[calc(100vh-80px)] py-16 px-4">
      <!-- Login Card -->
      <div
        class="w-full max-w-[500px] bg-white rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.08)] px-12 py-11"
      >
        <div class="flex flex-col items-center gap-11">
          <!-- Header -->
          <div class="flex flex-col items-center gap-[5px]">
            <h1 class="text-[#4A7043] text-3xl font-bold text-center">Masuk</h1>
            <p class="text-neutral-600 text-sm font-medium text-center">Masuk ke akunmu</p>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleLogin" class="w-full flex flex-col gap-3">
            <!-- Fields -->
            <div class="flex flex-col gap-5">
              <!-- Email -->
              <div class="flex flex-col gap-2">
                <label for="email" class="text-[#4A7043] text-base font-bold">Email</label>
                <input
                  id="email"
                  type="email"
                  v-model="form.email"
                  placeholder="Masukkan email"
                  required
                  class="w-full h-12 px-3.5 py-2.5 bg-gray-200 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 outline-none focus:bg-gray-300 transition-colors"
                />
              </div>

              <!-- Password -->
              <div class="flex flex-col gap-2">
                <label for="password" class="text-[#4A7043] text-base font-bold">Password</label>
                <div class="relative flex items-center">
                  <input
                    ref="passwordInput"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    placeholder="Masukkan kata sandi"
                    required
                    class="w-full h-12 px-3.5 py-2.5 pr-11 bg-gray-200 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 outline-none focus:bg-gray-300 transition-colors duration-100"
                  />
                  <button
                    type="button"
                    @mousedown.prevent
                    @click="togglePassword"
                    class="absolute right-3.5 text-neutral-500 hover:text-neutral-700"
                  >
                    <svg
                      v-if="!showPassword"
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z"
                        stroke="currentColor"
                        stroke-width="1.67"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.67" />
                    </svg>
                    <svg
                      v-else
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.94 17.94A10.07 10.07 0 0112 20C5 20 1 12 1 12a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"
                        stroke="currentColor"
                        stroke-width="1.67"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <line
                        x1="1"
                        y1="1"
                        x2="23"
                        y2="23"
                        stroke="currentColor"
                        stroke-width="1.67"
                        stroke-linecap="round"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Forgot Password -->
            <div class="flex justify-end">
              <a
                href="/forgot-password"
                class="text-[#4A7043] text-sm font-medium no-underline hover:underline"
                >Lupa Password?</a
              >
            </div>

            <!-- Error Message -->
            <p v-if="errorMessage" class="text-red-600 text-sm font-medium text-center">
              {{ errorMessage }}
            </p>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isLoading"
              class="w-full h-12 bg-[#4A7043] hover:bg-[#3d5e37] disabled:opacity-60 text-stone-50 text-xl font-bold rounded-2xl transition-colors cursor-pointer mt-2"
            >
              {{ isLoading ? 'Memuat...' : 'Masuk' }}
            </button>

            <!-- Register Link -->
            <p class="text-center text-sm">
              <span class="text-neutral-600 font-medium">Belum punya akun? </span>
              <router-link to="/choose-role" class="text-[#4A7043] font-semibold hover:underline"
                >Daftar</router-link
              >
            </p>
          </form>
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
import { useRouter } from 'vue-router'

const router = useRouter()

// Mengambil axios yang sudah kita atur di main.js sebelumnya
const axios = inject('axios')

const form = ref({ email: '', password: '' })
const errorMessage = ref('')

// show password
const showPassword = ref(false)
const passwordInput = ref(null)

const togglePassword = () => {
  const input = passwordInput.value
  const start = input.selectionStart
  const end = input.selectionEnd

  showPassword.value = !showPassword.value

  requestAnimationFrame(() => {
    input.setSelectionRange(start, end)
    input.focus()
  })
}

// Fungsi Login
const handleLogin = async () => {
  try {
    errorMessage.value = ''

    // LANGKAH 1 WAJIB: Minta tiket / cookie keamanan ke Laravel
    await axios.get('/sanctum/csrf-cookie')

    // LANGKAH 2: Kirim data form login
    const response = await axios.post('/api/login', form.value)

    console.log(response.data)

    const role = response.data.role

    sessionStorage.setItem('user', JSON.stringify(response.data.user))
    sessionStorage.setItem('role', role)
    sessionStorage.setItem('token', response.data.token)

    // Redirect berdasarkan role
    switch (role) {
      case 'admin':
        router.push('/dashboard-admin')
        break
      case 'manager':
        router.push('/dashboard-manager')
        break
      case 'petugas':
        router.push('/dashboard-petugas')
        break
      case 'pengepul':
        router.push('/dashboard-pengepul')
        break
      case 'nasabah':
        router.push('/dashboard-nasabah')
        break
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error
  }
}
</script>
