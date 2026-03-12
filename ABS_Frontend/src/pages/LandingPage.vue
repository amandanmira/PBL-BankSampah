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
</template>

<script setup>
const role = sessionStorage.getItem('role')
const user = sessionStorage.getItem('user')
</script>
