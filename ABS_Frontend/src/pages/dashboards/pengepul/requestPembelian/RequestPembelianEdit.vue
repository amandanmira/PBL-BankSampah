<template>
	<div class="container">
		<h2>Edit Gudang</h2>

		<div v-if="loading">Loading...</div>

		<form v-else @submit.prevent="submitForm">
			<div>
				<label>Status</label><br />
				<input type="text" :value="form.status" readonly />
			</div>
			<div>
				<label>Deadline</label><br />
				<input type="text" :value="form.deadline" readonly />
			</div>
			<div>
				<label>Bukti Transfer</label>
				<input type="file" @change="handleFile" />

				<!-- preview -->
				<div v-if="preview">
					<img :src="preview" width="120" />
				</div>
			</div>

			<br />

			<button type="submit">Update</button>
			<button type="button" @click="goBack">Batal</button>
		</form>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('pengepul')

const route = useRoute()
const router = useRouter()

const form = ref({
	status: '',
	deadline: '',
	bukti_transfer: null,
})
const buktiTf = ref(null)
const preview = ref(null);

const errors = ref({})
const loading = ref(true)
const token = sessionStorage.getItem('token')

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const id = route.params.id

// ambil data awal
const fetchTransaksi = async () => {
	try {
		const res = await axios.get(`/api/pengepul/request-pembelian/${id}`, { headers })
		form.value = res.data

		if (form.value.bukti_transfer) {
			preview.value = `http://localhost:8000/storage/${form.value.bukti_transfer}`;
		}

		if (form.value.status !== 'proses') {
			alert('Tunggu sampai request diterima!')
			goBack()
		}
	} catch (err) {
		console.error(err)
	} finally {
		loading.value = false
	}
}

onMounted(() => {
	fetchTransaksi()
})

const handleFile = (e) => {
	const file = e.target.files[0];
	buktiTf.value = file;

	if (file) {
		preview.value = URL.createObjectURL(file);
	}
};

// submit update
const submitForm = async () => {
	errors.value = {}

	try {
		const formData = new FormData();

		if (buktiTf.value) {
			formData.append("bukti_transfer", buktiTf.value);
		}

		const headers = {
			'Authorization': `Bearer ${token}`,
			"Content-Type": "multipart/form-data"
		}

		await axios.post(`/api/pengepul/request-pembelian/${id}?_method=PUT`, formData, { headers })
		router.push('/dashboard-pengepul/request-pembelian')
	} catch (err) {
		if (err.response && err.response.status === 422) {
			errors.value = err.response.data.errors
		} else {
			console.error(err)
		}
	}
}

const goBack = () => {
	router.push('/dashboard-pengepul/request-pembelian')
}
</script>

<style scoped>
.container {
	padding: 20px;
}

textarea {
	width: 300px;
	height: 100px;
}

button {
	margin: 5px;
	cursor: pointer;
}

.error {
	color: red;
	font-size: 12px;
}
</style>
