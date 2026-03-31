<template>
	<div class="container">
		<h2>Konfigurasi Web</h2>

		<form @submit.prevent="updateData">
			<div>
				<label>Logo</label>
				<input type="file" @change="handleFile" />

				<!-- preview -->
				<div v-if="preview">
					<img :src="preview" width="120" />
				</div>
			</div>

			<div>
				<label>Quote</label>
				<input type="text" v-model="form.quote" />
			</div>

			<div>
				<label>Instagram</label>
				<input type="text" v-model="form.instagram" />
			</div>
			<div>
				<label>Facebook</label>
				<input type="text" v-model="form.facebook" />
			</div>
			<div>
				<label>Linkedin</label>
				<input type="text" v-model="form.linkedin" />
			</div>
			<div>
				<label>Youtube</label>
				<input type="text" v-model="form.youtube" />
			</div>

			<div>
				<label>Lama Deadline</label><br />
				<input v-model="form.lama_deadline" type="number" />
			</div>

			<div>
				<label>Email</label>
				<input type="email" v-model="form.email" />
			</div>

			<div>
				<label>No Telp</label>
				<input type="text" v-model="form.no_telp" />
			</div>

			<div>
				<label>Alamat</label>
				<textarea v-model="form.alamat"></textarea>
			</div>

			<div>
				<label>Tentang</label>
				<textarea v-model="form.tentang"></textarea>
			</div>

			<button type="submit">Simpan</button>
		</form>

		<button @click="router.push('/dashboard-admin')">
			Kembali
		</button>
	</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('admin')

const route = useRoute()
const router = useRouter()

const form = ref({
	quote: "",
	instagram: "",
	facebook: "",
	linkedin: "",
	youtube: "",
	no_telp: "",
	email: "",
	lama_deadline: "",
	alamat: "",
	tentang: ""
});

const logoFile = ref(null);
const preview = ref(null);
const loading = ref(false);

const token = sessionStorage.getItem('token')

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

// ambil data awal
const getData = async () => {
	try {
		loading.value = true;

		const headers = { 'Authorization': `Bearer ${token}` }
		const res = await axios.get("/api/admin/web-config", { headers });
		const data = res.data;

		form.value = {
			...form.value,
			...data
		};

		if (data.logo) {
			preview.value = `http://localhost:8000/storage/${data.logo}`;
			console.log(preview.value)
		}
	} catch (err) {
		console.error(err);
	} finally {
		loading.value = false;
	}
};

// handle file input
const handleFile = (e) => {
	const file = e.target.files[0];
	logoFile.value = file;

	if (file) {
		preview.value = URL.createObjectURL(file);
	}
};

// submit update
const updateData = async () => {
	try {
		loading.value = true;

		const formData = new FormData();

		Object.keys(form.value).forEach((key) => {
			if (form.value[key] !== null && form.value[key] !== "" && key !== "logo") {
				formData.append(key, form.value[key]);
			}
		});

		if (logoFile.value) {
			formData.append("logo", logoFile.value);
		}

		const headers = {
			'Authorization': `Bearer ${token}`,
			"Content-Type": "multipart/form-data"
		}

		const res = await axios.post("/api/admin/web-config?_method=PUT", formData, { headers });

		console.log(res)

		alert("Berhasil update");
	} catch (err) {
		console.error(err);
	} finally {
		loading.value = false;
	}
};

onMounted(() => {
	getData();
});
</script>
