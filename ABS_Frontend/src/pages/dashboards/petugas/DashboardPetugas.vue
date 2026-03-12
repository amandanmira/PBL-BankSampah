<template>
  <h1>Dashboard Petugas</h1>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('petugas')
</script>
