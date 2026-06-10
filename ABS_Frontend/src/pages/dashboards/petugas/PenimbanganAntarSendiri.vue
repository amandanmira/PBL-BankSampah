<template>
  <DashboardLayout :title="step === 1 ? 'Dashboard Penimbangan' : step === 2 ? 'Form Detail Penimbangan' : step === 3 ? 'Pratinjau Penimbangan' : 'Setor Manual (Walk-in)'">
    
    <!-- STEP 1: PILIH NASABAH -->
    <div v-if="step === 1" class="max-w-3xl mx-auto pb-20 animate-in fade-in duration-500">
      <div class="mb-6">
        <h1 class="text-3xl font-black text-stone-800">Dashboard Penimbangan</h1>
        <p class="text-stone-500 font-medium mt-1">Pilih Nasabah Untuk Mengisi Form Penimbangan</p>
      </div>

      <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-stone-100">
        <h2 class="text-lg font-black text-stone-800 mb-4">Cari Nasabah</h2>
        
        <div class="relative flex mb-6">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Nama Nasabah atau ID..." 
            class="flex-1 bg-[#F9F9F7] border border-stone-200 rounded-l-[1rem] py-3.5 px-5 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043] transition-colors"
          />
          <button class="bg-[#4A7043] text-white px-6 rounded-r-[1rem] hover:bg-[#3D5C37] transition-colors flex items-center justify-center shadow-sm">
            <Icon icon="material-symbols:search" class="w-6 h-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div 
            v-for="n in filteredNasabahList" 
            :key="n.nasabah_id"
            @click="selectNasabahAndProceed(n)"
            class="bg-[#F9F9F7] border border-stone-200 rounded-[1.5rem] p-5 flex justify-between items-center cursor-pointer hover:border-[#4A7043] transition-all group"
          >
            <div>
              <div class="flex items-center gap-2 mb-1.5">
                <h3 class="text-lg font-black text-stone-800 group-hover:text-[#4A7043] transition-colors">{{ n.nama }}</h3>
                <span class="bg-[#5C8554] text-white text-[10px] font-bold px-2 py-0.5 rounded-full tracking-wider">NSB-{{ String(n.nasabah_id).padStart(3, '0') }}</span>
              </div>
              <p class="text-sm font-medium text-stone-500">{{ n.alamat || '-' }}</p>
              <p class="text-sm font-medium text-stone-500">{{ n.no_telp || '-' }}</p>
            </div>
            <div class="text-right">
              <p class="text-[11px] font-bold text-stone-400">Saldo Saat Ini</p>
              <p class="text-lg font-black text-[#4A7043]">{{ formatRupiah(Number(n.saldo)) }}</p>
            </div>
          </div>
          
          <div v-if="filteredNasabahList.length === 0" class="text-center py-10">
            <p class="text-stone-500 font-medium">Tidak ada nasabah yang ditemukan.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2: FORM PENIMBANGAN -->
    <div v-else-if="step === 2" class="max-w-3xl mx-auto space-y-6 pb-20 animate-in fade-in duration-500">
      <div class="flex items-center gap-4">
        <button @click="step = 1" class="w-10 h-10 rounded-full bg-white shadow-sm border border-stone-100 flex items-center justify-center text-[#4A7043] hover:bg-[#4A7043] hover:text-white transition-all">
          <Icon icon="material-symbols:arrow-back" class="w-6 h-6" />
        </button>
        <div>
          <h2 class="text-2xl font-black text-stone-800">Form Detail Penimbangan</h2>
          <p class="text-stone-500 font-medium">Input Detail Penimbangan Dari Transaksi Nasabah</p>
        </div>
      </div>

      <!-- Nasabah Card Summary -->
      <div class="bg-[#4A7043] rounded-[1.5rem] p-6 text-white shadow-lg flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
          <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
        </div>
        <div class="flex-1">
          <p class="text-white/70 text-xs font-bold mb-1">Nama Nasabah:</p>
          <h3 class="text-xl font-black">{{ selectedNasabahData?.nama }} <span class="text-white/60 text-base font-bold">(NSB-{{ String(selectedNasabahData?.nasabah_id).padStart(3, '0') }})</span></h3>
        </div>
      </div>

      <!-- Method Display -->
      <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 space-y-2">
        <p class="text-sm font-black text-[#4A7043]">Metode Setor:</p>
        <div class="w-full bg-[#F5F7F5] rounded-xl p-4 text-stone-600 font-black flex items-center gap-3">
          <Icon icon="material-symbols:move-to-inbox-outline" class="w-5 h-5 text-[#4A7043]" />
          <span>Setor Manual</span>
        </div>
      </div>

      <!-- Tukang Picker -->
      <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 space-y-4">
        <p class="text-sm font-black text-[#4A7043]">Tukang: <span class="text-red-500">*</span></p>
        
        <button 
          v-if="!selectedTukang"
          @click="openWorkerModal"
          class="w-full group cursor-pointer bg-stone-50/50 border-2 border-dashed border-stone-200 rounded-[2rem] p-10 transition-all hover:bg-white hover:border-[#4A7043] hover:shadow-2xl hover:shadow-green-900/10 active:scale-[0.98] flex flex-col items-center gap-5 relative overflow-hidden"
        >
          <div class="w-20 h-20 bg-white rounded-[1.5rem] flex items-center justify-center text-stone-400 group-hover:text-[#4A7043] group-hover:bg-[#EAF0EB] shadow-sm group-hover:shadow-md transition-all duration-500 relative z-10">
            <Icon icon="material-symbols:person-add-rounded" class="w-10 h-10 group-hover:scale-110 transition-transform" />
          </div>
          
          <div class="text-center relative z-10">
            <h4 class="text-lg font-black text-stone-800 group-hover:text-[#4A7043] transition-colors">Pilih Tukang Penimbang</h4>
            <div class="flex items-center justify-center gap-2 mt-2">
              <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
              <p class="text-[10px] font-black text-stone-400 uppercase tracking-[0.2em]">Wajib diisi untuk melanjutkan</p>
            </div>
          </div>

          <!-- Hover Indicator -->
          <div class="absolute bottom-4 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
            <Icon icon="material-symbols:keyboard-arrow-down-rounded" class="w-6 h-6 text-[#4A7043] animate-bounce" />
          </div>
        </button>

        <div v-else class="flex flex-col md:flex-row gap-6 relative">
          <button @click="openWorkerModal" class="absolute top-0 right-0 p-2 text-stone-400 hover:text-[#4A7043] transition-colors">
            <Icon icon="material-symbols:edit-outline" class="w-5 h-5" />
          </button>
          
          <div class="w-24 h-24 bg-[#F5F7F5] rounded-2xl flex items-center justify-center shrink-0 border border-stone-100 overflow-hidden shadow-sm">
            <img v-if="selectedTukangData?.foto" :src="`http://localhost:8000/storage/${selectedTukangData.foto}`" class="w-full h-full object-cover" />
            <Icon v-else icon="material-symbols:image-outline" class="w-8 h-8 text-stone-300" />
          </div>
          <div class="flex-1 bg-[#F9F9F7] rounded-2xl p-5 flex flex-col justify-center space-y-3 border border-stone-100 shadow-sm">
            <div class="flex items-center text-xs font-bold text-stone-600">
              <span class="w-20 text-[#4A7043] font-black">Nama</span> 
              <span class="w-4">:</span> 
              <span class="text-stone-800 font-black">{{ selectedTukangData?.nama || '-' }}</span>
            </div>
            <div class="flex items-center text-xs font-bold text-stone-600">
              <span class="w-20 text-[#4A7043] font-black">No. HP</span> 
              <span class="w-4">:</span> 
              <span class="text-stone-800 font-black">{{ selectedTukangData?.no_telp || '-' }}</span>
            </div>
            <div class="flex items-center text-xs font-bold text-stone-600">
              <span class="w-20 text-[#4A7043] font-black">Email</span> 
              <span class="w-4">:</span> 
              <span class="text-stone-800 font-black">{{ selectedTukangData?.email || '-' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Detail Sampah Form -->
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
          <div>
            <h3 class="text-lg font-black text-stone-800">Detail Sampah</h3>
            <p v-if="user?.gudang" class="text-xs font-bold text-stone-500 mt-1 bg-[#F5F7F5] px-3 py-1 rounded-full w-fit border border-stone-100">
              Stok Gudang: <span :class="Math.round(totalStokGudang + totalBerat) >= user.gudang.kapasitas ? 'text-red-500' : 'text-[#4A7043]'">{{ Math.round(totalStokGudang + totalBerat) }}</span> / {{ user.gudang.kapasitas }} kg
            </p>
          </div>
          <button @click="addRow" class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#3D5C37] transition-all flex items-center gap-1.5 shadow-sm">
            <Icon icon="material-symbols:add" class="w-4 h-4" /> Tambah Jenis
          </button>
        </div>

        <div class="space-y-6">
          <div v-for="(row, index) in formRows" :key="index" class="bg-[#F9F9F7] rounded-[1.5rem] p-5 border border-stone-100 relative">
            <button v-if="formRows.length > 1" @click="removeRow(index)" class="absolute -top-3 -right-3 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-all shadow-sm">
              <Icon icon="material-symbols:close" class="w-5 h-5 font-bold" />
            </button>

            <div class="flex flex-col md:flex-row gap-6">
              <!-- Upload Foto -->
              <div class="w-full md:w-1/3">
                <p class="text-[11px] font-black text-stone-800 mb-3 text-center uppercase tracking-widest">Upload Foto Sampah</p>
                <div @click="triggerFileInput(index)" class="w-full aspect-square border-2 border-dashed border-[#8CA68D] rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-green-50 transition-all relative overflow-hidden group">
                  <img v-if="row.fotoPreview" :src="row.fotoPreview" class="absolute inset-0 w-full h-full object-cover z-10" />
                  <div v-if="row.fotoPreview" class="absolute inset-0 bg-black/40 z-20 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                    <Icon icon="material-symbols:edit-outline" class="w-8 h-8 text-white" />
                  </div>
                  <div class="flex flex-col items-center gap-2 text-[#4A7043] group-hover:scale-110 transition-transform">
                    <Icon icon="material-symbols:upload-2" class="w-10 h-10" />
                  </div>
                  <input :id="'file-input-'+index" type="file" class="hidden" accept="image/*" @change="handleFileUpload($event, index)" />
                </div>
                <p class="text-[10px] text-center text-stone-400 mt-3 font-bold">Klik untuk upload foto sampah<br>Format: JPG, PNG (Max 5MB)</p>
              </div>

              <!-- Inputs -->
              <div class="w-full md:w-2/3 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Kategori Sampah <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="row.kategori_id" @change="row.sampah_id = ''" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-bold appearance-none focus:ring-2 focus:ring-[#4A7043]/20 focus:outline-none">
                        <option value="" disabled>Pilih</option>
                        <option v-for="kat in listKategori" :key="kat.kategori_id" :value="kat.kategori_id">{{ kat.nama }}</option>
                      </select>
                      <Icon icon="material-symbols:arrow-drop-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                    </div>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Jenis Sampah <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="row.sampah_id" :disabled="!row.kategori_id" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-bold appearance-none disabled:bg-stone-100 focus:ring-2 focus:ring-[#4A7043]/20 focus:outline-none">
                        <option value="" disabled>Pilih</option>
                        <option v-for="item in filteredSampah(row.kategori_id)" :key="item.sampah_id" :value="item.sampah_id">{{ item.item_sampah?.nama }}</option>
                      </select>
                      <Icon icon="material-symbols:arrow-drop-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                    </div>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Berat (kg) <span class="text-red-500">*</span></label>
                    <input type="number" v-model="row.berat_timbang" step="0.1" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-bold focus:ring-2 focus:ring-[#4A7043]/20 focus:outline-none" placeholder="0" />
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Harga/kg</label>
                    <div class="w-full bg-[#F5F7F5] border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-black text-stone-500">
                      {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}
                    </div>
                  </div>
                </div>

                <div class="pt-4 flex justify-end items-center gap-4 border-t border-stone-200">
                  <p class="text-sm font-black text-stone-800">Total:</p>
                  <p class="text-lg font-black text-[#4A7043]">{{ formatRupiah(getRowTotal(row)) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 bg-[#4A7043] rounded-2xl p-6 flex justify-between items-center text-white shadow-lg">
          <div>
            <p class="text-white/70 text-xs font-bold uppercase tracking-widest">Total Berat</p>
            <p class="text-2xl font-black">{{ totalBerat.toFixed(1) }} kg</p>
          </div>
          <div class="text-right">
            <p class="text-white/70 text-xs font-bold uppercase tracking-widest">Total Nilai</p>
            <p class="text-2xl font-black">{{ formatRupiah(totalNilai) }}</p>
          </div>
        </div>
      </div>

      <div class="flex gap-4">
        <button @click="step = 1" class="flex-1 py-4 rounded-2xl bg-stone-200 text-stone-600 font-black hover:bg-stone-300 transition-all">Batal</button>
        <button @click="goToPreview" :disabled="!isFormValid" class="flex-[2] py-4 rounded-2xl bg-[#4A7043] text-white font-black hover:bg-[#3D5C37] shadow-lg disabled:opacity-50 transition-all flex items-center justify-center gap-2">
          Lanjut ke Konfirmasi <Icon icon="material-symbols:arrow-forward" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- STEP 3: PRATINJAU PENIMBANGAN -->
    <div v-else-if="step === 3" class="max-w-3xl mx-auto space-y-6 pb-20 animate-in fade-in duration-500">
      <div class="flex items-center gap-4">
        <button @click="step = 2" class="w-10 h-10 rounded-full bg-white shadow-sm border border-stone-100 flex items-center justify-center text-[#4A7043] hover:bg-[#4A7043] hover:text-white transition-all">
          <Icon icon="material-symbols:arrow-back" class="w-6 h-6" />
        </button>
        <div>
          <h2 class="text-2xl font-black text-stone-800">Pratinjau Penimbangan</h2>
          <p class="text-stone-500 font-medium">Pratinjau transaksi nasabah yang datang langsung ke lokasi</p>
        </div>
      </div>

      <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-stone-100 space-y-8">
        <h3 class="text-lg font-black text-stone-800 border-b border-stone-100 pb-4">Konfirmasi Transaksi</h3>

        <!-- Nasabah Preview -->
        <div class="bg-[#F9F9F7] rounded-[1.5rem] p-6 space-y-4">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Nasabah</p>
          <div>
            <h4 class="text-xl font-black text-stone-800">{{ selectedNasabahData?.nama }}</h4>
            <p class="text-sm font-bold text-stone-500 mt-1">NSB-{{ String(selectedNasabahData?.nasabah_id).padStart(3, '0') }} • {{ selectedNasabahData?.no_telp || '-' }}</p>
            <p class="text-sm font-bold text-stone-500 mt-1">{{ selectedNasabahData?.alamat || '-' }}</p>
          </div>
          <div class="pt-4 border-t border-stone-200 flex items-center justify-between">
            <p class="text-sm font-bold text-stone-500">Saldo Saat Ini:</p>
            <p class="text-lg font-black text-[#4A7043]">{{ formatRupiah(saldoSaatIni) }}</p>
          </div>
        </div>

        <!-- Tukang Preview -->
        <div class="space-y-4">
          <p class="text-[10px] font-black text-[#4A7043] uppercase tracking-widest">Tukang</p>
          <div class="flex items-center gap-6 bg-[#F9F9F7] rounded-[1.5rem] p-5 border border-stone-100 shadow-sm">
            <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center shrink-0 border border-stone-100 overflow-hidden shadow-sm">
              <img v-if="selectedTukangData?.foto" :src="`http://localhost:8000/storage/${selectedTukangData.foto}`" class="w-full h-full object-cover" />
              <Icon v-else icon="material-symbols:image-outline" class="w-8 h-8 text-stone-300" />
            </div>
            <div class="flex-1 space-y-2">
              <div class="flex items-center text-xs font-bold text-stone-500"><span class="w-20 text-[#4A7043] font-black uppercase tracking-widest">Nama</span> <span class="w-4">:</span> <span class="text-stone-800 font-black">{{ selectedTukangData?.nama || '-' }}</span></div>
              <div class="flex items-center text-xs font-bold text-stone-500"><span class="w-20 text-[#4A7043] font-black uppercase tracking-widest">No. HP</span> <span class="w-4">:</span> <span class="text-stone-800 font-black">{{ selectedTukangData?.no_telp || '-' }}</span></div>
              <div class="flex items-center text-xs font-bold text-stone-500"><span class="w-20 text-[#4A7043] font-black uppercase tracking-widest">Email</span> <span class="w-4">:</span> <span class="text-stone-800 font-black">{{ selectedTukangData?.email || '-' }}</span></div>
            </div>
          </div>
        </div>

        <!-- Detail Sampah Preview -->
        <div class="space-y-4">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Detail Sampah</p>
          <div class="space-y-3">
            <div v-for="(row, i) in formRows" :key="i" class="flex items-center justify-between p-4 border border-stone-100 rounded-2xl bg-white shadow-sm hover:shadow-md transition-all">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-[#F9F9F7] border border-stone-100 overflow-hidden shrink-0 flex items-center justify-center shadow-inner">
                  <img v-if="row.fotoPreview" :src="row.fotoPreview" class="w-full h-full object-cover" />
                  <Icon v-else icon="fluent-emoji:package" class="w-8 h-8" />
                </div>
                <div>
                  <p class="font-black text-stone-800 text-sm">{{ getSampahName(row.sampah_id) }}</p>
                  <p class="text-[11px] font-bold text-stone-400 mt-1">{{ row.berat_timbang }} kg × {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}/kg</p>
                </div>
              </div>
              <p class="font-black text-[#4A7043] text-lg">{{ formatRupiah(getRowTotal(row)) }}</p>
            </div>
          </div>
        </div>

        <!-- Tipe Transaksi Preview -->
        <div class="space-y-4">
          <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Tipe Transaksi</p>
          <div class="w-full bg-white border border-stone-100 shadow-sm rounded-2xl p-5 text-stone-700 font-black text-sm flex items-center justify-between">
            <span>Setor Manual</span>
            <Icon icon="material-symbols:check-circle-outline" class="w-5 h-5 text-green-500" />
          </div>
        </div>

        <!-- Total Preview Card -->
        <div class="bg-[#4A7043] rounded-[2rem] overflow-hidden shadow-2xl">
          <div class="p-8 flex justify-between items-center text-white border-b border-white/10">
            <div>
              <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Total Berat</p>
              <p class="text-2xl font-black">{{ totalBerat.toFixed(1) }} kg</p>
            </div>
            <div class="text-right">
              <p class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Total Nilai</p>
              <p class="text-3xl font-black">{{ formatRupiah(totalNilai) }}</p>
            </div>
          </div>
          <div class="bg-[#3D5C37] p-6 flex justify-between items-center text-white">
            <p class="text-white/70 text-xs font-bold uppercase tracking-widest">Saldo Setelah Transaksi</p>
            <p class="text-xl font-black">{{ formatRupiah(saldoSetelahTransaksi) }}</p>
          </div>
        </div>

        <!-- Info text -->
        <div class="bg-blue-50 text-blue-600 text-xs font-black p-5 rounded-2xl flex items-start gap-3 border border-blue-100">
          <Icon icon="material-symbols:info-outline" class="w-6 h-6 shrink-0" />
          <p class="leading-relaxed">Nilai transaksi akan LANGSUNG masuk ke saldo nasabah setelah disimpan</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-4 pt-4">
        <button @click="step = 2" class="flex-[1] py-4 rounded-2xl bg-stone-100 text-stone-600 font-black hover:bg-stone-200 transition-all flex justify-center items-center gap-2">
          <Icon icon="material-symbols:arrow-back" class="w-5 h-5" /> Edit Data
        </button>
        <button @click="submitPenimbangan" :disabled="isSubmitting" class="flex-[2] py-4 rounded-2xl bg-[#4A7043] text-white font-black hover:bg-[#3D5C37] transition-all flex justify-center items-center gap-2 shadow-lg active:scale-[0.98]">
          <Icon v-if="isSubmitting" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
          <Icon v-else icon="material-symbols:save-outline" class="w-5 h-5" />
          {{ isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi' }}
        </button>
      </div>
    </div>

    <!-- STEP 4: SUCCESS / RECEIPT -->
    <div v-else-if="step === 4" class="max-w-3xl mx-auto flex flex-col items-center pb-20 text-center space-y-6">
      <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mb-2 shadow-lg shadow-green-200/50 animate-in zoom-in duration-700">
        <Icon icon="material-symbols:check-circle-outline" class="w-16 h-16" />
      </div>
      
      <div class="text-center space-y-2 mb-4">
        <h3 class="text-3xl font-black text-stone-800">Transaksi Berhasil!</h3>
        <p class="text-stone-500 font-medium">Nilai telah langsung masuk ke saldo nasabah</p>
      </div>
      
      <!-- Struk / Receipt Card -->
      <div class="w-full bg-white rounded-t-[2.5rem] rounded-b-3xl shadow-2xl border border-stone-100 overflow-hidden relative animate-in slide-in-from-bottom-8 duration-700">
        <!-- Struk Header -->
        <div class="p-8 border-b border-dashed border-stone-300 text-center space-y-2 bg-[#F9F9F7]">
          <h4 class="text-2xl font-black text-[#4A7043] tracking-widest uppercase">BANK SAMPAH</h4>
          <p class="text-xs font-bold text-stone-500 tracking-wide">Struk Transaksi Setor Manual</p>
        </div>
        
        <div class="p-8 space-y-8 text-left">
          <!-- Meta Data -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-10 text-[11px] font-bold text-stone-500 uppercase tracking-wider">
            <div class="flex justify-between border-b border-stone-50 pb-1.5"><span class="opacity-60">No. Transaksi:</span> <span class="text-stone-800 font-black">TRX-{{ String(receiptData?.transaksi_id || 0).padStart(6, '0') }}</span></div>
            <div class="flex justify-between border-b border-stone-50 pb-1.5"><span class="opacity-60">Gudang:</span> <span class="text-stone-800 font-black">{{ user?.gudang?.alamat || '-' }}</span></div>
            <div class="flex justify-between border-b border-stone-50 pb-1.5"><span class="opacity-60">Tanggal:</span> <span class="text-stone-800 font-black">{{ formatDate(new Date().toISOString()) }}</span></div>
            <div class="flex justify-between border-b border-stone-50 pb-1.5"><span class="opacity-60">Petugas Input:</span> <span class="text-stone-800 font-black">{{ user?.nama || 'Staff' }}</span></div>
            <div class="flex justify-between border-b border-stone-50 pb-1.5"><span class="opacity-60">Tukang:</span> <span class="text-stone-800 font-black">{{ selectedTukangData?.nama || '-' }}</span></div>
          </div>

          <!-- Foto Sampah Thumbnail -->
          <div class="space-y-3 pt-2">
            <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Foto Sampah</p>
            <div class="flex flex-wrap gap-3">
              <div v-for="(row, i) in formRows" :key="i" class="w-16 h-16 rounded-2xl border border-stone-100 overflow-hidden bg-[#F9F9F7] flex items-center justify-center shadow-sm">
                <img v-if="row.fotoPreview" :src="row.fotoPreview" class="w-full h-full object-cover" />
                <Icon v-else icon="fluent-emoji:package" class="w-8 h-8 opacity-40" />
              </div>
            </div>
          </div>

          <!-- Nasabah Info -->
          <div class="space-y-1 py-4 border-y border-stone-100">
            <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Nasabah</p>
            <h5 class="text-lg font-black text-stone-800">{{ selectedNasabahData?.nama }}</h5>
            <p class="text-xs font-bold text-stone-400">NSB-{{ String(selectedNasabahData?.nasabah_id).padStart(3, '0') }}</p>
          </div>

          <!-- Rincian Sampah -->
          <div class="space-y-4">
            <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Detail Sampah</p>
            <div class="space-y-3">
              <div v-for="(row, i) in formRows" :key="i" class="flex justify-between items-start group">
                <div>
                  <p class="text-sm font-black text-stone-700">{{ getSampahName(row.sampah_id) }}</p>
                  <p class="text-[11px] font-bold text-stone-400 mt-0.5">{{ row.berat_timbang }} kg × {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}</p>
                </div>
                <p class="text-sm font-black text-stone-800 group-hover:text-[#4A7043] transition-colors">{{ formatRupiah(getRowTotal(row)) }}</p>
              </div>
            </div>
          </div>

          <!-- Total Final -->
          <div class="pt-6 border-t border-stone-100 space-y-3">
            <div class="flex justify-between text-xs font-bold text-stone-500 uppercase tracking-widest">
              <span>Total Berat</span>
              <span class="text-stone-800 font-black">{{ totalBerat.toFixed(1) }} kg</span>
            </div>
            <div class="flex justify-between items-end">
              <span class="text-base font-black text-stone-800 uppercase tracking-widest">Total Nilai</span>
              <span class="text-3xl font-black text-[#4A7043]">{{ formatRupiah(totalNilai) }}</span>
            </div>
          </div>

          <!-- Saldo Recap Box -->
          <div class="bg-green-50/50 rounded-2xl p-6 space-y-3 border border-green-100 shadow-inner">
            <div class="flex justify-between text-xs font-bold text-stone-500 uppercase tracking-wider">
              <span>Saldo Sebelum</span>
              <span class="text-stone-700 font-black">{{ formatRupiah(saldoSaatIni) }}</span>
            </div>
            <div class="flex justify-between text-xs font-bold text-green-600 uppercase tracking-wider">
              <span>Nilai Tambahan</span>
              <span class="font-black">+ {{ formatRupiah(totalNilai) }}</span>
            </div>
            <div class="flex justify-between text-lg font-black text-stone-800 pt-3 border-t border-green-200/50">
              <span class="text-sm uppercase tracking-widest">Saldo Sesudah</span>
              <span>{{ formatRupiah(saldoSetelahTransaksi) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="w-full pt-4">
        <button @click="router.push('/dashboard-petugas')" class="w-full py-4 rounded-2xl bg-[#4A7043] text-white font-black text-sm hover:bg-[#3D5C37] shadow-lg transition-all flex items-center justify-center gap-2">
          <Icon icon="material-symbols:home-outline" class="w-5 h-5" /> Kembali ke Menu
        </button>
      </div>
    </div>

    <!-- Worker Selection Modal -->
    <div v-if="isWorkerModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="isWorkerModalOpen = false"></div>
      <div class="relative bg-[#F5F5F0] w-full max-w-2xl rounded-[3rem] shadow-2xl flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in-95 duration-300">
        <div class="p-8 pb-6 flex justify-between items-start bg-white border-b border-stone-100">
          <div>
            <h3 class="text-2xl font-black text-stone-800">Pilih Tukang Penimbang</h3>
            <p class="text-stone-500 font-medium text-sm mt-1">Pilih petugas yang bertugas melakukan penimbangan</p>
          </div>
          <button @click="isWorkerModalOpen = false" class="p-2.5 bg-stone-50 hover:bg-stone-200 rounded-xl transition-colors">
            <Icon icon="material-symbols:close" class="w-6 h-6 text-stone-400" />
          </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 pt-6 space-y-4">
          <div 
            v-for="worker in listTukang" 
            :key="worker.tukang_id"
            @click="selectWorker(worker)"
            class="bg-white border-2 border-stone-100 rounded-3xl p-5 flex items-center gap-6 cursor-pointer hover:border-[#4A7043] hover:shadow-xl transition-all group active:scale-[0.98]"
          >
            <div class="w-24 h-24 bg-[#F9F9F7] rounded-[1.5rem] flex items-center justify-center overflow-hidden border border-stone-100 shrink-0 group-hover:border-[#4A7043]/20">
              <img v-if="worker.foto" :src="`http://localhost:8000/storage/${worker.foto}`" alt="Foto Tukang" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
              <Icon v-else icon="material-symbols:image-outline" class="w-10 h-10 text-stone-200" />
            </div>
            <div class="flex-1 space-y-3">
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Nama:</p>
                  <p class="font-black text-xl text-stone-800 group-hover:text-[#4A7043] transition-colors">{{ worker.nama }}</p>
                </div>
                <div class="bg-green-50 text-[#4A7043] px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm border border-green-100 group-hover:bg-[#4A7043] group-hover:text-white transition-all">
                  Pilih
                </div>
              </div>
              <div class="flex gap-6 pt-1 border-t border-stone-50">
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-0.5">No. HP:</p>
                  <p class="text-sm font-black text-stone-600">{{ worker.no_telp }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-0.5">Email:</p>
                  <p class="text-sm font-bold text-stone-400 truncate max-w-[150px]">{{ worker.email || '-' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="listTukang.length === 0" class="text-center py-20 flex flex-col items-center gap-4">
            <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center text-stone-300">
              <Icon icon="material-symbols:person-off-outline" class="w-10 h-10" />
            </div>
            <p class="text-stone-400 font-black text-lg">Tidak ada tukang yang tersedia di gudang ini.</p>
          </div>
        </div>
      </div>
    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";

const axios = inject('axios'); // Gunakan axios global
const router = useRouter();

const step = ref(1);
const listNasabah = ref([]);
const selectedNasabah = ref(""); 
const selectedNasabahData = ref(null);
const searchQuery = ref("");

const listKategori = ref([]);
const listSampah = ref([]);
const listTukang = ref([]); 
const selectedTukang = ref(""); 
const selectedTukangData = ref(null);
const isWorkerModalOpen = ref(false);

const isSubmitting = ref(false);
const receiptData = ref(null);

const formRows = ref([{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null }]);

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch {
    return {};
  }
});

const totalStokGudang = computed(() => {
  return listSampah.value.reduce((sum, item) => sum + (parseFloat(item.stok) || 0), 0);
});

const saldoSaatIni = computed(() => Number(selectedNasabahData.value?.saldo || 0));
const saldoSetelahTransaksi = computed(() => saldoSaatIni.value + totalNilai.value);

const compressImage = (file) => {
  return new Promise((resolve) => {
    if (file.size <= 1024 * 1024 || !file.type.startsWith('image/')) {
      resolve(file);
      return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        const MAX_WIDTH = 1600;
        const MAX_HEIGHT = 1600;
        if (width > height) {
          if (width > MAX_WIDTH) {
            height = Math.round((height * MAX_WIDTH) / width);
            width = MAX_WIDTH;
          }
        } else {
          if (height > MAX_HEIGHT) {
            width = Math.round((width * MAX_HEIGHT) / height);
            height = MAX_HEIGHT;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        let quality = 0.8;
        const checkAndCompress = (q) => {
          canvas.toBlob((blob) => {
            if (!blob) {
              resolve(file);
              return;
            }
            
            if (blob.size > 1024 * 1024 && q > 0.3) {
              checkAndCompress(q - 0.15);
            } else {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });
              resolve(compressedFile);
            }
          }, 'image/jpeg', q);
        };

        checkAndCompress(quality);
      };
      img.src = event.target.result;
    };
    reader.readAsDataURL(file);
  });
};

const handleFileUpload = async (event, index) => {
  const file = event.target.files[0];
  if (file) {
    const compressedFile = await compressImage(file);
    formRows.value[index].foto = compressedFile;
    formRows.value[index].fotoPreview = URL.createObjectURL(compressedFile);
  }
};

const triggerFileInput = (index) => {
  const input = document.getElementById(`file-input-${index}`);
  if (input) input.click();
};

const addRow = () => {
  formRows.value.push({ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null });
};

const removeRow = (index) => {
  if (formRows.value.length > 1) formRows.value.splice(index, 1);
};

const filteredSampah = (kategoriId) => {
  if (!kategoriId) return [];
  return listSampah.value.filter(item => item.item_sampah && item.item_sampah.kategori_id === kategoriId);
};

const getHargaPerKg = (sampah_id) => {
  if (!sampah_id) return 0;
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  return selectedItem && selectedItem.item_sampah ? Number(selectedItem.item_sampah.harga_beli) : 0;
};

const getSampahName = (sampah_id) => {
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  return selectedItem?.item_sampah?.nama || 'Unknown';
};

const getRowTotal = (row) => {
  return (Number(row.berat_timbang) || 0) * getHargaPerKg(row.sampah_id);
};

const totalBerat = computed(() => formRows.value.reduce((sum, row) => sum + (Number(row.berat_timbang) || 0), 0));
const totalNilai = computed(() => formRows.value.reduce((sum, row) => sum + getRowTotal(row), 0));

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + 
         date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const filteredNasabahList = computed(() => {
  if (!searchQuery.value) return listNasabah.value;
  const lowerQuery = searchQuery.value.toLowerCase();
  return listNasabah.value.filter(n => 
    (n.nama && n.nama.toLowerCase().includes(lowerQuery)) || 
    `NSB-${String(n.nasabah_id).padStart(3, '0')}`.toLowerCase().includes(lowerQuery)
  );
});

const selectNasabahAndProceed = (nasabah) => {
  selectedNasabah.value = nasabah.nasabah_id;
  selectedNasabahData.value = nasabah;
  step.value = 2;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const isFormValid = computed(() => {
  const validItems = formRows.value.filter(row => row.sampah_id !== "" && row.berat_timbang > 0);
  return validItems.length === formRows.value.length && validItems.length > 0 && selectedTukang.value !== "";
});

const openWorkerModal = () => {
  isWorkerModalOpen.value = true;
};

const selectWorker = (worker) => {
  selectedTukang.value = worker.tukang_id;
  selectedTukangData.value = worker;
  isWorkerModalOpen.value = false;
};

const goToPreview = () => {
  if (isFormValid.value) {
    step.value = 3;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// API CALLS (Menggunakan URL Relatif)
const fetchListNasabah = async () => {
  try {
    const response = await axios.get("/api/petugas/list-nasabah");
    listNasabah.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListKategori = async () => {
  try {
    const response = await axios.get("/api/petugas/list-kategori");
    listKategori.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListSampah = async () => {
  try {
    const response = await axios.get("/api/petugas/list-sampah");
    listSampah.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListTukang = async () => {
  try {
    const response = await axios.get("/api/petugas/list-tukang");
    listTukang.value = response.data.data;
  } catch (err) { console.error(err); }
};

const submitPenimbangan = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    formData.append("nasabah_id", selectedNasabah.value);
    formData.append("tukang_id", selectedTukang.value);
    
    formRows.value.forEach((item, index) => {
      formData.append(`items[${index}][sampah_id]`, item.sampah_id);
      formData.append(`items[${index}][berat_timbang]`, item.berat_timbang);
      if (item.foto) formData.append(`items[${index}][foto]`, item.foto);
    });

    const response = await axios.post("/api/petugas/penimbangan-antar-sendiri", formData);
    receiptData.value = response.data;
    step.value = 4;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    alert("Gagal menyimpan: " + (err.response ? err.response.data.message : err.message));
  } finally {
    isSubmitting.value = false;
  }
};

const resetToStart = () => {
  step.value = 1;
  selectedNasabah.value = "";
  selectedNasabahData.value = null;
  selectedTukang.value = "";
  selectedTukangData.value = null;
  formRows.value = [{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null }];
  fetchListNasabah();
};

onMounted(() => {
  fetchListNasabah(); 
  fetchListKategori(); 
  fetchListSampah();
  fetchListTukang();
});
</script>

<style scoped>
.animate-in {
  animation: fadeIn 0.7s ease-out both;
}

.fade-in {
  animation: fadeIn 0.5s ease-out both;
}

.zoom-in {
  animation: zoomIn 0.5s ease-out both;
}

.slide-in-from-bottom-4 {
  animation: slideInBottom4 0.5s ease-out both;
}

.slide-in-from-bottom-8 {
  animation: slideInBottom8 0.7s ease-out both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes slideInBottom4 {
  from { opacity: 0; transform: translateY(1rem); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInBottom8 {
  from { opacity: 0; transform: translateY(2rem); }
  to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e2e2;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #d1d1d1;
}
</style>
