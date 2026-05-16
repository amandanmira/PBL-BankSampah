<script setup>
import { ref, watch, onMounted, inject } from 'vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { Icon } from '@iconify/vue';
import { X, Image as ImageIcon, Loader2 } from 'lucide-vue-next';

const axios = inject('axios');

const props = defineProps({
  isOpen: Boolean,
  newsData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const form = ref({
  judul: '',
  kategori: 'Berita',
  isi: '',
  thumbnail: null
});

const imagePreview = ref(null);
const fileInput = ref(null);
const isLoading = ref(false);

const resetForm = () => {
  form.value = {
    judul: '',
    kategori: 'Berita',
    isi: '',
    thumbnail: null
  };
  imagePreview.value = null;
};

watch(() => props.newsData, (newVal) => {
  if (newVal) {
    form.value = {
      judul: newVal.judul || '',
      kategori: newVal.kategori || 'Berita',
      isi: newVal.isi || '',
      thumbnail: null
    };
    imagePreview.value = newVal.thumbnail ? `${axios.defaults.baseURL}/storage/${newVal.thumbnail}` : null;
  } else {
    resetForm();
  }
}, { immediate: true });


const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.thumbnail = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleSubmit = async () => {
  isLoading.value = true;
  try {
    const formData = new FormData();
    formData.append('judul', form.value.judul);
    formData.append('kategori', form.value.kategori);
    formData.append('isi', form.value.isi);
    
    if (form.value.thumbnail) {
      formData.append('thumbnail', form.value.thumbnail);
    }

    emit('saved', formData);
  } catch (error) {
    console.error('Submit error:', error);
  } finally {
    isLoading.value = false;
  }
};

const close = () => {
  emit('close');
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="close"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl animate-in fade-in zoom-in duration-300">
      <!-- Header -->
      <div class="flex items-center justify-between p-8 border-b border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800">{{ newsData ? 'Edit Berita' : 'Buat Berita Baru' }}</h2>
        <button @click="close" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
          <X class="w-6 h-6 text-gray-400" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
        <!-- Thumbnail Upload -->
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Gambar Berita</label>
          <div 
            @click="triggerFileInput"
            class="relative aspect-[21/9] w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 hover:border-gray-300 transition-all overflow-hidden"
          >
            <input 
              type="file" 
              ref="fileInput" 
              class="hidden" 
              accept="image/*"
              @change="handleFileChange"
            />
            
            <template v-if="imagePreview">
              <img :src="imagePreview" class="w-full h-full object-cover" />
              <div class="absolute inset-0 bg-black/20 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center text-white font-medium">
                Klik untuk ganti gambar
              </div>
            </template>
            <template v-else>
              <div class="bg-white p-4 rounded-2xl shadow-sm mb-3">
                <ImageIcon class="w-8 h-8 text-gray-400" />
              </div>
              <p class="text-gray-500 font-medium">Klik untuk upload gambar</p>
            </template>
          </div>
        </div>

        <!-- Judul & Kategori Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 space-y-2">
            <label class="text-sm font-semibold text-gray-600">Judul Berita</label>
            <input 
              v-model="form.judul"
              type="text" 
              placeholder="Masukkan judul berita..."
              class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all"
              required
            />
          </div>
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Kategori</label>
            <select 
              v-model="form.kategori"
              class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] outline-none transition-all appearance-none cursor-pointer"
            >
              <option value="Berita">Berita</option>
              <option value="Artikel">Artikel</option>
              <option value="Event">Event</option>
            </select>
          </div>
        </div>

        <!-- Editor -->
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Deskripsi / Konten Berita</label>
          <div class="border border-gray-200 rounded-xl overflow-hidden min-h-[400px]">
            <MdEditor 
              v-model="form.isi" 
              language="en-US"
              :preview="false"
              placeholder="Tulis konten berita Anda di sini..."
              class="!border-none"
            />
          </div>
          <p class="text-[10px] text-gray-400 flex items-center gap-1.5 px-1">
            <Icon icon="material-symbols:lightbulb-outline" class="w-3 h-3 text-yellow-500" />
            Tips: Gunakan markdown untuk formatting. **bold**, *italic*, # Heading, - List
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
          <button 
            type="button"
            @click="close"
            class="px-8 py-3 rounded-xl font-semibold text-gray-600 hover:bg-gray-100 transition-colors"
          >
            Batal
          </button>
          <button 
            type="submit"
            :disabled="isLoading"
            class="px-8 py-3 bg-[#4A7043] text-white rounded-xl font-semibold shadow-lg shadow-[#4A7043]/20 hover:bg-[#3D5C37] transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
            {{ newsData ? 'Simpan Perubahan' : 'Buat Berita' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
/* Styling md-editor-v3 to match the UI */
.md-editor {
  --md-theme-color: #4A7043;
}
.md-editor-toolbar-wrapper {
  background-color: #f9fafb !important;
  border-bottom: 1px solid #e5e7eb !important;
}
</style>
