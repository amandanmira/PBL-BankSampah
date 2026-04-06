<template>
  <h1>Dashboard Admin</h1>

  <RouterLink to="/dashboard-admin/buat-petugas">Buat Petugas</RouterLink>
  <br />

  <RouterLink to="/dashboard-admin/buat-manager">Buat Manager</RouterLink>
  <br />

  <RouterLink to="/dashboard-admin/kelola-users">Kelola User</RouterLink>
  <br />
  
  <RouterLink to="/dashboard-admin/kelola-gudang">Kelola Gudang</RouterLink>
  <br />
  
  <RouterLink to="/dashboard-admin/kelola-sampah">Kelola Sampah</RouterLink>
  <br />

  <RouterLink to="/dashboard-admin/verifikasi-pengepul">Verifikasi Pengepul</RouterLink>
  <br />

  <RouterLink to="/dashboard-admin/konfigurasi-web">Konfigurasi Web</RouterLink>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('admin')
</script>
