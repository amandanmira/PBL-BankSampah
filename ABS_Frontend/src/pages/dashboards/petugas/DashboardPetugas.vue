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
  <button @click="downloadExcel()">Cetak Laporan Excel</button>
</template>

<script setup>
import { checkRole } from '@/utils'
import axios from 'axios'

checkRole('petugas')

const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const downloadExcel = async () => {
  try {
    const res = await axios.get(
      `/api/petugas/cetak-laporan/excel`,
      {
        headers,
        responseType: 'blob', // penting
      }
    )

    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'transaksi.xlsx') // nama file
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (err) {
    console.error(err)
  }
}
</script>
