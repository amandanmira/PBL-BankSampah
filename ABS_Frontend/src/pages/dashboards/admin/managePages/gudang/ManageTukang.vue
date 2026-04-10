<template>
	<div class="container">
		<h2>Manage Tukang</h2>

		<button @click="addSampah">+ Tambah Tukang</button>

		<form @submit.prevent="submit">
			<div v-for="(t, index) in gudang.tukang" :key="t.tukang_id || index"
				style="border:1px solid #ccc; padding:10px; margin-top:10px;">
				<div>
					<label>Foto</label>
					<input type="file" @change="handleFile($event, index)" />

					<!-- preview -->
					<div>
						<img v-if="t.foto || t.foto_baru" :src="previewFile(t)" width="120" />
					</div>
				</div>

				<div>
					<label>Nama</label><br />
					<input v-model="t.nama" />
				</div>

				<div>
					<label>No Telepon</label><br />
					<input v-model="t.no_telp" />
				</div>

				<div>
					<input v-model="t.checkbox" type="checkbox" /> Tukang Aktif
				</div>

				<button v-if="t.active === null" @click="removeSampah(index)">
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

const token = sessionStorage.getItem('token')

if (!token) {
	throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }
const id = route.params.id

const addSampah = () => {
	gudang.value.tukang.push({
		nama: "",
		no_telp: "",
		foto_baru: "",
		active: null,
		checkbox: true,
	});
};

const removeSampah = async (index) => {
	gudang.value.tukang.splice(index, 1);
};

// ambil data berdasarkan id
const fetchGudang = async () => {
	try {
		const res = await axios.get(`/api/admin/gudang/${id}`, { headers })
		gudang.value = res.data

		for (const item of gudang.value.tukang) {
			item.checkbox = item.active === 1;
		}
	} catch (err) {
		console.error(err)
	}
}

onMounted(() => {
	fetchGudang()
})

const handleFile = (e, index) => {
	const file = e.target.files[0];
	gudang.value.tukang[index].foto_baru = file;
};

const previewFile = (k) => {
	if (k.foto_baru) {
		return URL.createObjectURL(k.foto_baru);
	}
	return `http://localhost:8000/storage/${k.foto}`;
};

const submit = async () => {
	const formData = new FormData();

	let i = 0;
	for (const k of gudang.value.tukang) {
		if (k.tukang_id) {
			formData.append(`tukang[${i}][tukang_id]`, k.tukang_id);
		}

		formData.append(`tukang[${i}][nama]`, k.nama);
		formData.append(`tukang[${i}][no_telp]`, k.no_telp);
		formData.append(`tukang[${i}][active]`, k.checkbox ? 1 : 0);

		// hanya kirim kalau ada file baru
		if (k.foto_baru) {
			formData.append(`tukang[${i}][foto]`, k.foto_baru);
		}

		i++;
	};

	try {
		await axios.post(`/api/admin/tukang/${id}?_method=PUT`, formData, { headers });
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
