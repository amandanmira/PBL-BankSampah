<template>
	<div class="container">
		<h2>Manage Sampah</h2>

		<button @click="addSampah">+ Tambah Sampah</button>

		<form @submit.prevent="submit">
			<div v-for="(s, index) in gudang.sampah" :key="s.sampah_id || index"
				style="border:1px solid #ccc; padding:10px; margin-top:10px;">
				<div>
					<label>Item Sampah</label><br />
					<select v-model="s.item_id">
						<option value="">Pilih Sampah</option>
						<option v-for="sampah in itemSampahList" :key="sampah.item_id" :value="sampah.item_id">
							{{ sampah.nama }}
						</option>
					</select>
				</div>

				<div>
					<label>Stok</label><br />
					<input v-model="s.stok" type="number" step="0.01" />
				</div>

				<button type="button" @click="removeSampah(index)">
					Hapus
				</button>
			</div>

			<button type="submit">Update</button>
		</form>

		<button @click="goBack">Kembali</button>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('admin')

const route = useRoute()
const router = useRouter()

const gudang = ref([])
const itemSampahList = ref([])

const token = sessionStorage.getItem('token')

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }
const id = route.params.id

const addSampah = () => {
	gudang.value.sampah.push({
		stok: null,
		kategori_id: null,
	});
};

const removeSampah = async (index) => {
	const s = gudang.value.sampah[index];

	// kalau sudah ada di database → hit API delete
	if (s.sampah_id) {
		if (!confirm("Hapus sampah ini?")) return;

		try {
			await axios.delete(`/api/admin/gudang/sampah/${s.sampah_id}`, { headers });
		} catch (err) {
			alert("Gagal hapus sampah");
			return;
		}
	}

	gudang.value.sampah.splice(index, 1);
};

// ambil data berdasarkan id
const fetchGudang = async () => {
	try {
		const res = await axios.get(`/api/admin/gudang/${id}`, { headers })
		gudang.value = res.data

		const resSampah = await axios.get(`/api/admin/item-sampah`, { headers })
		itemSampahList.value = resSampah.data
	} catch (err) {
		console.error(err)
	}
}

onMounted(() => {
	fetchGudang()
})

const submit = async () => {
	const payload = {
		sampah: gudang.value.sampah.map((s) => {
			const item = {
				stok: s.stok,
				item_id: s.item_id,
			};

			if (s.sampah_id) {
				item.sampah_id = s.sampah_id;
			}

			return item;
		}),
	};

	try {
		await axios.put(`/api/admin/gudang/sampah/${id}`, payload, { headers });
		fetchGudang()
	} catch (err) {
		console.error(err)
	}
};

const goBack = () => {
	router.push('/dashboard-admin/kelola-gudang')
}
</script>

<style scoped>
.container {
	padding: 20px;
}

button {
	margin-top: 10px;
	cursor: pointer;
}
</style>
