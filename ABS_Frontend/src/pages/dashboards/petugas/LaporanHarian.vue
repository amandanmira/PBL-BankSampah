<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import axios from 'axios'

const token = sessionStorage.getItem('token')

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const listSampah = ref([]);
const listGudang = ref([]);

const data = ref({
  start_date: 30,
  gudang_id: "",
  sampah: [],
})

const addSampah = () => {
  data.value.sampah.push({
    sampah_id: "",
  });
};

const removeSampah = (index) => {
  data.value.sampah.splice(index, 1);
};

const downloadExcel = async () => {
  try {
    const res = await axios.get(
      `/api/cetak-laporan/excel`,
      {
        headers,
        params: {
          start_date: data.value.start_date,
          gudang_id: data.value.gudang_id,
          sampah: data.value.sampah,
        },
        responseType: 'blob', // penting
      }
    )

    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'transaksi.xlsx') // nama file
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (err) {
    console.error(err)
  }
}

const previewPdf = async () => {
  try {
    const response = await axios.get(
      `/api/cetak-laporan/pdf`,
      {
        headers,
        params: {
          start_date: data.value.start_date,
          gudang_id: data.value.gudang_id,
          sampah: data.value.sampah,
        },
        responseType: 'blob', // penting
      }
    )

    const file = new Blob([response.data], { type: 'application/pdf' })
    const fileURL = URL.createObjectURL(file)

    window.open(fileURL)
  } catch (err) {
    console.error(err)
  }
}

const fetchData = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/laporan/list-sampah", { headers });
    listSampah.value = response.data;
    
    const responseG = await axios.get("http://localhost:8000/api/laporan/list-gudang", { headers });
    listGudang.value = responseG.data;
  } catch (err) {
    console.error("Gagal mengambil data:", err);
  }
};

onMounted(async () => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Laporan Harian">
    <div class="bg-white rounded-[2.5rem] p-12 shadow-sm border border-stone-100 flex flex-col items-center justify-center text-center">
      <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center mb-6">
        <Icon icon="material-symbols:description-outline" class="w-10 h-10 text-stone-300" />
      </div>
      <h2 class="text-2xl font-black text-stone-800 mb-2">Laporan Harian</h2>
      <!-- <p class="text-stone-400 font-medium max-w-xs">Halaman ini sedang dalam tahap pengembangan. Segera Anda dapat melihat laporan harian di sini.</p> -->
      <div>
        <div>
          <div>
            <label>Periode (Hari)</label><br />
            <input type="number" v-model="data.start_date" />
          </div>
          
          <div>
            <label>Gudang</label><br />
            <select v-model="data.gudang_id">
                <option value="">
                  -- Pilih Gudang --
                </option>
                <option
                  v-for="item in listGudang"
                  :key="item.gudang_id"
                  :value="item.gudang_id"
                >
                  {{ item.alamat }}
                </option>
              </select>
          </div>

          <button @click="addSampah">+ Tambah Sampah</button>
          <div
            v-for="(s, index) in data.sampah"
            :key="index"
            style="border:1px solid #ccc; padding:10px; margin-top:10px;"
          >
            <div>
              <label>Sampah</label><br />
              <select v-model="s.sampah_id">
                <option value="">
                  -- Pilih Item Sampah --
                </option>
                <option
                  v-for="item in listSampah"
                  :key="item.item_id"
                  :value="item.item_id"
                >
                  {{ item.nama }}
                </option>
              </select>
            </div>

            <button @click="removeSampah(index)">
              Hapus Sampah
            </button>
          </div>
        </div>

        <button @click="downloadExcel()" class="border-2 p-2">Cetak Laporan Excel</button><br />
        <button @click="previewPdf()" class="border-2 p-2">Cetak Laporan Pdf</button>
      </div>
    </div>
  </DashboardLayout>
</template>
