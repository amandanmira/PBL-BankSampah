import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'

import App from './App.vue'
import router from './router'

// Set alamat dasar ke Laravel-mu
axios.defaults.baseURL = 'http://localhost:8000';
// INI WAJIB TRUE! Agar browser mau mengirim/menerima Cookie Sanctum
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.provide('axios', axios)
app.mount('#app')
