<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold mb-2">
          <Bookmark class="w-3.5 h-3.5" />
          <span>Sirkulasi Mandiri Anggota</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Peminjaman Buku Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau buku yang sedang Anda pinjam, tanggal jatuh tempo, dan riwayat pengembalian.</p>
      </div>

      <router-link
        to="/"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition self-start sm:self-auto cursor-pointer"
      >
        <Library class="w-4 h-4" />
        <span>Katalog Perpustakaan</span>
      </router-link>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <Clock class="w-6 h-6" />
        </div>
        <div>
          <span class="text-xs font-medium text-slate-400">Sedang Dipinjam</span>
          <h3 class="text-2xl font-extrabold text-slate-800">{{ activeLoans.length }} Buku</h3>
        </div>
      </div>

      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <CheckCircle class="w-6 h-6" />
        </div>
        <div>
          <span class="text-xs font-medium text-slate-400">Telah Dikembalikan</span>
          <h3 class="text-2xl font-extrabold text-slate-800">{{ returnedLoans.length }} Buku</h3>
        </div>
      </div>

      <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
          <BookOpen class="w-6 h-6" />
        </div>
        <div>
          <span class="text-xs font-medium text-slate-400">Total Riwayat Pinjaman</span>
          <h3 class="text-2xl font-extrabold text-slate-800">{{ loans.length }} Transaksi</h3>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="currentTab = tab.key"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer"
        :class="currentTab === tab.key 
          ? 'bg-indigo-600 text-white shadow-xs' 
          : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
      >
        {{ tab.label }} ({{ tab.count }})
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="py-16 flex justify-center items-center">
      <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredLoans.length === 0"
      class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto shadow-xs"
    >
      <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
        <BookOpen class="w-8 h-8 opacity-70" />
      </div>
      <h3 class="text-lg font-bold text-slate-900">Belum ada catatan peminjaman</h3>
      <p class="text-sm text-slate-500 mt-1">
        {{ currentTab === 'borrowed' ? 'Anda tidak memiliki pinjaman buku yang sedang aktif.' : 'Belum ada transaksi peminjaman pada tab ini.' }}
      </p>
      <router-link
        to="/"
        class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition cursor-pointer shadow-md shadow-indigo-500/20"
      >
        <span>Pinjam Buku Sekarang</span>
        <ArrowRight class="w-4 h-4" />
      </router-link>
    </div>

    <!-- Loans List -->
    <div v-else class="space-y-4">
      <div
        v-for="item in filteredLoans"
        :key="item.id"
        class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs hover:border-indigo-200 hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-5"
      >
        <div class="flex items-start gap-4">
          <div
            class="w-12 h-14 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-xs"
            :class="item.status === 'borrowed' ? 'bg-gradient-to-tr from-indigo-600 to-violet-600' : 'bg-slate-400'"
          >
            <BookOpen class="w-6 h-6" />
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                :class="item.status === 'borrowed'
                  ? 'bg-amber-50 text-amber-700 border border-amber-200/80'
                  : 'bg-emerald-50 text-emerald-700 border border-emerald-200/80'"
              >
                {{ item.status === 'borrowed' ? 'Sedang Dipinjam' : 'Sudah Dikembalikan' }}
              </span>
              <span class="text-xs text-slate-400 font-mono">ISBN: {{ item.book?.isbn || '-' }}</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
              {{ item.book?.title || 'Judul Buku' }}
            </h3>
            <p class="text-xs text-slate-500 mt-1">
              Penulis: <strong class="text-slate-700">{{ item.book?.author || '-' }}</strong> • Lokasi: Rak {{ item.book?.shelf_location || '-' }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
              <div class="flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                <span>Dipinjam: <strong>{{ formatDate(item.borrow_date) }}</strong></span>
              </div>
              <div class="flex items-center gap-1.5" :class="isOverdue(item) ? 'text-rose-600 font-bold' : ''">
                <Clock class="w-3.5 h-3.5" />
                <span>Batas Kembali: <strong>{{ formatDate(item.due_date) }}</strong></span>
                <span v-if="isOverdue(item)" class="text-[11px] px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md font-bold">Terlambat</span>
              </div>
              <div v-if="item.return_date" class="flex items-center gap-1.5 text-emerald-700">
                <CheckCircle class="w-3.5 h-3.5" />
                <span>Dikembalikan: {{ formatDate(item.return_date) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Area -->
        <div class="sm:shrink-0 flex items-center justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
          <button
            v-if="item.status === 'borrowed'"
            @click="askReturnBook(item)"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm hover:shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer"
          >
            <RotateCcw class="w-4 h-4" />
            <span>Kembalikan Buku</span>
          </button>
          <div v-else class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
            <CheckCircle class="w-4 h-4 text-emerald-500" />
            <span>Selesai</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Modal Kembalikan Buku -->
    <ConfirmModal
      :isOpen="showReturnModal"
      title="Konfirmasi Pengembalian Buku"
      :message="`Apakah Anda ingin mengembalikan buku '${loanToReturn?.book?.title}'? Stok fisik akan otomatis ditambahkan kembali ke inventaris perpustakaan.`"
      confirmText="Ya, Kembalikan Buku"
      cancelText="Batalkan"
      :loading="returning"
      @confirm="executeReturn"
      @cancel="showReturnModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { toastStore } from '../stores/toast';
import ConfirmModal from '../components/ConfirmModal.vue';
import {
  Bookmark,
  Library,
  BookOpen,
  Clock,
  CheckCircle,
  Calendar,
  RotateCcw,
  ArrowRight
} from 'lucide-vue-next';

const loans = ref([]);
const loading = ref(false);
const currentTab = ref('borrowed');

const showReturnModal = ref(false);
const loanToReturn = ref(null);
const returning = ref(false);

const fetchLoans = async () => {
  loading.value = true;
  try {
    const res = await api.get('/transactions/my');
    loans.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load transactions:', err);
    toastStore.error('Gagal memuat daftar peminjaman.');
  } finally {
    loading.value = false;
  }
};

const activeLoans = computed(() => loans.value.filter((l) => l.status === 'borrowed'));
const returnedLoans = computed(() => loans.value.filter((l) => l.status === 'returned'));

const tabs = computed(() => [
  { key: 'borrowed', label: 'Sedang Dipinjam', count: activeLoans.value.length },
  { key: 'returned', label: 'Sudah Dikembalikan', count: returnedLoans.value.length },
  { key: 'all', label: 'Semua Riwayat', count: loans.value.length },
]);

const filteredLoans = computed(() => {
  if (currentTab.value === 'borrowed') return activeLoans.value;
  if (currentTab.value === 'returned') return returnedLoans.value;
  return loans.value;
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(date);
};

const isOverdue = (item) => {
  if (item.status !== 'borrowed' || !item.due_date) return false;
  return new Date(item.due_date) < new Date();
};

const askReturnBook = (loan) => {
  loanToReturn.value = loan;
  showReturnModal.value = true;
};

const executeReturn = async () => {
  if (!loanToReturn.value) return;
  returning.value = true;
  try {
    const res = await api.put(`/transactions/return/${loanToReturn.value.id}`);
    toastStore.success(res.data.message || 'Buku berhasil dikembalikan!');
    showReturnModal.value = false;
    fetchLoans();
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal mengembalikan buku.';
    toastStore.error(msg);
  } finally {
    returning.value = false;
  }
};

onMounted(() => {
  fetchLoans();
});
</script>
