<script setup>
import { ref, onMounted } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";
import { useRouter } from "vue-router";

checkRole("admin");

const router = useRouter();
const gudangList = ref([])

const form = ref({
  nama: "",
  username: "",
  email: "",
  password: "",
  no_telp: "",
  gudang_id: "",
});

const loading = ref(false);

const token = sessionStorage.getItem("token");
if (!token) {
  throw new Error("Otentikasi diperlukan.");
}

const headers = { Authorization: `Bearer ${token}` };

const fetchGudang = async () => {
  try {
    const res = await axios.get('/api/admin/gudang', { headers })
    gudangList.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

onMounted(() => {
  fetchGudang()
})

const register = async () => {
  loading.value = true;
  try {

    const res = await axios.post("/api/admin/buatPetugas", form.value, { headers });

    alert("Buat akun berhasil");
    router.push("/dashboard-admin");
  } catch (err) {
    console.log(err.response.data);
    alert("Terjadi kesalahan saat membuat akun.");
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div>
    <h2>Buat Akun Petugas</h2>

    <input v-model="form.nama" placeholder="Nama" />
    <br />

    <input v-model="form.no_telp" placeholder="No Telp" />
    <br />

    <input v-model="form.email" placeholder="Email" />
    <br />

    <input type="password" v-model="form.password" placeholder="Password" />
    <br />

    <input v-model="form.username" placeholder="Username" />
    <br />

    <select v-model="form.gudang_id">
      <option value="">Pilih Gudang</option>
      <option v-for="gudang in gudangList" :key="gudang.gudang_id" :value="gudang.gudang_id">
        {{ gudang.alamat }}
      </option>
    </select>
    <br />

    <button @click="register" :disabled="loading">
      {{ loading ? "Membuat Akun..." : "Buat Akun" }}
    </button>
  </div>
</template>
