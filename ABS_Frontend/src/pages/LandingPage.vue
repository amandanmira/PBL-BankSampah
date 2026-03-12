<template>
  <h1>Landing Page</h1>

  <div v-if="user">
    <p>Welcome {{ user.nama }}</p>
    <p>Role: {{ role }}</p>
  </div>

  <div v-else>
    <p>User belum login</p>
  </div>

  <RouterLink to="/login">Login</RouterLink>
  <br />

  <RouterLink to="/register-nasabah">Register Nasabah</RouterLink>
  <br />

  <RouterLink to="/register-pengepul">Register Pengepul</RouterLink>
</template>

<script setup>
const role = sessionStorage.getItem('role')
const user = JSON.parse(sessionStorage.getItem('user'))
</script>
