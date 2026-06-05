<template>
  <DashboardLayout :title="step === 1 ? 'Form Detail Penimbangan' : step === 2 ? 'Pratinjau Penimbangan' : 'Simpan Penimbangan Nasabah'">
    <div class="max-w-3xl mx-auto space-y-6 pb-20 animate-in fade-in duration-500">
      
      <!-- Header Teks -->
      <div v-if="step === 1">
        <h2 class="text-2xl font-black text-stone-800">Form Detail Penimbangan</h2>
        <p class="text-stone-500 font-medium">Input Detail Penimbangan Dari Transaksi Nasabah</p>
      </div>
      <div v-else-if="step === 2">
        <h2 class="text-2xl font-black text-stone-800">Pratinjau Penimbangan</h2>
        <p class="text-stone-500 font-medium">Pratinjau transaksi nasabah yang datang langsung ke lokasi</p>
      </div>
      <div v-else-if="step === 3" class="text-center pt-8">
        <h2 class="text-2xl font-black text-stone-800">Simpan Penimbangan Nasabah</h2>
        <p class="text-stone-500 font-medium">Hasil Transaksi Nasabah Beserta Detailnya Akan Tampil Di Bawah</p>
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 text-[#4A7043]" />
      </div>

      <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-xl font-bold">
        {{ error }}
      </div>

      <template v-else>
        <!-- STEP 1: Form Detail Penimbangan -->
        <div v-if="step === 1" class="space-y-6">
          <!-- Nasabah Card -->
          <div class="bg-[#4A7043] rounded-[1.5rem] p-6 text-white shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
              <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
            </div>
            <div>
              <p class="text-white/70 text-xs font-bold mb-1">Nama Nasabah:</p>
              <h3 class="text-xl font-black">{{ penjemputan?.nasabah?.nama }} <span class="text-white/60 text-base font-bold">(NSB-{{ String(penjemputan?.nasabah_id).padStart(3, '0') }})</span></h3>
            </div>
          </div>

          <!-- Tipe Transaksi -->
          <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 space-y-2">
            <p class="text-sm font-black text-[#4A7043]">Tipe Transaksi:</p>
            <div class="w-full bg-[#F5F7F5] rounded-xl p-4 text-stone-600 font-bold">
              Penjemputan
            </div>
          </div>

          <!-- Tukang Card -->
          <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 space-y-4">
            <p class="text-sm font-black text-[#4A7043]">Tukang:</p>
            <div class="flex flex-col md:flex-row gap-6">
              <div class="w-24 h-24 bg-[#F5F7F5] rounded-2xl flex items-center justify-center shrink-0 border border-stone-100 overflow-hidden">
                <img v-if="penjemputan?.tukang?.foto" :src="`http://localhost:8000/storage/${penjemputan.tukang.foto}`" class="w-full h-full object-cover" />
                <Icon v-else icon="material-symbols:image-outline" class="w-8 h-8 text-stone-300" />
              </div>
              <div class="flex-1 bg-[#F5F7F5] rounded-2xl p-4 flex flex-col justify-center space-y-2 border border-stone-100">
                <div class="flex text-xs font-bold text-stone-600"><span class="w-20 text-[#4A7043]">Nama</span> <span class="w-4">:</span> <span>{{ penjemputan?.tukang?.nama || '-' }}</span></div>
                <div class="flex text-xs font-bold text-stone-600"><span class="w-20 text-[#4A7043]">No. Telp</span> <span class="w-4">:</span> <span>{{ penjemputan?.tukang?.no_telp || '-' }}</span></div>
              </div>
            </div>
          </div>

          <!-- Detail Sampah Form -->
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-black text-stone-800">Detail Sampah</h3>
              <button @click="addRow" class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#3D5C37] transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
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
                    <p class="text-sm font-black text-stone-800 mb-2 text-center">Upload Foto Sampah</p>
                    <div 
                      @click="triggerFileInput(index)"
                      class="w-full aspect-square border-2 border-dashed border-[#8CA68D] rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-green-50/50 transition-all relative overflow-hidden group"
                    >
                      <img v-if="row.fotoPreview" :src="row.fotoPreview" class="absolute inset-0 w-full h-full object-cover z-10" />
                      <div v-if="row.fotoPreview" class="absolute inset-0 bg-black/40 z-20 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                        <Icon icon="material-symbols:edit-outline" class="w-8 h-8 text-white" />
                      </div>
                      <Icon v-if="!row.fotoPreview" icon="material-symbols:upload-2" class="w-10 h-10 text-[#4A7043] mb-2" />
                      <input :id="'file-input-'+index" type="file" class="hidden" accept="image/*" @change="handleFileUpload($event, index)" />
                    </div>
                    <p class="text-[10px] text-center text-stone-400 mt-2 font-medium">Klik untuk upload foto sampah<br>Format: JPG, PNG (Max 5MB)</p>
                  </div>

                  <!-- Inputs -->
                  <div class="w-full md:w-2/3 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                      <!-- Kategori -->
                      <div class="space-y-1.5">
                        <label class="text-xs font-black text-stone-800">Kategori Sampah <span class="text-red-500">*</span></label>
                        <div class="relative">
                          <select v-model="row.kategori_id" @change="row.sampah_id = ''" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 appearance-none text-stone-700">
                            <option value="" disabled>Pilih</option>
                            <option v-for="kat in listKategori" :key="kat.kategori_id" :value="kat.kategori_id">{{ kat.nama }}</option>
                          </select>
                          <Icon icon="material-symbols:arrow-drop-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                        </div>
                      </div>

                      <!-- Jenis -->
                      <div class="space-y-1.5">
                        <label class="text-xs font-black text-stone-800">Jenis Sampah <span class="text-red-500">*</span></label>
                        <div class="relative">
                          <select v-model="row.sampah_id" :disabled="!row.kategori_id" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 appearance-none text-stone-700 disabled:bg-stone-100 disabled:text-stone-400">
                            <option value="" disabled>Pilih</option>
                            <option v-for="item in filteredSampah(row.kategori_id)" :key="item.sampah_id" :value="item.sampah_id">{{ item.item_sampah?.nama }}</option>
                          </select>
                          <Icon icon="material-symbols:arrow-drop-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                        </div>
                      </div>

                      <!-- Berat -->
                      <div class="space-y-1.5">
                        <label class="text-xs font-black text-stone-800">Berat (kg) <span class="text-red-500">*</span></label>
                        <input type="number" v-model="row.berat_timbang" step="0.1" min="0.1" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 text-stone-700" placeholder="0" />
                      </div>

                      <!-- Harga -->
                      <div class="space-y-1.5">
                        <label class="text-xs font-black text-stone-800">Harga/kg <span class="text-red-500">*</span></label>
                        <div class="w-full bg-[#F5F7F5] border border-stone-200/50 rounded-xl py-2.5 px-4 text-sm font-bold text-stone-500">
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

            <!-- Grand Total -->
            <div class="mt-6 bg-[#4A7043] rounded-2xl p-6 shadow-lg flex justify-between items-center text-white">
              <div>
                <p class="text-white/70 text-xs font-bold mb-1">Total Berat</p>
                <p class="text-2xl font-black">{{ totalBerat.toFixed(1) }} kg</p>
              </div>
              <div class="text-right">
                <p class="text-white/70 text-xs font-bold mb-1">Total Nilai</p>
                <p class="text-2xl font-black">{{ formatRupiah(totalNilai) }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-4 pt-4">
            <button @click="router.push('/dashboard-petugas/listpenjemputan')" class="flex-[1] py-4 rounded-2xl bg-stone-200 text-stone-600 font-bold hover:bg-stone-300 transition-all flex justify-center items-center gap-2">
              <Icon icon="material-symbols:close" class="w-5 h-5" /> Batal
            </button>
            <button @click="goToPreview" :disabled="!isFormValid" class="flex-[2] py-4 rounded-2xl font-bold transition-all flex justify-center items-center gap-2 shadow-lg" :class="isFormValid ? 'bg-[#4A7043] text-white hover:bg-[#3D5C37]' : 'bg-stone-200 text-stone-400 cursor-not-allowed shadow-none'">
              Lanjut ke Konfirmasi <Icon icon="material-symbols:arrow-forward" class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- STEP 2: Pratinjau Penimbangan -->
        <div v-else-if="step === 2" class="space-y-6">
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100 space-y-6">
            <h3 class="text-lg font-black text-stone-800 border-b border-stone-100 pb-4">Konfirmasi Transaksi</h3>

            <!-- Nasabah Preview -->
            <div class="bg-[#F9F9F7] rounded-[1.5rem] p-5 space-y-3">
              <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Nasabah</p>
              <div>
                <h4 class="text-lg font-black text-stone-800">{{ penjemputan?.nasabah?.nama }}</h4>
                <p class="text-xs font-bold text-stone-500 mt-1">NSB-{{ String(penjemputan?.nasabah_id).padStart(3, '0') }} • {{ penjemputan?.nasabah?.no_telp || '-' }}</p>
                <p class="text-xs font-bold text-stone-500 mt-1">{{ penjemputan?.alamat }}</p>
              </div>
              <div class="pt-3 border-t border-stone-200 flex items-center gap-2">
                <p class="text-xs font-bold text-stone-500">Saldo Saat Ini:</p>
                <p class="text-xs font-black text-[#4A7043]">{{ formatRupiah(saldoSaatIni) }} <span class="text-stone-400 font-medium">*statis</span></p>
              </div>
            </div>

            <!-- Tukang Preview -->
            <div class="space-y-3">
              <p class="text-[10px] font-black text-[#4A7043] uppercase tracking-widest">Tukang:</p>
              <div class="flex items-center gap-4 bg-[#F9F9F7] rounded-[1.5rem] p-4">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center shrink-0 border border-stone-100 overflow-hidden">
                  <img v-if="penjemputan?.tukang?.foto" :src="`http://localhost:8000/storage/${penjemputan.tukang.foto}`" class="w-full h-full object-cover" />
                  <Icon v-else icon="material-symbols:image-outline" class="w-6 h-6 text-stone-300" />
                </div>
                <div class="flex-1 space-y-1">
                  <div class="flex text-[11px] font-bold text-stone-500"><span class="w-16 text-[#4A7043]">Nama</span> <span class="w-3">:</span> <span class="text-stone-700">{{ penjemputan?.tukang?.nama || '-' }}</span></div>
                  <div class="flex text-[11px] font-bold text-stone-500"><span class="w-16 text-[#4A7043]">No. Telp</span> <span class="w-3">:</span> <span class="text-stone-700">{{ penjemputan?.tukang?.no_telp || '-' }}</span></div>
                </div>
              </div>
            </div>

            <!-- Detail Sampah Preview -->
            <div class="space-y-3">
              <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Detail Sampah:</p>
              <div class="space-y-3">
                <div v-for="(row, i) in formRows" :key="i" class="flex items-center justify-between p-4 border border-stone-100 rounded-2xl bg-white shadow-sm">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#F9F9F7] border border-stone-100 overflow-hidden shrink-0 flex items-center justify-center">
                      <img v-if="row.fotoPreview" :src="row.fotoPreview" class="w-full h-full object-cover" />
                      <Icon v-else icon="fluent-emoji:package" class="w-8 h-8" />
                    </div>
                    <div>
                      <p class="font-black text-stone-800 text-sm">{{ getSampahName(row.sampah_id) }}</p>
                      <p class="text-[11px] font-bold text-stone-400 mt-0.5">{{ row.berat_timbang }} kg × {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}/kg</p>
                    </div>
                  </div>
                  <p class="font-black text-[#4A7043]">{{ formatRupiah(getRowTotal(row)) }}</p>
                </div>
              </div>
            </div>

            <!-- Tipe Transaksi Preview -->
            <div class="space-y-3">
              <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Tipe Transaksi:</p>
              <div class="w-full bg-white border border-stone-100 shadow-sm rounded-xl p-3.5 text-stone-600 font-bold text-sm">
                Penjemputan
              </div>
            </div>

            <!-- Total Preview -->
            <div class="bg-[#4A7043] rounded-[1.5rem] overflow-hidden shadow-lg">
              <div class="p-6 flex justify-between items-center text-white border-b border-white/10">
                <div>
                  <p class="text-white/70 text-xs font-bold mb-1">Total Berat</p>
                  <p class="text-lg font-black">{{ totalBerat.toFixed(1) }} kg</p>
                </div>
                <div class="text-right">
                  <p class="text-white/70 text-xs font-bold mb-1">Total Nilai</p>
                  <p class="text-2xl font-black">{{ formatRupiah(totalNilai) }}</p>
                </div>
              </div>
              <div class="bg-[#3D5C37] p-5 flex justify-between items-center text-white">
                <p class="text-white/70 text-xs font-bold">Saldo Setelah Transaksi:</p>
                <p class="text-lg font-black">{{ formatRupiah(saldoSetelahTransaksi) }}</p>
              </div>
            </div>

            <!-- Info text -->
            <div class="bg-blue-50 text-blue-600 text-xs font-bold p-4 rounded-xl flex items-start gap-2">
              <Icon icon="material-symbols:info" class="w-5 h-5 shrink-0" />
              <p>Nilai transaksi akan LANGSUNG masuk ke saldo nasabah setelah disimpan</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-4">
            <button @click="step = 1" class="flex-[1] py-4 rounded-2xl bg-[#E8ECE8] text-[#4A7043] font-bold hover:bg-[#DCE3DC] transition-all flex justify-center items-center gap-2">
              <Icon icon="material-symbols:arrow-back" class="w-5 h-5" /> Edit Data
            </button>
            <button @click="submitPenimbangan" :disabled="isSubmitting" class="flex-[2] py-4 rounded-2xl bg-[#4A7043] text-white font-bold hover:bg-[#3D5C37] transition-all flex justify-center items-center gap-2 shadow-lg">
              <Icon v-if="isSubmitting" icon="line-md:loading-twotone-loop" class="w-5 h-5" />
              <Icon v-else icon="material-symbols:save-outline" class="w-5 h-5" />
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi' }}
            </button>
          </div>
        </div>

        <!-- STEP 3: Success -->
        <div v-else-if="step === 3" class="space-y-6 flex flex-col items-center">
          <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mb-2 animate-in zoom-in duration-500">
            <Icon icon="material-symbols:check-circle-outline" class="w-16 h-16" />
          </div>
          
          <div class="text-center space-y-2 mb-4 animate-in slide-in-from-bottom-4 duration-700">
            <h3 class="text-2xl font-black text-stone-800">Penimbangan Berhasil Tersimpan!</h3>
            <p class="text-sm font-medium text-stone-500">Detail Penimbangan Nasabah Telah Berhasil<br>Disimpan Ke Database!</p>
          </div>

          <!-- Struk/Nota Card -->
          <div class="w-full bg-white rounded-t-3xl rounded-b-xl shadow-lg border border-stone-200 overflow-hidden relative animate-in slide-in-from-bottom-8 duration-700 delay-100">
            <!-- Struk Header -->
            <div class="p-6 border-b border-dashed border-stone-300 text-center space-y-1 bg-[#F9F9F7]">
              <h4 class="text-lg font-black text-[#4A7043] tracking-widest">BANK SAMPAH</h4>
              <p class="text-xs font-bold text-stone-500">Struk Penimbangan Transaksi Nasabah</p>
            </div>
            
            <div class="p-6 space-y-6">
              <!-- Meta Data -->
              <div class="space-y-2 text-xs font-bold text-stone-600">
                <div class="flex justify-between"><span class="text-stone-400">ID Transaksi:</span> <span class="text-stone-800">{{ receiptData?.transaksi_id ? 'TR-' + String(receiptData.transaksi_id).padStart(3, '0') : 'TR-NEW' }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">ID Penjemputan:</span> <span class="text-stone-800">REQ-{{ String(penjemputan?.penjemputan_id).padStart(3, '0') }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Gudang:</span> <span class="text-stone-800">{{ penjemputan?.gudang?.alamat || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Tanggal:</span> <span class="text-stone-800">{{ formatDate(new Date().toISOString()) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Petugas Input:</span> <span class="text-stone-800">{{ penjemputan?.petugas?.nama || '-' }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Tukang:</span> <span class="text-stone-800">{{ penjemputan?.tukang?.nama || '-' }}</span></div>
              </div>

              <!-- Nasabah -->
              <div class="space-y-1 pt-4 border-t border-stone-100">
                <p class="text-xs font-black text-stone-800">Nasabah:</p>
                <h5 class="text-sm font-black text-stone-800">{{ penjemputan?.nasabah?.nama }}</h5>
                <p class="text-[10px] font-bold text-stone-400">NSB-{{ String(penjemputan?.nasabah_id).padStart(3, '0') }}</p>
              </div>

              <!-- Foto Sampah Thumbnail -->
              <div class="space-y-2 pt-4 border-t border-stone-100">
                <p class="text-xs font-black text-stone-800">Foto Sampah:</p>
                <div class="flex flex-wrap gap-2">
                  <div v-for="(row, i) in formRows" :key="i" class="w-12 h-12 rounded-lg border border-stone-200 overflow-hidden bg-[#F9F9F7] flex items-center justify-center">
                    <img v-if="row.fotoPreview" :src="row.fotoPreview" class="w-full h-full object-cover" />
                    <Icon v-else icon="fluent-emoji:package" class="w-6 h-6" />
                  </div>
                </div>
              </div>

              <!-- Rincian -->
              <div class="space-y-3 pt-4 border-t border-stone-100">
                <p class="text-xs font-black text-stone-800">Detail Sampah:</p>
                <div v-for="(row, i) in formRows" :key="i" class="flex justify-between items-start">
                  <div>
                    <p class="text-xs font-black text-stone-700">{{ getSampahName(row.sampah_id) }}</p>
                    <p class="text-[10px] font-bold text-stone-400">{{ row.berat_timbang }} kg × {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}</p>
                  </div>
                  <p class="text-xs font-black text-stone-700">{{ formatRupiah(getRowTotal(row)) }}</p>
                </div>
              </div>

              <!-- Total Bottom -->
              <div class="pt-4 border-t border-stone-100 space-y-2">
                <div class="flex justify-between text-xs font-bold text-stone-500">
                  <span>Total Berat:</span>
                  <span class="text-stone-800">{{ totalBerat.toFixed(1) }} kg</span>
                </div>
                <div class="flex justify-between items-end">
                  <span class="text-sm font-black text-stone-800 uppercase">Total Nilai:</span>
                  <span class="text-xl font-black text-[#4A7043]">{{ formatRupiah(totalNilai) }}</span>
                </div>
              </div>

              <!-- Saldo Recap Box -->
              <div class="bg-green-50/50 rounded-xl p-4 space-y-1.5 border border-green-100">
                <div class="flex justify-between text-[11px] font-bold text-stone-500">
                  <span>Saldo Sebelum:</span>
                  <span>{{ formatRupiah(saldoSaatIni) }}</span>
                </div>
                <div class="flex justify-between text-[11px] font-bold text-green-600">
                  <span>Nilai Tambahan:</span>
                  <span>+ {{ formatRupiah(totalNilai) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-stone-800 pt-1.5 border-t border-green-200/50">
                  <span>Saldo Sesudah:</span>
                  <span>{{ formatRupiah(saldoSetelahTransaksi) }}</span>
                </div>
              </div>

            </div>
          </div>

          <button @click="router.push('/dashboard-petugas/listpenjemputan')" class="w-full py-4 rounded-2xl bg-[#4A7043] text-white font-bold hover:bg-[#3D5C37] transition-all shadow-lg mt-4 animate-in fade-in duration-1000">
            Kembali
          </button>
        </div>

      </template>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";

const route = useRoute();
const router = useRouter(); 
const penjemputan = ref(null);
const loading = ref(true);
const error = ref(null);

const listKategori = ref([]);
const listSampah = ref([]);

const isSubmitting = ref(false);
const step = ref(1); 
const isSuccess = ref(false);
const receiptData = ref(null);

const formRows = ref([{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null }]);

const saldoSaatIni = 320000;
const saldoSetelahTransaksi = computed(() => saldoSaatIni + totalNilai.value);

const handleFileUpload = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    formRows.value[index].foto = file;
    formRows.value[index].fotoPreview = URL.createObjectURL(file);
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
  if (formRows.value.length > 1) {
    formRows.value.splice(index, 1);
  }
};

const filteredSampah = (kategoriId) => {
  if (!kategoriId) return [];
  return listSampah.value.filter(item => item.item_sampah && item.item_sampah.kategori_id === kategoriId);
};

const getHargaPerKg = (sampah_id) => {
  if (!sampah_id) return 0;
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  if (selectedItem && selectedItem.item_sampah) {
    return Number(selectedItem.item_sampah.harga_beli);
  }
  return 0;
};

const getRowTotal = (row) => {
  const berat = Number(row.berat_timbang) || 0;
  return berat * getHargaPerKg(row.sampah_id);
};

const totalBerat = computed(() => {
  return formRows.value.reduce((sum, row) => sum + (Number(row.berat_timbang) || 0), 0);
});

const totalNilai = computed(() => {
  return formRows.value.reduce((sum, row) => sum + getRowTotal(row), 0);
});

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(angka);
};

const getSampahName = (sampah_id) => {
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  return selectedItem?.item_sampah?.nama || 'Unknown';
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + 
         date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const isFormValid = computed(() => {
  // Pastikan setiap row punya kategori, sampah, dan berat
  const validItems = formRows.value.filter(row => row.sampah_id !== "" && row.berat_timbang > 0);
  return validItems.length === formRows.value.length && validItems.length > 0;
});

const goToPreview = () => {
  if (isFormValid.value) {
    step.value = 2;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const fetchData = async () => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) throw new Error("Otentikasi diperlukan.");
    const penjemputanId = route.params.id;
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get(`http://localhost:8000/api/petugas/showpenjemputan/${penjemputanId}`, { headers });
    penjemputan.value = response.data.data;
  } catch (err) {
    error.value = "Gagal mengambil data detail penjemputan. " + (err.response ? err.response.data.message : err.message);
  } finally {
    loading.value = false;
  }
};

const fetchListKategori = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/list-kategori", { headers });
    listKategori.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil daftar kategori:", err);
  }
};

const fetchListSampah = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/list-sampah", { headers });
    listSampah.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil daftar sampah:", err);
  }
};

const submitPenimbangan = async () => {
  isSubmitting.value = true;
  
  try {
    const token = sessionStorage.getItem("token");
    const headers = { 
      Authorization: `Bearer ${token}`,
      "Content-Type": "multipart/form-data" 
    };

    const validItems = formRows.value.filter(row => row.sampah_id !== "" && row.berat_timbang > 0);

    const formData = new FormData();
    formData.append("nasabah_id", penjemputan.value.nasabah_id);
    formData.append("penjemputan_id", penjemputan.value.penjemputan_id);
    // Menggunakan tukang dari penjemputan data
    formData.append("tukang_id", penjemputan.value.tukang_id || penjemputan.value.tukang?.tukang_id);
    
    validItems.forEach((item, index) => {
      formData.append(`items[${index}][sampah_id]`, item.sampah_id);
      formData.append(`items[${index}][berat_timbang]`, item.berat_timbang);
      if (item.foto) {
        formData.append(`items[${index}][foto]`, item.foto);
      }
    });

    const response = await axios.post("http://localhost:8000/api/petugas/penimbangan", formData, { headers });
    
    isSuccess.value = true;
    receiptData.value = response.data.data || { transaksi_id: response.data.transaksi_id }; // Mocking response structure handling
    
    step.value = 3;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
  } catch (err) {
    alert("Gagal menyimpan: " + (err.response ? err.response.data.message : err.message));
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
  fetchListKategori(); 
  fetchListSampah();
});
</script>