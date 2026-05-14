<script setup>
import { ref } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import axios from 'axios'

const token = sessionStorage.getItem('token')

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const date = ref({
  start_date: null,
  end_date: null,
})

const downloadExcel = async () => {
  try {
    const res = await axios.get(
      `/api/cetak-laporan/excel`,
      {
        headers,
        params: {
          start_date: date.value.start_date,
          end_date: date.value.end_date
        },
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

const previewPdf = async () => {
  try {
    const response = await axios.get(
      `/api/cetak-laporan/pdf`,
      {
        headers,
        responseType: 'blob', // penting
      }
    )

    const file = new Blob([response.data], { type: 'application/pdf' })
    const fileURL = URL.createObjectURL(file)

    window.open(fileURL)
  } catch (err) {
    console.error(err)
  }
}
</script>

<template>
  <DashboardLayout title="Laporan Harian">
    <div class="bg-white rounded-[2.5rem] p-12 shadow-sm border border-stone-100 flex flex-col items-center justify-center text-center">
      <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center mb-6">
        <Icon icon="material-symbols:description-outline" class="w-10 h-10 text-stone-300" />
      </div>
      <h2 class="text-2xl font-black text-stone-800 mb-2">Laporan Harian</h2>
      <!-- <p class="text-stone-400 font-medium max-w-xs">Halaman ini sedang dalam tahap pengembangan. Segera Anda dapat melihat laporan harian di sini.</p> -->
      <div>
        <div class="flex gap-16">
          <div>
            <label>Dari Tanggal</label><br />
            <input type="date" v-model="date.start_date" />
          </div>
          
          <div>
            <label>Sampai Tanggal</label><br />
            <input type="date" v-model="date.end_date" />
          </div>
        </div>

        <button @click="downloadExcel()" class="border-2 p-2">Cetak Laporan Excel</button><br />
        <button @click="previewPdf()" class="border-2 p-2">Cetak Laporan Pdf</button>
      </div>
    </div>
  </DashboardLayout>
</template>
