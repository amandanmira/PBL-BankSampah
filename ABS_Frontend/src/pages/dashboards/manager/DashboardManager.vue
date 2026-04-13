<template>
  <h1>Dashboard Manager</h1>

  <RouterLink to="/dashboard-manager/riwayat-penjemputan">Riwayat Penjemputan</RouterLink>
  <br />
  <RouterLink to="/dashboard-manager/riwayat-penarikan">Riwayat Penarikan</RouterLink>
  <br />
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('manager')
</script>
