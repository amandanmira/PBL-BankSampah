import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'

import App from './App.vue'
import router from './router'

// Set alamat dasar ke Laravel-mu
// axios.defaults.baseURL = 'https://api.tabungansampah.online';

// alamat ke backend sekarang dari file .env
// jika belum punya .env copy dari .env.example dulu.
console.log(import.meta.env.VITE_API_BASE_URL);
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL;

// INI WAJIB TRUE! Agar browser mau mengirim/menerima Cookie Sanctum
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Interceptor untuk menambahkan token otomatis
axios.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.provide('axios', axios)
app.mount('#app')
