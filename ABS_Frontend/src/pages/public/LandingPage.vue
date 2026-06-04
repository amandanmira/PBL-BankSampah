<script setup>
import Blog from '@/components/public/Blog.vue';
import CallToAction from '@/components/public/CallToAction.vue';
import Feature from '@/components/public/Feature.vue';
import Hero from '@/components/public/Hero.vue';
import HowItWorks from '@/components/public/HowItWorks.vue';
import TrashPrice from '@/components/public/TrashPrice.vue';
import axios from 'axios'
import { useRouter } from "vue-router";

const router = useRouter()

const role = sessionStorage.getItem('role') || localStorage.getItem('role')
const user = JSON.parse(sessionStorage.getItem('user') || localStorage.getItem('user') || 'null')

const logout = async () => {
  try {
    const token = sessionStorage.getItem("token") || localStorage.getItem("token")
    const headers = { 'Authorization': `Bearer ${token}` }

    const res = await axios.post("/api/logout", {}, { headers })
    sessionStorage.clear()
    localStorage.clear()
    router.push("/login")
  } catch (error) {
    console.log(error.response)
  }
}
</script>


<template>
  <Hero />
  <TrashPrice />

  <div id="layanan">
    <Feature />
  </div>


  <div id="cara-kerja">
    <HowItWorks />
  </div>

  <Blog />
  <CallToAction />

  <!-- <h1>Landing Page</h1>

  <div v-if="user">
    <p>Welcome {{ user.nama }}</p>
    <p>Role: {{ role }}</p>
    <button @click="logout">Logout</button>
  </div>

  <div v-else>
    <p>User belum login</p>
    <RouterLink to="/login">Login</RouterLink>
    <br />

    <RouterLink to="/register-nasabah">Register Nasabah</RouterLink>
    <br />

    <RouterLink to="/register-pengepul">Register Pengepul</RouterLink>
  </div> -->
</template>