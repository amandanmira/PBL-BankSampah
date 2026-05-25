<template>
	<div class="container">
		<h2>Riwayat Pembelian</h2>

		<!-- tombol create -->
		<button @click="router.push('/dashboard-pengepul/request-pembelian/create')">+ Tambah Pembelian</button>
		<button @click="downloadExcel()">Download Laporan Excel</button>

		<table border="1" cellpadding="8" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>Status</th>
					<th>Deadline</th>
					<th>Detail</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="transaksi in transaksiList" :key="transaksi.transaksi_id">
					<td>{{ transaksi.transaksi_id }}</td>
					<td>{{ transaksi.status }}</td>
					<td>{{ transaksi.deadline ?? '-' }}</td>
					<td>
						<ul>
							<li v-for="d in transaksi.detail_transaksi" :key="d.detail_id">
								{{ d.sampah.item_sampah.nama }}
							</li>
						</ul>
					</td>
					<td>
						<button v-if="transaksi.status == 'proses'"
							@click="router.push(`/dashboard-pengepul/request-pembelian/${transaksi.transaksi_id}`)">Update</button>
						<button @click="previewPdf(transaksi.transaksi_id)">Cetak PDF</button>

					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('pengepul')

const router = useRouter()
const transaksiList = ref([])

const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

// ambil data
const fetchTransaksi = async () => {
	try {
		const res = await axios.get(`/api/pengepul/request-pembelian/${user.pengepul_id}`, { headers })
		transaksiList.value = res.data.data
	} catch (err) {
		console.error(err)
	}
}

const downloadExcel = async () => {
	try {
		const res = await axios.get(
			`/api/pengepul/export-pembelian/excel/${user.pengepul_id}`,
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

const previewPdf = async (id) => {
	try {
		const response = await axios.get(
			`/api/pengepul/export-pembelian/pdf/${id}`,
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

// lifecycle
onMounted(() => {
	fetchTransaksi()
})
</script>

<style scoped>
.container {
	padding: 20px;
}

button {
	margin: 2px;
	cursor: pointer;
}
</style>
