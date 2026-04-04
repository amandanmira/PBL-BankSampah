<template>
  <h1>Dashboard Pengepul</h1>

  <RouterLink to="/dashboard-pengepul/edit-profile">Edit Profile</RouterLink>
</template>

<script setup>
import { checkRole } from '@/utils'

checkRole('pengepul')
</script>
