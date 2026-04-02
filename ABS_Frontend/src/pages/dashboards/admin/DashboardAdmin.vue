<template>
  <h1>Dashboard Admin</h1>

  <RouterLink to="/dashboard-admin/buat-petugas">Buat Petugas</RouterLink>
  <br />

  <RouterLink to="/dashboard-admin/kelola-petugas">Kelola Petugas</RouterLink>
  <br />
  
  <RouterLink to="/dashboard-admin/kelola-gudang">Kelola Gudang</RouterLink>
  <br />
  
  <RouterLink to="/dashboard-admin/kelola-sampah">Kelola Sampah</RouterLink>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('admin')
</script>
