<template>
  <h1>Landing Page</h1>

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
  </div>
</template>

<script setup>
import axios from 'axios'
import { useRouter } from "vue-router";

const router = useRouter()

const role = sessionStorage.getItem('role')
const user = JSON.parse(sessionStorage.getItem('user'))

const logout = async () => {
  try {
    const token = sessionStorage.getItem("token")
    const headers = { 'Authorization': `Bearer ${token}` }

    const res = await axios.post("/api/logout", {}, {headers})
    sessionStorage.clear()
    router.push("/login")
  } catch (error) {
    console.log(error.response)
  }
}
</script>
