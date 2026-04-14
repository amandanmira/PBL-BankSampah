<template>
	<div class="container">
		<h2>Request Pembelian</h2>

		<button @click="addSampah">+ Tambah Sampah</button>

		<div v-for="(d, index) in form.detail" :key="index" style="border:1px solid #ccc; padding:10px; margin-top:10px;">
			<div>
				<label>Kategori Sampah</label><br />
				<select v-model="d.kategori_id" @change="d.item_id = ''; d.sampah_id = ''">
					<option value="">Pilih Sampah</option>
					<option v-for="sampah in sampahList" :key="sampah.kategori_id" :value="sampah.kategori_id">
						{{ sampah.nama }}
					</option>
				</select>
			</div>

			<div>
				<label>Sampah</label><br />
				<select v-model="d.item_id" :disabled="!d.kategori_id" @change="d.sampah_id = ''">
					<option value="">Pilih Sampah</option>
					<option v-for="sampah in getItems(d.kategori_id)" :key="sampah.item_id" :value="sampah.item_id">
						{{ sampah.nama }}
					</option>
				</select>
			</div>

			<div>
				<label>Dari Gudang</label><br />
				<select v-model="d.sampah_id" :disabled="!d.item_id">
					<option disabled value="">Pilih gudang</option>
					<option v-for="s in getSampahByKategori(d.kategori_id, d.item_id)" :key="s.sampah_id"
						:value="s.sampah_id">
						{{ s.gudang.alamat }}
					</option>
				</select>
			</div>

			<div>
				<label>Berat</label><br />
				<input v-model="d.berat" type="number" />
			</div>

			<div>
				<label>Subtotal</label><br />
				<input type="text" :value="getSubtotal(d)" readonly />
			</div>
			<br />

			<button @click="removeSampah(index)">
				Hapus Sampah
			</button>
		</div>
		<br />

		<div>
			<label>Total Harga</label><br />
			<input type="text" :value="totalSemua" readonly />
		</div>

		<br />

		<button @click="submitForm">
			Kirim
		</button>

		<button @click="router.push('/dashboard-pengepul')">
			Batal
		</button>
	</div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('pengepul')

const router = useRouter()
const sampahList = ref([])

const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const form = reactive({
	pengepul_id: user.pengepul_id,
	detail: []
});

const addSampah = () => {
	form.detail.push({
		kategori_id: '',
		item_id: '',
		sampah_id: '',
		berat: 0,
		harga: 0,
	});
};

const removeSampah = (index) => {
	form.detail.splice(index, 1);
};

const getHarga = (kategori_id, item_id) => {
	const kategori = ref(sampahList.value.find(i => i.kategori_id === kategori_id))
	const item = kategori.value.item_sampah.find(i => i.item_id === item_id)
	return item ? item.harga_beli : 0
}

const getSubtotal = (item) => {
	if (item.kategori_id && item.item_id) {
		const subtotal = getHarga(item.kategori_id, item.item_id) * item.berat
		item.harga = subtotal
		return subtotal
	} else {
		return 0
	}
}

const getSampahByKategori = (kategori_id, item_id) => {
	const sampah = getItems(kategori_id).find(s => s.item_id === item_id)
	return sampah ? sampah.sampah : []
}

const totalSemua = computed(() => {
	return form.detail.reduce((total, item) => {
		return total + getSubtotal(item)
	}, 0)
})

// ambil data
const fetchGudang = async () => {
	try {
		const res = await axios.get('/api/pengepul/daftar-sampah', { headers })
		sampahList.value = res.data
	} catch (err) {
		console.error(err)
	}
}

const getItems = (kategori_id) => {
	const kategori = sampahList.value.find(k => k.kategori_id === kategori_id)
	return kategori ? kategori.item_sampah : []
}

// lifecycle
onMounted(() => {
	fetchGudang()
})

const submitForm = async () => {
	try {
		await axios.post('/api/pengepul/request-pembelian', form, { headers })
		router.push('/dashboard-pengepul')
	} catch (err) {
		console.error(err)
	}
}
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
