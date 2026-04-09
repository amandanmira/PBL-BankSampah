<template>
	<div class="container">
		<h2>Request Penjemputan</h2>

		<form @submit.prevent="updateData">
			<div>
				<label>Foto</label>
				<input type="file" @change="handleFile" />

				<!-- preview -->
				<div v-if="preview">
					<img :src="preview" width="120" />
				</div>
			</div>

			<div>
				<label>Deskripsi</label>
				<textarea v-model="form.deskripsi"></textarea>
			</div>

			<div>
				<label>Alamat</label>
				<textarea v-model="form.alamat"></textarea>
			</div>

			<div>
				<label>Gudang</label>
				<select v-model="form.gudang_id">
					<option value="">Pilih Gudang</option>
					<option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id">
						{{ gudang.alamat }}
					</option>
				</select>
			</div>

			<button type="submit">Simpan</button>
		</form>

		<button @click="router.push('/dashboard-nasabah')">
			Kembali
		</button>
	</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('nasabah')

const router = useRouter()

const form = ref({
	foto: "",
	deskripsi: "",
	alamat: "",
	nasabah_id: "",
	gudang_id: "",
});

const logoFile = ref(null);
const preview = ref(null);
const loading = ref(false);
const gudangList = ref([])

const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

if (user.alamat !== null && user.alamat !== '') {
	form.value.alamat = user.alamat
}

// handle file input
const handleFile = (e) => {
	const file = e.target.files[0];
	logoFile.value = file;

	if (file) {
		preview.value = URL.createObjectURL(file);
	}
};

const fetchGudang = async () => {
	try {
		const headers = {
			'Authorization': `Bearer ${token}`
		}

		const res = await axios.get('/api/nasabah/list-gudang', { headers })
		gudangList.value = res.data.data
	} catch (err) {
		console.error(err)
	}
}

onMounted(() => {
	fetchGudang()
})

// submit update
const updateData = async () => {
	try {
		loading.value = true;

		const formData = new FormData();

		Object.keys(form.value).forEach((key) => {
			if (form.value[key] !== null && form.value[key] !== "") {
				formData.append(key, form.value[key]);
			}
		});

		if (logoFile.value) {
			formData.append("foto", logoFile.value);
		}

		formData.append("nasabah_id", user.nasabah_id);

		const headers = {
			'Authorization': `Bearer ${token}`,
			"Content-Type": "multipart/form-data"
		}

		const res = await axios.post("/api/nasabah/request-penjemputan", formData, { headers });

		alert("Berhasil Request");
		router.push('/dashboard-nasabah')
	} catch (err) {
		console.error(err);
	} finally {
		loading.value = false;
	}
};
</script>
