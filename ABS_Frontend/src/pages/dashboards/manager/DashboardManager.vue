<template>
  <h1>Dashboard Manager</h1>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('manager')
</script>
