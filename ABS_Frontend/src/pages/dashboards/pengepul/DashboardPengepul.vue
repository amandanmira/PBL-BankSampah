<template>
  <h1>Dashboard Pengepul</h1>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('pengepul')
</script>
