<template>
  <h1>Dashboard Nasabah</h1>

  <RouterLink to="/dashboard-nasabah/edit-profile">Edit Profile</RouterLink>
  <br />

  <RouterLink to="/dashboard-nasabah/request-penjemputan">Request Penjemputan</RouterLink>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('nasabah')
</script>
