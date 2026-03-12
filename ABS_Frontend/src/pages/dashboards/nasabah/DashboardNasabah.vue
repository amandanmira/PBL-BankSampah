<template>
  <h1>Dashboard Nasabah</h1>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('nasabah')
</script>
