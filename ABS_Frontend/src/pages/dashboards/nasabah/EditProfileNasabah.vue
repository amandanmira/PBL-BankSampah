<template>
  <div class="container">
    <h2>Edit Profile</h2>

    <div v-if="loading">Loading...</div>

    <form v-else>
      <div>
        <label>Nama</label><br />
        <input v-model="form.nama"></input>
      </div>
      <div>
        <label>Username</label><br />
        <input v-model="form.username"></input>
      </div>
      <div>
        <label>No Telp</label><br />
        <input v-model="form.no_telp"></input>
      </div>
      <div>
        <label>Alamat</label><br />
        <textarea v-model="form.alamat"></textarea>
      </div>
      <div>
        <label>GMap</label><br />
        <div>
          <button type="button" @click="getLocation">Ambil Lokasi</button>

          <div id="map" style="height: 400px;"></div>

          <p v-if="latitude && longitude">
            Latitude: {{ latitude }} <br />
            Longitude: {{ longitude }}
          </p>

          <p v-if="error">{{ error }}</p>
        </div>
      </div>
      <div>
        <label>Nama Bank</label><br />
        <input v-model="form.nama_bank"></input>
      </div>
      <div>
        <label>No Rekening</label><br />
        <input v-model="form.no_rekening"></input>
      </div>

      <br />

      <button type="button" @click="submitForm">Update</button>
      <button type="button" @click="goBack">Batal</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'
import L from "leaflet";
import "leaflet/dist/leaflet.css";

checkRole('nasabah')

const router = useRouter()

const form = ref({
  nama: '',
  username: '',
  no_telp: '',
  alamat: '',
  gmap: '',
  nama_bank: '',
  no_rekening: '',
})

let map;
let marker;

const latitude = ref(null);
const longitude = ref(null);
const error = ref(null);
const errors = ref({})
const loading = ref(true)
const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

const koordinat = computed(() => ({
  lat: latitude.value,
  lng: longitude.value,
}));

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const getLocation = () => {
  if (!navigator.geolocation) {
    error.value = "Geolocation tidak didukung browser";
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;

      latitude.value = lat;
      longitude.value = lng;

      // geser map ke lokasi user
      map.setView([lat, lng], 15);

      // set / update marker
      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng]).addTo(map);
      }
    },
    (err) => {
      error.value = err.message;
    }
  );
};

// ambil data awal
const fetchProfile = async () => {
  try {
    const id = user.nasabah_id
    const res = await axios.get(`/api/nasabah/profile/${id}`, { headers })
    console.log(res)
    form.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(loading, async (val) => {
  if (val === false) {
    await nextTick(); // tunggu DOM benar-benar muncul

    if (!map) {
      map = L.map("map").setView([-6.2, 106.8], 5); // default Indonesia

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap",
      }).addTo(map);

      // Event klik peta
      map.on("click", (e) => {
        const { lat, lng } = e.latlng;

        latitude.value = lat;
        longitude.value = lng;

        // kalau marker sudah ada, pindahkan
        if (marker) {
          marker.setLatLng([lat, lng]);
        } else {
          marker = L.marker([lat, lng]).addTo(map);
        }
      });
    }
  }
});

onMounted(() => {
  fetchProfile()
})

// submit update
const submitForm = async () => {
  errors.value = {}

  try {
    const id = user.nasabah_id

    form.value.gmap = JSON.stringify(koordinat.value)

    await axios.put(`/api/nasabah/edit-profile/${id}`, form.value, { headers })

    const res = await axios.get(`/api/nasabah/profile/${id}`, { headers })
    sessionStorage.setItem("user", JSON.stringify(res.data))

    router.push('/dashboard-nasabah')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error(err)
    }
  }
}

const goBack = () => {
  router.push('/dashboard-pengepul')
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
