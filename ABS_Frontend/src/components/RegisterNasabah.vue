<script setup>
import { ref } from 'vue'
import axios from 'axios'

const username = ref('')
const email = ref('')
const password = ref('')
const nama_nasabah = ref('')

const register = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        
        const res = await axios.post('/register', {
            username: username.value,
            email: email.value,
            password: password.value,
            nama_nasabah: nama_nasabah.value
        })

        console.log(res.data)
        alert("Registrasi berhasil")

    } catch (err) {
        console.log(err.response.data)
    }
}
</script>

<template>
<div>

<h2>Register</h2>

<input v-model="username" placeholder="Username" />
<br>

<input v-model="email" placeholder="Email" />
<br>

<input type="password" v-model="password" placeholder="Password" />
<br>

<input v-model="nama_nasabah" placeholder="Nama" />
<br>

<button @click="register">Register</button>

</div>
</template>