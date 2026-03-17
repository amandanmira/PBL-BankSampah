<script setup>
import { ref } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";
import { useRouter } from "vue-router";

checkRole("admin");

const router = useRouter();

const form = ref({
  nama: "",
  username: "",
  email: "",
  password: "",
});

const loading = ref(false);

const register = async () => {
  loading.value = true;
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }

    const headers = { Authorization: `Bearer ${token}` };

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

    <input v-model="form.email" placeholder="Email" />
    <br />

    <input type="password" v-model="form.password" placeholder="Password" />
    <br />

    <input v-model="form.username" placeholder="Username" />
    <br />

    <button @click="register" :disabled="loading">
      {{ loading ? "Membuat Akun..." : "Buat Akun" }}
    </button>
  </div>
</template>
