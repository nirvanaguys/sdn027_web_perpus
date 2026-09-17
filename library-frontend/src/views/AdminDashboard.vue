<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-8 gap-4">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold mb-2">
          <ShieldCheck class="w-3.5 h-3.5" />
          <span>Panel Kontrol Pustakawan</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Inventaris Buku</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data buku, penataan nomor rak fisik, dan sirkulasi stok.</p>
      </div>

      <button
        @click="openModal('add')"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-2xl shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/35 transition duration-200 cursor-pointer self-start sm:self-auto"
      >
        <Plus class="w-5 h-5" />
        <span>Tambah Buku Baru</span>
      </button>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Total Judul</span>
          <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <BookOpen class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-800 mt-2">{{ books.length }}</div>
        <div class="text-xs text-slate-400 mt-1">Koleksi terdata</div>
      </div>

      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Total Stok Fisik</span>
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Layers class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-800 mt-2">{{ totalCopies }}</div>
        <div class="text-xs text-slate-400 mt-1">Eksemplar di rak</div>
      </div>

      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Buku Tersedia</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <CheckCircle class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-emerald-600 mt-2">{{ inStockCount }}</div>
        <div class="text-xs text-slate-400 mt-1">Siap dipinjam</div>
      </div>

      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Stok Kosong</span>
          <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
            <AlertCircle class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-rose-600 mt-2">{{ outOfStockCount }}</div>
        <div class="text-xs text-slate-400 mt-1">Perlu pengadaan</div>
      </div>
    </div>

    <!-- Table Container with Search & Filters -->
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
          <input
            v-model="tableSearch"
            type="text"
            placeholder="Cari judul, penulis, rak, atau ISBN..."
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
          />
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
          <span class="text-slate-400">Filter Stok:</span>
          <select
            v-model="stockFilter"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="all">Semua Status</option>
            <option value="available">Tersedia Saja</option>
            <option value="empty">Stok Habis</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-xs sm:text-sm">
          <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
            <tr>
              <th class="px-6 py-4">Buku & Penulis</th>
              <th class="px-6 py-4">Kategori</th>
              <th class="px-6 py-4">Penerbit</th>
              <th class="px-6 py-4">Kode ISBN</th>
              <th class="px-6 py-4">Lokasi Rak</th>
              <th class="px-6 py-4 text-center">Stok</th>
              <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="book in filteredTableBooks"
              :key="book.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="font-bold text-slate-900 leading-snug">{{ book.title }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Penulis: {{ book.author }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                  {{ book.category || 'Umum' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-600">{{ book.publisher }}</td>
              <td class="px-6 py-4 font-mono text-xs text-slate-500">
                <span class="bg-slate-100 px-2 py-1 rounded-md">{{ book.isbn }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700">
                  <MapPin class="w-3 h-3" />
                  {{ book.shelf_location }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="book.stock > 0 
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' 
                    : 'bg-rose-50 text-rose-700 border border-rose-200/80'"
                >
                  {{ book.stock > 0 ? `${book.stock} unit` : 'Habis' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="openModal('edit', book)"
                    title="Edit Buku"
                    class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 transition cursor-pointer"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    @click="askDeleteBook(book)"
                    title="Hapus Buku"
                    class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredTableBooks.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                <BookOpen class="w-8 h-8 mx-auto mb-2 opacity-50" />
                <span>Tidak ada data buku yang sesuai.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Buku) -->
    <transition
      enter-active-class="ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
        @click.self="isModalOpen = false"
      >
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-100 relative">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
              <h3 class="text-xl font-bold text-slate-900">
                {{ modalMode === 'add' ? 'Tambah Koleksi Buku' : 'Perbarui Data Buku' }}
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">Isi rincian buku untuk katalog perpustakaan.</p>
            </div>
            <button
              @click="isModalOpen = false"
              class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="saveBook" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Buku</label>
              <input
                v-model="form.title"
                required
                type="text"
                placeholder="Contoh: Laskar Pelangi"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Penulis</label>
                <input
                  v-model="form.author"
                  required
                  type="text"
                  placeholder="Nama Penulis"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Penerbit</label>
                <input
                  v-model="form.publisher"
                  required
                  type="text"
                  placeholder="Nama Penerbit"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kode ISBN</label>
                <input
                  v-model="form.isbn"
                  required
                  type="text"
                  placeholder="978-xxx-xxx"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Stok Fisik</label>
                <input
                  v-model.number="form.stock"
                  required
                  min="0"
                  type="number"
                  placeholder="0"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
                <input
                  v-model="form.category"
                  required
                  type="text"
                  placeholder="Contoh: Teknologi, Fiksi, Sains"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Lokasi Rak Fisik</label>
                <input
                  v-model="form.shelf_location"
                  required
                  type="text"
                  placeholder="Contoh: Rak A-02"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button
                type="button"
                @click="isModalOpen = false"
                class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2 cursor-pointer"
              >
                <div
                  v-if="submitting"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                ></div>
                <span>{{ submitting ? 'Menyimpan...' : 'Simpan Data' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteModal"
      title="Hapus Koleksi Buku"
      :message="`Apakah Anda yakin ingin menghapus buku '${bookToDelete?.title}' dari sistem perpustakaan? Tindakan ini tidak dapat dibatalkan.`"
      confirmText="Ya, Hapus Buku"
      cancelText="Batal"
      :isDanger="true"
      :loading="deleting"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { toastStore } from '../stores/toast';
import ConfirmModal from '../components/ConfirmModal.vue';
import {
  ShieldCheck,
  Plus,
  BookOpen,
  Layers,
  CheckCircle,
  AlertCircle,
  Search,
  MapPin,
  Edit3,
  Trash2,
  X
} from 'lucide-vue-next';

const books = ref([]);
const tableSearch = ref('');
const stockFilter = ref('all');

const isModalOpen = ref(false);
const modalMode = ref('add');
const submitting = ref(false);
const selectedBookId = ref(null);

const showDeleteModal = ref(false);
const bookToDelete = ref(null);
const deleting = ref(false);

const form = ref({
  title: '',
  author: '',
  publisher: '',
  isbn: '',
  category: 'Umum',
  stock: 0,
  shelf_location: '',
});

const fetchBooks = async () => {
  try {
    const res = await api.get('/books');
    books.value = res.data.data.data || [];
  } catch (err) {
    console.error('Gagal mengambil data buku:', err);
    toastStore.error('Gagal mengambil data inventaris buku.');
  }
};

const totalCopies = computed(() => books.value.reduce((sum, b) => sum + (b.stock || 0), 0));
const inStockCount = computed(() => books.value.filter((b) => b.stock > 0).length);
const outOfStockCount = computed(() => books.value.filter((b) => b.stock <= 0).length);

const filteredTableBooks = computed(() => {
  return books.value.filter((b) => {
    // Search match
    if (tableSearch.value) {
      const q = tableSearch.value.toLowerCase();
      const match =
        b.title?.toLowerCase().includes(q) ||
        b.author?.toLowerCase().includes(q) ||
        b.isbn?.toLowerCase().includes(q) ||
        b.category?.toLowerCase().includes(q) ||
        b.shelf_location?.toLowerCase().includes(q);
      if (!match) return false;
    }

    // Stock filter
    if (stockFilter.value === 'available' && b.stock <= 0) return false;
    if (stockFilter.value === 'empty' && b.stock > 0) return false;

    return true;
  });
});

const openModal = (mode, book = null) => {
  modalMode.value = mode;
  if (mode === 'edit' && book) {
    selectedBookId.value = book.id;
    form.value = { category: 'Umum', ...book };
  } else {
    selectedBookId.value = null;
    form.value = { title: '', author: '', publisher: '', isbn: '', category: 'Umum', stock: 5, shelf_location: '' };
  }
  isModalOpen.value = true;
};

const saveBook = async () => {
  submitting.value = true;
  try {
    if (modalMode.value === 'add') {
      await api.post('/admin/books', form.value);
      toastStore.success('Buku baru berhasil ditambahkan ke katalog!');
    } else {
      await api.put(`/admin/books/${selectedBookId.value}`, form.value);
      toastStore.success('Data buku berhasil diperbarui!');
    }
    isModalOpen.value = false;
    fetchBooks();
  } catch (err) {
    const msg = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.';
    toastStore.error(msg);
  } finally {
    submitting.value = false;
  }
};

const askDeleteBook = (book) => {
  bookToDelete.value = book;
  showDeleteModal.value = true;
};

const executeDelete = async () => {
  if (!bookToDelete.value) return;
  deleting.value = true;
  try {
    await api.delete(`/admin/books/${bookToDelete.value.id}`);
    toastStore.success('Buku berhasil dihapus dari inventaris.');
    showDeleteModal.value = false;
    fetchBooks();
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menghapus buku.';
    toastStore.error(msg);
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  fetchBooks();
});
</script>
