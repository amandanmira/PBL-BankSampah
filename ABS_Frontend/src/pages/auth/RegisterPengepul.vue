<template>
  <!-- Fixed Navbar -->
  <div class="fixed top-0 left-0 w-full bg-[#4A7043] z-50 border-b border-[#73A36B] shadow-sm">
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <RouterLink to="/" class="text-3xl md:text-4xl font-bold tracking-wide text-white">
            ABS
          </RouterLink>
        </div>
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

  <div class="min-h-screen w-full bg-stone-100 font-['Inter'] pt-24 pb-12">
    <!-- Back Header -->
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 pt-3">
      <router-link
        to="/choose-role"
        class="text-[#8ba783] hover:text-[#4A7043] flex items-center gap-2 text-sm font-medium transition-colors w-fit"
      >
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="w-4 h-4">
          <path d="M19 12H5M12 19l-7-7 7-7"></path>
        </svg>
        Kembali
      </router-link>
    </div>

    <!-- Container -->
    <main class="flex flex-col items-center max-w-[600px] mx-auto px-4 mt-4">
      <div class="w-full bg-stone-50 rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] px-8 py-10 border border-[#E8EAE8]/60">
        
        <!-- State 1: Form -->
        <div v-if="!isSuccess">
          <h1 class="text-[#4A7043] text-2xl md:text-3xl font-bold mb-8 text-center tracking-tight">
            Mendaftar sebagai pengepul
          </h1>

          <form @submit.prevent="register" class="w-full flex flex-col gap-6">
            <!-- Basic Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Nama Lengkap</label>
                <input v-model="form.nama" type="text" placeholder="Nama lengkap" required class="w-full h-12 px-4 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Username</label>
                <input v-model="form.username" type="text" placeholder="Username" required class="w-full h-12 px-4 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-[#4A7043] text-sm font-bold ml-1">Email</label>
              <input v-model="form.email" @blur="checkEmail" type="email" placeholder="Email aktif" required class="w-full h-12 px-4 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
              <p v-if="emailError" class="text-red-500 text-[12px] ml-1 font-medium">{{ emailError }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Nama Lembaga</label>
                <input v-model="form.nama_lembaga" type="text" placeholder="Contoh: PT. Maju Jaya" required class="w-full h-12 px-4 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Nomor Telepon</label>
                <input v-model="form.no_telp" type="tel" placeholder="08xxxx" required class="w-full h-12 px-4 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-[#4A7043] text-sm font-bold ml-1">Alamat Lengkap</label>
              <textarea v-model="form.alamat" rows="2" placeholder="Alamat lengkap lembaga/tempat usaha" required class="w-full px-4 py-3 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400 resize-none"></textarea>
            </div>

            <!-- Password Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Password</label>
                <div class="relative flex items-center">
                  <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Min. 8 karakter" required class="w-full h-12 px-4 pr-11 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
                  <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 text-neutral-500">
                    <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                  </button>
                </div>
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Konfirmasi Password</label>
                <div class="relative flex items-center">
                  <input :type="showConfirm ? 'text' : 'password'" v-model="form.confirmPassword" placeholder="Ulangi password" required class="w-full h-12 px-4 pr-11 bg-gray-200/60 rounded-xl text-sm outline-none focus:bg-gray-200 transition-all text-neutral-700 placeholder-neutral-400" />
                  <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3.5 text-neutral-500">
                    <svg v-if="!showConfirm" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                  </button>
                </div>
              </div>
            </div>
            <p v-if="passwordMismatch" class="text-red-500 text-[12px] ml-1 font-medium mt--4">Konfirmasi password tidak cocok!</p>

            <!-- File Uploads -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
              <!-- KTP -->
              <div class="flex flex-col gap-2">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Foto KTP</label>
                <div class="relative w-full h-32 bg-gray-200/60 rounded-2xl border-2 border-dashed border-[#8ba783]/40 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-200 transition-all overflow-hidden" @click="$refs.ktpInput.click()">
                  <input ref="ktpInput" type="file" @change="handleFile($event, 'ktp')" accept="image/*" class="hidden" required />
                  <div v-if="!form.ktp" class="flex flex-col items-center gap-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#4A7043]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <span class="text-[12px] text-neutral-500 font-medium">Upload KTP</span>
                  </div>
                  <img v-else :src="previewKtp" class="w-full h-full object-cover" />
                  <div v-if="form.ktp" class="absolute top-1 right-1 bg-white/80 rounded-full p-1 text-[#4A7043]" @click.stop="form.ktp = null; previewKtp = null">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                  </div>
                </div>
              </div>

              <!-- NPWP -->
              <div class="flex flex-col gap-2">
                <label class="text-[#4A7043] text-sm font-bold ml-1">Foto NPWP</label>
                <div class="relative w-full h-32 bg-gray-200/60 rounded-2xl border-2 border-dashed border-[#8ba783]/40 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-200 transition-all overflow-hidden" @click="$refs.npwpInput.click()">
                  <input ref="npwpInput" type="file" @change="handleFile($event, 'npwp')" accept="image/*" class="hidden" required />
                  <div v-if="!form.npwp" class="flex flex-col items-center gap-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#4A7043]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <span class="text-[12px] text-neutral-500 font-medium">Upload NPWP</span>
                  </div>
                  <img v-else :src="previewNpwp" class="w-full h-full object-cover" />
                  <div v-if="form.npwp" class="absolute top-1 right-1 bg-white/80 rounded-full p-1 text-[#4A7043]" @click.stop="form.npwp = null; previewNpwp = null">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                  </div>
                </div>
              </div>
            </div>

            <p v-if="errorMessage" class="text-red-500 text-sm font-medium text-center">
              {{ errorMessage }}
            </p>

            <button
              type="submit"
              :disabled="isLoading || passwordMismatch || !!emailError"
              class="w-full h-14 bg-[#4A7043] hover:bg-[#3d5e37] disabled:opacity-60 text-white font-bold text-xl rounded-2xl transition-all shadow-md active:scale-[0.98] mt-4"
            >
              {{ isLoading ? 'Mendaftar...' : 'Daftar Sekarang' }}
            </button>

            <p class="text-center text-sm font-medium text-neutral-600">
              Sudah punya akun?
              <router-link to="/login" class="text-[#4A7043] font-bold hover:underline ml-1">Masuk</router-link>
            </p>
          </form>
        </div>

        <!-- State 2: Success / Pending Approval -->
        <div v-else class="flex flex-col items-center gap-8 py-4">
          <div class="flex flex-col items-center gap-2">
            <h1 class="text-[#4A7043] text-3xl font-bold text-center">Registrasi Berhasil!</h1>
            <p class="text-neutral-600 text-sm font-medium text-center">Data pendaftaran telah kami terima</p>
          </div>

          <div class="w-full bg-[#E9EFE8] rounded-3xl p-10 flex flex-col items-center text-center gap-5">
            <div class="w-20 h-20 bg-[#4A7043] rounded-full flex items-center justify-center text-white shadow-xl">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            
            <div class="flex flex-col gap-3">
              <h2 class="text-[#4A7043] text-2xl font-bold">Menunggu Persetujuan</h2>
              <p class="text-neutral-600 text-[15px] leading-relaxed">
                Halo <span class="text-[#4A7043] font-bold">{{ form.nama }}</span>, akun Anda sedang dalam proses verifikasi oleh tim Admin ABS. 
                <br><br>
                Kami akan segera mengaktifkan akun Anda setelah dokumen pendukung (KTP & NPWP) divalidasi. Silakan cek email Anda secara berkala.
              </p>
            </div>
          </div>

          <router-link
            to="/login"
            class="w-full h-14 bg-[#4A7043] hover:bg-[#3d5e37] text-white text-xl font-bold rounded-2xl transition-all shadow-md flex items-center justify-center active:scale-[0.98]"
          >
            Kembali ke Login
          </router-link>
        </div>

      </div>
    </main>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import Footer from '@/components/public/Footer.vue'
import { redirectLogin } from "@/utils";

redirectLogin()

const axios = inject('axios')

const form = ref({
  nama: '',
  username: '',
  email: '',
  nama_lembaga: '',
  no_telp: '',
  alamat: '',
  password: '',
  confirmPassword: '',
  ktp: null,
  npwp: null,
})

const previewKtp = ref(null)
const previewNpwp = ref(null)

const showPassword = ref(false)
const showConfirm = ref(false)
const isLoading = ref(false)
const isSuccess = ref(false)
const errorMessage = ref('')
const emailError = ref('')

const passwordMismatch = computed(() => {
  return form.value.password && form.value.confirmPassword && form.value.password !== form.value.confirmPassword
})

const handleFile = (e, type) => {
  const file = e.target.files[0]
  if (!file) return

  if (type === 'ktp') {
    form.value.ktp = file
    previewKtp.value = URL.createObjectURL(file)
  } else if (type === 'npwp') {
    form.value.npwp = file
    previewNpwp.value = URL.createObjectURL(file)
  }
}

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
  isLoading.value = true

  try {
    await axios.get('/sanctum/csrf-cookie')

    const formData = new FormData()
    formData.append("username", form.value.username)
    formData.append("nama", form.value.nama)
    formData.append("email", form.value.email)
    formData.append("password", form.value.password)
    formData.append("no_telp", form.value.no_telp)
    formData.append("nama_lembaga", form.value.nama_lembaga)
    formData.append("alamat", form.value.alamat)
    formData.append("ktp", form.value.ktp)
    formData.append("npwp", form.value.npwp)

    await axios.post('/api/register-pengepul', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    isSuccess.value = true
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat mendaftar.'
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const firstError = Object.values(errors)[0][0]
      errorMessage.value = firstError
    }
  } finally {
    isLoading.value = false
  }
}
</script>
