<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from "vue-router";

const router = useRouter()

const form = ref({
    username: '',
    email: '',
    password: '',
    nama: '',
})

const register = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        
        const res = await axios.post('/api/register-nasabah', form.value)

        alert("Registrasi berhasil")
        router.push('/login')
    } catch (err) {
        console.log(err.response.data)
    }
}
</script>

<template>
<div>

<h2>Register</h2>

<input v-model="form.username" placeholder="Username" />
<br>

<input v-model="form.email" placeholder="Email" />
<br>

<input type="password" v-model="form.password" placeholder="Password" />
<br>

<input v-model="form.nama" placeholder="Nama" />
<br>

<button @click="register">Register</button>

</div>
</template>