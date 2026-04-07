<template>
	<div class="container">
		<h2>Riwayat Pembelian</h2>

		<!-- tombol create -->
		<button @click="router.push('/dashboard-pengepul/request-pembelian/create')">+ Tambah Pembelian</button>

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
						<p v-else>-</p>
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

// ambil data
const fetchTransaksi = async () => {
	try {
		const token = sessionStorage.getItem('token')
		const user = JSON.parse(sessionStorage.getItem('user'))

		if (!token) {
			throw new Error('Otentikasi diperlukan.')
		}

		const headers = { 'Authorization': `Bearer ${token}` }

		const res = await axios.get(`/api/pengepul/request-pembelian/${user.pengepul_id}`, { headers })
		transaksiList.value = res.data
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
