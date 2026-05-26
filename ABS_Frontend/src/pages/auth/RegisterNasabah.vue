<template>
  <!-- Fixed Navbar top bar to mimic absolute header in the screenshot -->
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
          <span class="text-white/90 text-[15px]">
            Butuh pasokan sampah daur ulang?
          </span>
          <RouterLink
            to="/register-pengepul"
            class="text-white text-[15px] font-bold hover:text-white/80 transition-colors"
          >
            Buat Akun Pengepul
          </RouterLink>
        </div>
      </div>
    </div>
  </div>

  <div class="min-h-screen w-full bg-[#f6f7f6] font-['Inter'] relative pt-[80px] pb-12">
    <!-- Back Header -->
    <div class="max-w-screen-2xl mx-auto px-6 md:px-12 lg:px-20 pt-3">
      <button 
        @click="handleBack"
        class="text-[#8ba783] hover:text-[#4A7043] flex items-center gap-2 text-sm font-medium transition-colors w-fit bg-transparent border-none cursor-pointer"
      >
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="w-4 h-4">
          <path d="M19 12H5M12 19l-7-7 7-7"></path>
        </svg>
        {{ step === 1 ? 'Kembali' : 'Kembali ke Registrasi' }}
      </button>
    </div>

    <!-- Form Container -->
    <main class="flex flex-col items-center max-w-[560px] mx-auto px-4">
      <div v-if="step === 1" class="w-full bg-white rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] px-10 py-12 border border-[#E8EAE8]/60">
        <h1 class="text-[#4A7043] text-[28px] font-bold mb-10 text-center tracking-tight">
          Mendaftar sebagai nasabah
        </h1>

        <form @submit.prevent="register" class="w-full flex flex-col gap-5">
          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Username</label>
            <input v-model="form.username" type="text" placeholder="Masukkan username" required class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]" />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Nama</label>
            <input v-model="form.nama" type="text" placeholder="Masukkan nama" required class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]" />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Email</label>
            <input v-model="form.email" @blur="checkEmail" type="email" placeholder="Masukkan email" required class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all text-[#5E6460] placeholder-[#a9b0aa]" />
            <p v-if="emailError" class="text-red-500 text-[13px] mt-1">{{ emailError }}</p>
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Password</label>
            <div class="relative flex items-center">
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Masukkan kata sandi" required class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all pr-12 text-[#5E6460] placeholder-[#a9b0aa]" />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-4 text-[#8ba783] hover:text-[#4A7043]">
                <svg v-if="!showPassword" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg v-else width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>
          
          <div class="flex flex-col gap-2">
            <label class="text-[#73A36B] text-[15px] font-bold tracking-wide">Konfirmasi Password</label>
            <div class="relative flex items-center">
              <input :type="showConfirm ? 'text' : 'password'" v-model="form.confirmPassword" placeholder="Masukkan ulang kata sandi" required class="w-full h-[50px] px-4 bg-[#eff1ef] rounded-xl text-[15px] outline-none focus:ring-2 focus:ring-[#4A7043]/30 transition-all pr-12 text-[#5E6460] placeholder-[#a9b0aa]" />
              <button type="button" @click="showConfirm = !showConfirm" class="absolute right-4 text-[#8ba783] hover:text-[#4A7043]">
                <svg v-if="!showConfirm" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg v-else width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
            <p v-if="passwordMismatch" class="text-red-500 text-[13px] mt-1">Konfirmasi password tidak cocok!</p>
            <p v-if="serverError" class="text-red-500 text-[13px] mt-1">{{ serverError }}</p>
          </div>

          <button type="submit" :disabled="isLoading || passwordMismatch || !!emailError" class="w-full h-[52px] bg-[#4A7043] hover:bg-[#3d5e37] active:scale-[0.99] disabled:opacity-60 text-white font-bold text-[17px] rounded-xl transition-all mt-6 shadow-sm">
            {{ isLoading ? 'Memproses...' : 'Daftar' }}
          </button>

          <p class="text-center text-[13px] text-[#5E6460] font-medium mt-3 tracking-wide">
            Sudah punya akun? <router-link to="/login" class="text-[#8ba783] hover:text-[#4A7043] font-bold transition-colors">Masuk</router-link>
          </p>
        </form>
      </div>

      <!-- STEP 2: VERIFICATION -->
      <div v-else class="w-full bg-white rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] px-10 py-12 border border-[#E8EAE8]/60 text-center">
        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-[#f6f7f6] rounded-full flex items-center justify-center text-[#4A7043]">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-[#4A7043] text-[28px] font-bold mb-2 tracking-tight">
          Verifikasi Email
        </h1>
        <p class="text-[#5E6460] text-[15px] mb-8">
            Kode verifikasi telah dikirim ke <br>
            <span class="font-bold">{{ form.email }}</span>
        </p>

        <!-- Developer Mode OTP display -->
        <!-- <div v-if="localOtp" class="mb-6 p-4 bg-[#F0F7F0] border border-[#D5ECD5] rounded-2xl text-left text-[#3D5E37] text-[14px] shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-base">🛠️</span>
                <span class="font-bold">Mode Developer</span>
            </div>
            <p>Kode verifikasi Anda adalah <span class="font-mono font-bold text-lg select-all bg-[#D5ECD5] px-2 py-0.5 rounded text-[#22401C]">{{ localOtp }}</span> (muncul karena APP_ENV=local).</p>
        </div> -->

        <div class="flex justify-between gap-2 mb-8">
            <input 
                v-for="(digit, index) in 6" 
                :key="index"
                :id="'otp-' + index"
                v-model="otp[index]"
                @input="handleOtpInput($event, index)"
                @keydown.backspace="handleOtpBackspace($event, index)"
                type="text" 
                maxlength="1" 
                class="w-[50px] h-[60px] text-center text-2xl font-bold bg-[#eff1ef] rounded-xl outline-none border-2 border-transparent focus:border-[#4A7043] focus:bg-white transition-all text-[#4A7043]"
                autocomplete="off"
            />
        </div>

        <button 
            @click="verifyOtp" 
            :disabled="isLoading || !isOtpComplete"
            class="w-full h-[52px] bg-[#4A7043] hover:bg-[#3d5e37] active:scale-[0.99] disabled:opacity-60 text-white font-bold text-[17px] rounded-xl transition-all mb-6 shadow-sm flex items-center justify-center gap-2"
        >
            <svg v-if="!isLoading" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"></path>
            </svg>
            {{ isLoading ? 'Memverifikasi...' : 'Verifikasi' }}
        </button>

        <p v-if="serverError" class="text-red-500 text-[14px] mb-6">{{ serverError }}</p>

        <div class="text-[14px] text-[#5E6460]">
            Tidak menerima kode? <br>
            <button 
                @click="resendOtp" 
                :disabled="isLoading || countdown > 0"
                class="text-[#8ba783] hover:text-[#4A7043] font-bold mt-2 disabled:opacity-50 transition-colors bg-transparent border-none cursor-pointer"
            >
                {{ countdown > 0 ? `Kirim Ulang Kode (${countdown}s)` : 'Kirim Ulang Kode' }}
            </button>
        </div>

        <div class="mt-10 p-4 bg-[#f6f7f6] rounded-2xl flex items-start gap-3 text-left">
            <span class="text-lg">💡</span>
            <p class="text-[12px] text-[#73A36B] leading-relaxed">
                <span class="font-bold">Tips:</span> Periksa folder spam/junk jika tidak menemukan email verifikasi
            </p>
        </div>
      </div>
    </main>
  </div>
  <Footer />
</template>

<script setup>
import Footer from '@/components/public/Footer.vue'
import { ref, computed, inject, onUnmounted, nextTick } from 'vue'
import { useRouter } from "vue-router";
import { redirectLogin } from "@/utils";

redirectLogin()

const router = useRouter()
const axios = inject('axios')

const step = ref(1)
const otp = ref(['', '', '', '', '', ''])
const countdown = ref(0)
let timer = null

const form = ref({
    username: '',
    email: '',
    password: '',
    confirmPassword: '',
    nama: '',
})

const showPassword = ref(false)
const showConfirm = ref(false)
const isLoading = ref(false)
const serverError = ref('')
const emailError = ref('')
const localOtp = ref('')

const passwordMismatch = computed(() => {
  return form.value.password && form.value.confirmPassword && form.value.password !== form.value.confirmPassword;
})

const isOtpComplete = computed(() => {
    return otp.value.every(digit => digit !== '')
})

const handleBack = () => {
    if (step.value === 1) {
        router.push('/choose-role')
    } else {
        step.value = 1
        otp.value = ['', '', '', '', '', '']
        serverError.value = ''
    }
}

const handleOtpInput = (e, index) => {
    const val = e.target.value
    if (!/^\d$/.test(val)) {
        otp.value[index] = ''
        return
    }

    if (val && index < 5) {
        document.getElementById(`otp-${index + 1}`).focus()
    }
}

const handleOtpBackspace = (e, index) => {
    if (!otp.value[index] && index > 0) {
        document.getElementById(`otp-${index - 1}`).focus()
    }
}

const startCountdown = () => {
    countdown.value = 60
    if (timer) clearInterval(timer)
    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--
        } else {
            clearInterval(timer)
        }
    }, 1000)
}

const checkEmail = async () => {
    if (!form.value.email) {
        emailError.value = '';
        return;
    }
    
    try {
        const res = await axios.post('/api/check-email', {
            email: form.value.email
        });
        
        if (res.data.used) {
            emailError.value = 'Email sudah terpakai';
        } else {
            emailError.value = '';
        }
    } catch (err) {
        console.error('Error checking email', err);
    }
}

const register = async () => {
    if (passwordMismatch.value || emailError.value) return;
    
    isLoading.value = true;
    serverError.value = '';
    
    try {
        await axios.get('/sanctum/csrf-cookie');
        
        const res = await axios.post('/api/register-nasabah', {
            username: form.value.username,
            nama: form.value.nama,
            email: form.value.email,
            password: form.value.password
        })

        if (res.data && res.data.data && res.data.data.otp) {
            localOtp.value = res.data.data.otp
        }

        step.value = 2
        startCountdown()
        // Wait for next tick to focus first OTP input
        nextTick(() => {
            document.getElementById('otp-0')?.focus()
        })
    } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
            serverError.value = err.response.data.message;
            if (err.response.data.errors) {
                const errors = err.response.data.errors;
                const firstError = Object.values(errors)[0][0];
                serverError.value = firstError;
            }
        } else {
            serverError.value = 'Terjadi kesalahan saat mendaftar.';
        }
    } finally {
        isLoading.value = false;
    }
}

const verifyOtp = async () => {
    isLoading.value = true
    serverError.value = ''

    try {
        await axios.post('/api/verify-otp', {
            email: form.value.email,
            otp: otp.value.join('')
        })

        alert("Email berhasil diverifikasi! Silakan login.")
        router.push('/login')
    } catch (err) {
        serverError.value = err.response?.data?.message || 'Kode verifikasi salah atau kedaluwarsa.'
    } finally {
        isLoading.value = false
    }
}

const resendOtp = async () => {
    isLoading.value = true
    serverError.value = ''

    try {
        const res = await axios.post('/api/resend-otp', {
            email: form.value.email
        })
        startCountdown()
        if (res.data && res.data.otp) {
            localOtp.value = res.data.otp
        }
        alert("Kode baru telah dikirim!")
    } catch (err) {
        serverError.value = err.response?.data?.message || 'Gagal mengirim ulang kode.'
    } finally {
        isLoading.value = false
    }
}

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>