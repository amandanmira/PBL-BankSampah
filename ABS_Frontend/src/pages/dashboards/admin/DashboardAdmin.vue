<template>
  <h1>Dashboard Admin</h1>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('admin')
</script>
