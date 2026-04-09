<template>
  <div class="container">
    <h2>Edit Manager</h2>

    <div v-if="loading">Loading...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <form v-else @submit.prevent="submitForm">
      <div>
        <label for="nama">Nama</label><br />
        <input id="nama" v-model="form.nama" />
        <div v-if="errors.nama" class="error">{{ errors.nama[0] }}</div>
      </div>
      <div>
        <label for="username">Username</label><br />
        <input id="username" v-model="form.username" />
        <div v-if="errors.username" class="error">{{ errors.username[0] }}</div>
      </div>

      <br />

      <button type="submit">Update</button>
      <button type="button" @click="goBack">Batal</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter, useRoute } from "vue-router";
import { checkRole } from "@/utils";

checkRole("admin");

const router = useRouter();
const route = useRoute();

const form = ref({
  nama: "",
  username: "",
});

const error = ref(null);
const errors = ref({});
const loading = ref(true);
const token = sessionStorage.getItem("token");
const managerId = route.params.id;

if (!token) {
  router.push("/login");
  throw new Error("Otentikasi diperlukan.");
}

const headers = { Authorization: `Bearer ${token}` };

// ambil data awal
const fetchManager = async () => {
  try {
    const res = await axios.get(`/api/admin/manager/${managerId}`, { headers });
    const managerData = res.data.data;
    form.value.nama = managerData.nama;
    form.value.username = managerData.username;
  } catch (err) {
    error.value = "Gagal mengambil data manager.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchManager();
});

// submit update
const submitForm = async () => {
  errors.value = {};
  error.value = null;

  try {
    await axios.put(`/api/admin/edit-manager/${managerId}`, form.value, { headers });
    alert("Data petugas berhasil diperbarui!");
    router.push("/dashboard-admin/kelola-users");
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      error.value = "Terjadi kesalahan saat memperbarui data.";
      console.error(err);
    }
  }
};

const goBack = () => {
  router.push("/dashboard-admin/manager");
};
</script>

<style scoped>
.container {
  padding: 20px;
}

input {
  width: 300px;
  padding: 8px;
  margin-top: 4px;
}

button {
  margin: 10px 5px 0 0;
  cursor: pointer;
  padding: 8px 12px;
}

.error {
  color: red;
  font-size: 12px;
  margin-top: 4px;
}
</style>
