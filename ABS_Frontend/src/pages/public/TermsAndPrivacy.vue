<script setup>
import { ref, computed } from 'vue';

const activeTab = ref('terms'); // 'terms' atau 'privacy'

const tabs = [
  { id: 'terms', label: 'Syarat dan Ketentuan' },
  { id: 'privacy', label: 'Kebijakan Privasi' }
];

const pageData = {
  terms: {
    title: 'Syarat dan Ketentuan',
    sections: [
      {
        subtitle: 'Penerimaan Syarat Penggunaan Layanan',
        text: 'Dengan mendaftar, mengakses, atau menggunakan platform Bank Sampah ABS, Anda menyetujui untuk tunduk dan terikat dengan setiap syarat dan ketentuan yang tertulis di halaman ini beserta semua perubahannya dari waktu ke waktu. Jika Anda tidak setuju, harap untuk segera menghentikan penggunaan layanan sistem kami.'
      },
      {
        subtitle: 'Standar Penyetoran Obyek Sampah',
        text: 'Nasabah diwajibkan menyetorkan sampah dalam keadaan sudah terpilah dengan rapi (botol plastik terpisah dari kardus atau aluminium). Pihak Mitra Pengepul serta sentra penampungan ABS memiliki hak penuh untuk merevisi taksiran harga, atau menolak penjemputan apabila sampah tercampur material berbahaya medis atau organik.'
      },
      {
        subtitle: 'Ketentuan Nilai Komisi dan Saldo',
        text: 'Nilai tukar sampah berpatokan pada harga pasar daur ulang hari ini yang senantiasa diperbarui oleh pihak manajer sistem operasional. Seluruh akumulasi saldo poin yang didapatkan nasabah dapat ditarik atau dicairkan dengan batas transaksi minimal dan tanpa potongan biaya yang mengikat (selama opsi transfer yang disetujui masih berlaku).'
      },
      {
        subtitle: 'Tanggung Jawab Pengguna (Pengepul / Nasabah)',
        text: 'Setiap pihak diwajibkan untuk mengamankan kredensial profil masing-masing. Bank Sampah ABS akan memberikan mitigasi tetapi tidak bertanggung jawab penuh atas segala kerentanan penarikan dana apabila kelalaian kata sandi (password sharing) berada murni dari perangkat dan kesalahan user.'
      }
    ]
  },
  privacy: {
    title: 'Kebijakan Privasi',
    sections: [
      {
        subtitle: 'Informasi dan Data Pribadi yang Dikumpulkan',
        text: 'Sistem ABS mengumpulkan beberapa informasi pribadi Anda termasuk: nama lengkap, alamat jemput, koordinat rute perangkat GPS (secara dinamis saat memesan pengepul logistik), nama akun dompet digital (jika tertaut pencairan uang), dan validasi KTP khusus apabila Anda mendaftar sebagai agen lapangan pengepul.'
      },
      {
        subtitle: 'Tujuan Penggunaan Data',
        text: 'Kami menghimpun profil dan mobilitas perangkat Anda bukan untuk promosi pasif dari pihak ketiga tak terafiliasi. Data ini kami gunakan 100% demi pelacakan penjemputan sampah secara akurat, penyediaan log transaksi Anda yang adil, serta pencegahan modus manipulasi berat nilai gramasi sampah oleh oknum palsu.'
      },
      {
        subtitle: 'Perlindungan Kriptografi Digital',
        text: 'Sistem informasi Bank Sampah ABS beroperasi di atas kerangka enkripsi standar pelindung database terkini. Algoritme perlindungan yang memadai telah diaktifkan guna meredam serbuan peretas terhadap profil personal Anda beserta saldo komisi menabung sampah bulanan yang telah membeku menjadi uang fiat.'
      },
      {
        subtitle: 'Tautan Menuju Sistem Eksternal',
        text: 'Ada kalanya rute penarikan uang yang dituju menghubungkan API (Antarmuka Program) menuju ekosistem bank atau E-wallet eksternal di luar otoritas ABS. Mohon waspada atas situs dan antarmuka otentikasi pembayaran yang dihandle domain ketiga, karena syarat privasi mereka berlangsung lepas dari layanan situs kami.'
      }
    ]
  }
};

const currentContent = computed(() => pageData[activeTab.value]);

</script>

<template>
  <main class="py-16 md:py-28 min-h-[85vh] w-full bg-transparent">
    <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16 flex flex-col md:flex-row gap-12 lg:gap-24 relative items-start">
      
      <!-- Kolom Menu Kiri (Navigasi Halaman) -->
      <!-- Di HP jadi barisan tab sejajar yang bisa di-scroll ke kanan. Di laptop/PC nempel di samping (sticky) -->
      <aside class="w-full md:w-64 flex-shrink-0 md:sticky md:top-32 border-b border-[#DDDDDD] md:border-b-0 pb-4 md:pb-0 z-10">
        <div class="flex flex-row md:flex-col gap-6 md:gap-7 overflow-x-auto scrollbar-hide">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            class="text-left text-[17px] md:text-[20px] whitespace-nowrap transition-colors outline-none pb-2 md:pb-0 border-b-2 md:border-b-0 border-transparent cursor-pointer"
            :class="activeTab === tab.id 
              ? 'text-[#4A7043] font-bold border-b-[#4A7043] md:border-b-transparent' 
              : 'text-[#777777] font-semibold hover:text-[#4A7043] opacity-80'"
          >
            {{ tab.label }}
          </button>
        </div>
      </aside>

      <!-- Kolom Master Konten Kanan -->
      <section class="flex-1 pb-16">
        <!-- Judul Dinamis -->
        <h1 class="text-3xl md:text-4xl lg:text-[42px] font-extrabold text-[#4A7043] mb-10 md:mb-14 tracking-tight">
          {{ currentContent.title }}
        </h1>
        
        <!-- List Deskripsi Dinamis -->
        <div class="space-y-10 md:space-y-12 text-[#555555]">
          <div v-for="(section, idx) in currentContent.sections" :key="idx" class="flex flex-col gap-3">
            <h2 class="text-[17px] md:text-[19px] font-bold leading-snug">
              {{ section.subtitle }}
            </h2>
            <p class="text-[14.5px] md:text-[15.5px] leading-relaxed pr-2 sm:pr-6 md:pr-10 lg:pr-24">
              {{ section.text }}
            </p>
          </div>
        </div>
      </section>

    </div>
  </main>
</template>

<style scoped>
/* Fasilitas agar tidak ada gulir bawah sadar ganda di mobile view */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}
</style>
