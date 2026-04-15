<template>
  <h1>Dashboard Petugas</h1>

  <RouterLink to="/dashboard-petugas/Kelola-berita">kelola Berita</RouterLink>
  <br />
  <RouterLink to="/dashboard-petugas/listpenjemputan">List Penjemputan</RouterLink>
  <br />
  <RouterLink to="/dashboard-petugas/riwayat-penjemputan">Riwayat Penjemputan</RouterLink>
  <br />
  <RouterLink to="/dashboard-petugas/riwayat-penarikan">Riwayat Penarikan</RouterLink>
  <br />
  <RouterLink to="/dashboard-petugas/listpenarikan">List Penarikan</RouterLink>
  <br />
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('petugas')
</script>
