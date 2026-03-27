<template>
  <div class="fixed top-0 left-0 w-full bg-[#4A7043] z-50 border-b border-[#73A36B] shadow-sm">
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 py-4">
      <div class="flex items-center justify-between">
        <!-- Left -->
        <div class="flex items-center gap-4">
          <RouterLink to="/" class="text-3xl md:text-4xl font-bold tracking-wide text-white">
            ABS
          </RouterLink>
        </div>

        <!-- Right -->
        <div class="hidden sm:flex items-center gap-6">
          <span class="text-white/90 text-[15px]"> Ingin menyetorkan sampah dan menabung? </span>
          <RouterLink
            to="/register-nasabah"
            class="text-white text-[15px] font-bold hover:text-white/80 transition-colors"
          >
            Buat Akun Nasabah
          </RouterLink>
        </div>
      </div>
    </div>
  </div>

  <div class="min-h-screen w-full bg-[#f6f7f6] font-['Inter'] relative pt-[80px] pb-12">
    <!-- Back Header -->
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 pt-3">
      <router-link
        to="/choose-role"
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
        Kembali
      </router-link>
    </div>

    <!-- Form Container -->
    <main class="flex flex-col items-center max-w-[560px] mx-auto px-4">
      <div
        class="w-full bg-white rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] px-10 py-12 border border-[#E8EAE8]/60"
      >
        <h1 class="text-[#4A7043] text-[28px] font-bold mb-10 text-center tracking-tight">
          Mendaftar sebagai pengepul
        </h1>

        <!-- Form bound to API -->
        <form @submit.prevent="register" class="w-full flex flex-col gap-5">
          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Nama</label>
            <input
              v-model="form.nama"
              type="text"
              placeholder="Masukkan nama lengkap"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Nama Lembaga</label>
            <input
              v-model="form.nama_lembaga"
              type="text"
              placeholder="Masukkan nama lembaga"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Alamat</label>
            <input
              v-model="form.alamat"
              type="text"
              placeholder="Masukkan alamat"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Nomor Telepon</label>
            <input
              v-model="form.no_telp"
              type="tel"
              placeholder="Masukkan nomor telepon"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Username</label>
            <input
              v-model="form.username"
              type="text"
              placeholder="Masukkan username"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Email</label>
            <input
              v-model="form.email"
              @blur="checkEmail"
              type="email"
              placeholder="Masukkan email"
              required
              class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]"
            />
            <p v-if="emailError" class="text-red-500 text-[13px] mt-1">{{ emailError }}</p>
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Password</label>
            <div class="relative flex items-center">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="Masukkan kata sandi"
                required
                class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all pr-12 text-[#5E6460] placeholder-[#a9b0aa]"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 text-[#8ba783] hover:text-[#4A7043]"
              >
                <svg
                  v-if="!showPassword"
                  width="22"
                  height="22"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  viewBox="0 0 24 24"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg
                  v-else
                  width="22"
                  height="22"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"
                  ></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide"
              >Konfirmasi Password</label
            >
            <div class="relative flex items-center">
              <input
                :type="showConfirm ? 'text' : 'password'"
                v-model="form.confirmPassword"
                placeholder="Masukkan ulang kata sandi"
                required
                class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all pr-12 text-[#5E6460] placeholder-[#a9b0aa]"
              />
              <button
                type="button"
                @click="showConfirm = !showConfirm"
                class="absolute right-4 text-[#8ba783] hover:text-[#4A7043]"
              >
                <svg
                  v-if="!showConfirm"
                  width="22"
                  height="22"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  viewBox="0 0 24 24"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg
                  v-else
                  width="22"
                  height="22"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"
                  ></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
            <p v-if="passwordMismatch" class="text-red-500 text-[13px] mt-1">
              Konfirmasi password tidak cocok!
            </p>
          </div>

          <p v-if="errorMessage" class="text-red-500 text-[13px] text-center font-medium">
            {{ errorMessage }}
          </p>
          <p v-if="successMessage" class="text-[#4A7043] text-[13px] text-center font-medium">
            {{ successMessage }}
          </p>

          <button
            type="submit"
            :disabled="isLoading || passwordMismatch || !!emailError"
            class="w-full h-[52px] bg-[#4A7043] hover:bg-[#3d5e37] active:scale-[0.99] disabled:opacity-60 text-white font-bold text-[17px] rounded-xl transition-all mt-4 shadow-sm"
          >
            {{ isLoading ? 'Mendaftar...' : 'Daftar' }}
          </button>

          <p class="text-center text-[13px] text-[#5E6460] font-medium mt-3 tracking-wide">
            Sudah punya akun?
            <router-link
              to="/login"
              class="text-[#8ba783] hover:text-[#4A7043] font-bold transition-colors"
              >Masuk</router-link
            >
          </p>
        </form>
      </div>
    </main>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import Footer from '@/components/public/Footer.vue'

const router = useRouter()
const axios = inject('axios')

const form = ref({
  nama: '',
  nama_lembaga: '',
  alamat: '',
  no_telp: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
})

const showPassword = ref(false)
const showConfirm = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const emailError = ref('')

const passwordMismatch = computed(() => {
  return (
    form.value.password &&
    form.value.confirmPassword &&
    form.value.password !== form.value.confirmPassword
  )
})

const checkEmail = async () => {
  if (!form.value.email) {
    emailError.value = ''
    return
  }

  try {
    const res = await axios.post('/api/check-email', {
      email: form.value.email,
    })

    if (res.data.used) {
      emailError.value = 'Email sudah terpakai'
    } else {
      emailError.value = ''
    }
  } catch (err) {
    console.error('Error checking email', err)
  }
}

const register = async () => {
  if (passwordMismatch.value || emailError.value) return

  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true

  try {
    await axios.get('/sanctum/csrf-cookie')

    const res = await axios.post('/api/register-pengepul', {
      username: form.value.username,
      nama: form.value.nama,
      email: form.value.email,
      password: form.value.password,
      no_telp: form.value.no_telp,
      nama_lembaga: form.value.nama_lembaga,
      alamat: form.value.alamat,
    })

    successMessage.value = 'Registrasi berhasil! Mengalihkan ke halaman login...'

    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message
      if (error.response.data.errors) {
        const errors = error.response.data.errors
        const firstError = Object.values(errors)[0][0]
        errorMessage.value = firstError
      }
    } else {
      errorMessage.value = 'Terjadi kesalahan saat mendaftar.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
