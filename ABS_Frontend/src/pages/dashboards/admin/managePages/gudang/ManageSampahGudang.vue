<template>
	<div class="container">
		<h2>Manage Sampah</h2>

		<button @click="addSampah">+ Tambah Sampah</button>

		<form @submit.prevent="submit">
			<div v-for="(s, index) in gudang.sampah" :key="s.sampah_id || index"
				style="border:1px solid #ccc; padding:10px; margin-top:10px;">
				<div>
					<label>Kategori Sampah</label><br />
					<select v-model="s.kategori_id" @change="s.item_id = ''">
						<option value="">Pilih Kategori</option>
						<option v-for="sampah in itemSampahList" :key="sampah.kategori_id" :value="sampah.kategori_id">
							{{ sampah.nama }}
						</option>
					</select>
				</div>
				<div>
					<label>Item Sampah</label><br />
					<select v-model="s.item_id" :disabled="!s.kategori_id">
						<option value="">Pilih Sampah</option>
						<option v-for="sampah in getItems(s.kategori_id)" :key="sampah.item_id" :value="sampah.item_id">
							{{ sampah.nama }}
						</option>
					</select>
				</div>

				<div>
					<label>Stok</label><br />
					<input v-model="s.stok" type="number" step="0.01" />
				</div>

				<div>
					<input v-model="s.checkbox" type="checkbox" /> Sampah Aktif
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
		stok: 0,
		active: null,
		checkbox: true,
		kategori_id: '',
		item_id: '',
	});
};

const removeSampah = async (index) => {
	gudang.value.sampah.splice(index, 1);
};

const getItems = (kategori_id) => {
	const kategori = itemSampahList.value.find(k => k.kategori_id === kategori_id)
	return kategori ? kategori.item_sampah : []
}

// ambil data berdasarkan id
const fetchGudang = async () => {
	try {
		const res = await axios.get(`/api/admin/gudang/${id}`, { headers })
		gudang.value = res.data

		for (const item of gudang.value.sampah) {
			item.checkbox = item.active === 1;
			item.kategori_id = item.item_sampah.kategori_sampah.kategori_id
		}

		const resSampah = await axios.get(`/api/admin/kategori-sampah`, { headers })
		//itemSampahList.value = resSampah.data

		itemSampahList.value = resSampah.data
			.filter(k => k.active == 1)
			.map(k => ({
				...k,
				item_sampah: k.item_sampah.filter(i => i.active == 1)
			}))
		console.log(itemSampahList.value)
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
				active: s.checkbox ? 1 : 0,
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
