<template>
  <div>
    <!-- Modern Hero Section with Ambient Glow -->
    <section
      :style="{ backgroundImage: `url(${schoolImg})` }"
      class="relative overflow-hidden bg-cover bg-center text-white pt-14 pb-20 px-4 sm:px-6 lg:px-8"
    >
      <!-- Background Glowing Orbs -->
      <div
        class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"
      ></div>
      <div
        class="absolute -bottom-10 right-1/4 w-96 h-96 bg-violet-500/15 rounded-full blur-3xl pointer-events-none"
      ></div>

      <div class="absolute inset-0 bg-black/40 pointer-events-none"></div>

      <div class="relative max-w-5xl mx-auto text-center space-y-6 z-10">
        <!-- Badge -->
        <div
          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-indigo-300 text-xs font-semibold tracking-wide shadow-inner"
        >
          <span>Sistem Manajemen Perpustakaan Terpadu</span>
        </div>

        <!-- Headline -->
        <h1
          class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-indigo-200"
        >
          Perpustakaan Resmi
          <br class="hidden sm:block" />
          SDN 027 Balikpapan Utara
        </h1>

        <!-- Subtitle -->
        <p
          class="max-w-2xl mx-auto text-slate-300 text-sm sm:text-base leading-relaxed"
        ></p>

        <!-- Prominent Search Bar -->
        <div class="max-w-2xl mx-auto pt-2">
          <div
            class="relative flex items-center bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-1.5 shadow-2xl focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-transparent transition-all"
          >
            <div class="pl-3.5 text-slate-400">
              <Search class="w-5 h-5 text-indigo-300" />
            </div>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cari judul buku, nama penulis, penerbit, atau ISBN..."
              class="w-full px-3.5 py-2.5 bg-transparent text-white placeholder-slate-400 text-sm outline-none"
            />
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="p-2 text-slate-400 hover:text-white rounded-xl transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Metric Highlights -->
        <div
          class="flex flex-wrap items-center justify-center gap-6 pt-4 text-xs sm:text-sm text-slate-300"
        >
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span
              >Total <strong>{{ allBooks.length }}</strong> Judul
              Terdaftar</span
            >
          </div>
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
            <span
              ><strong>{{ availableBooksCount }}</strong> Buku Siap
              Dipinjam</span
            >
          </div>
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span
              ><strong>{{ uniqueShelvesCount }}</strong> Lokasi Rak Fisik</span
            >
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content: Catalog Grid & Filters -->
    <main
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 pb-16"
    >
      <!-- Filter Bar -->
      <div
        class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4"
      >
        <!-- Stock Filter Tabs -->
        <div
          class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0"
        >
          <button
            v-for="filter in filterOptions"
            :key="filter.value"
            @click="selectedFilter = filter.value"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer"
            :class="
              selectedFilter === filter.value
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            "
          >
            {{ filter.label }}
          </button>
        </div>

        <!-- Dropdown Filters: Category & Shelf Location -->
        <div
          class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end"
        >
          <Filter class="w-4 h-4 text-slate-400 shrink-0" />
          <select
            v-model="selectedCategory"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>

          <select
            v-model="selectedShelf"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="">Semua Lokasi Rak</option>
            <option v-for="shelf in uniqueShelves" :key="shelf" :value="shelf">
              Rak: {{ shelf }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading Skeleton Cards -->
      <div
        v-if="loading"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
      >
        <div
          v-for="n in 8"
          :key="n"
          class="bg-white rounded-3xl p-5 border border-slate-200/70 shadow-xs animate-pulse space-y-4"
        >
          <div class="h-32 bg-slate-200 rounded-2xl"></div>
          <div class="h-4 bg-slate-200 rounded w-3/4"></div>
          <div class="h-3 bg-slate-100 rounded w-1/2"></div>
          <div class="h-8 bg-slate-200 rounded-xl mt-4"></div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredBooks.length === 0"
        class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto shadow-xs"
      >
        <div
          class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4"
        >
          <BookOpen class="w-8 h-8 opacity-70" />
        </div>
        <h3 class="text-lg font-bold text-slate-900">Buku tidak ditemukan</h3>
        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
          Tidak ada koleksi yang cocok dengan filter atau kata kunci "{{
            searchQuery
          }}".
        </p>
        <button
          @click="resetFilters"
          class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition cursor-pointer"
        >
          <RotateCcw class="w-4 h-4" />
          <span>Reset Pencarian</span>
        </button>
      </div>

      <!-- Books Grid -->
      <div
        v-else
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
      >
        <div
          v-for="book in filteredBooks"
          :key="book.id"
          class="group bg-white rounded-3xl border border-slate-200/80 hover:border-indigo-300 shadow-xs hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden"
        >
          <!-- Card Header Spine Gradient -->
          <div
            class="h-36 p-5 flex flex-col justify-between text-white relative overflow-hidden"
            :class="getCardGradient(book.id)"
          >
            <!-- Decorative circle -->
            <div
              class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-white/10 blur-sm pointer-events-none"
            ></div>

            <div class="flex items-start justify-between z-10">
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-black/25 backdrop-blur-md"
              >
                <MapPin class="w-3.5 h-3.5" />
                <span>{{ book.shelf_location }}</span>
              </span>

              <!-- Live Stock Indicator Pill -->
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold shadow-xs backdrop-blur-md"
                :class="
                  book.stock > 0
                    ? 'bg-emerald-500/90 text-white'
                    : 'bg-rose-500/90 text-white'
                "
              >
                <span
                  class="w-1.5 h-1.5 rounded-full bg-white animate-ping"
                ></span>
                <span>{{
                  book.stock > 0 ? `${book.stock} Ada` : "Habis"
                }}</span>
              </span>
            </div>

            <!-- Stylized Spine Typography -->
            <div class="z-10 flex items-center justify-between">
              <span
                class="font-mono text-[11px] text-white/80 bg-black/20 px-2 py-0.5 rounded-md"
              >
                ISBN: {{ book.isbn }}
              </span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-5 flex-1 flex flex-col">
            <div class="flex items-center gap-1.5 mb-2.5">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700"
              >
                {{ book.category || "Umum" }}
              </span>
            </div>

            <h3
              class="font-bold text-base text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug cursor-pointer"
              @click="openDetail(book)"
            >
              {{ book.title }}
            </h3>

            <div class="mt-3 space-y-1.5 text-xs text-slate-500">
              <div class="flex items-center gap-2">
                <User class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <span class="truncate"
                  >Penulis:
                  <strong class="text-slate-700 font-medium">{{
                    book.author
                  }}</strong></span
                >
              </div>
              <div class="flex items-center gap-2">
                <Bookmark class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <span class="truncate">Penerbit: {{ book.publisher }}</span>
              </div>
            </div>

            <!-- Action Area -->
            <div class="mt-auto pt-5 grid grid-cols-2 gap-2">
              <button
                @click="openDetail(book)"
                class="py-2.5 px-3 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Eye class="w-3.5 h-3.5" />
                <span>Detail</span>
              </button>

              <button
                @click="askBorrowBook(book)"
                :disabled="book.stock === 0"
                class="py-2.5 px-3 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
                :class="
                  book.stock > 0
                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/20'
                    : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                "
              >
                <BookOpen class="w-3.5 h-3.5" />
                <span>{{ book.stock > 0 ? "Pinjam" : "Kosong" }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Detail Buku -->
    <transition
      enter-active-class="ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="selectedDetailBook"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
        @click.self="selectedDetailBook = null"
      >
        <div
          class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100"
        >
          <div
            class="p-6 text-white relative"
            :class="getCardGradient(selectedDetailBook.id)"
          >
            <button
              @click="selectedDetailBook = null"
              class="absolute top-4 right-4 p-1.5 rounded-full bg-black/20 hover:bg-black/30 text-white transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
            <span
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-black/30 mb-3"
            >
              <MapPin class="w-3.5 h-3.5" />
              Rak: {{ selectedDetailBook.shelf_location }}
            </span>
            <h2 class="text-xl font-bold leading-snug">
              {{ selectedDetailBook.title }}
            </h2>
            <p class="text-sm text-white/80 mt-1">
              Karya {{ selectedDetailBook.author }}
            </p>
          </div>

          <div class="p-6 space-y-4">
            <div
              class="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs"
            >
              <div>
                <span class="text-slate-400 block mb-0.5">Kategori</span>
                <strong class="text-slate-800 text-sm font-semibold">{{
                  selectedDetailBook.category || "Umum"
                }}</strong>
              </div>
              <div>
                <span class="text-slate-400 block mb-0.5">Penerbit</span>
                <strong class="text-slate-800 text-sm font-semibold">{{
                  selectedDetailBook.publisher
                }}</strong>
              </div>
              <div>
                <span class="text-slate-400 block mb-0.5">Kode ISBN</span>
                <strong
                  class="font-mono text-slate-800 text-sm font-semibold"
                  >{{ selectedDetailBook.isbn }}</strong
                >
              </div>
              <div>
                <span class="text-slate-400 block mb-0.5">Stok Fisik</span>
                <span
                  class="font-bold inline-block px-2 py-0.5 rounded-md"
                  :class="
                    selectedDetailBook.stock > 0
                      ? 'bg-emerald-100 text-emerald-800'
                      : 'bg-rose-100 text-rose-800'
                  "
                >
                  {{ selectedDetailBook.stock }} Eksemplar
                </span>
              </div>
              <div class="col-span-2">
                <span class="text-slate-400 block mb-0.5">Masa Pinjam</span>
                <strong class="text-slate-800 text-sm font-semibold"
                  >7 Hari Kalender</strong
                >
              </div>
            </div>

            <div
              class="text-xs text-slate-500 leading-relaxed bg-indigo-50/60 p-3.5 rounded-xl border border-indigo-100/80"
            >
              💡 <strong>Ketentuan Sirkulasi:</strong> Anggota dapat meminjam
              buku fisik ini langsung di rak perpustakaan. Pengembalian dapat
              dilakukan melalui menu <em>Buku Saya</em>.
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button
                type="button"
                @click="selectedDetailBook = null"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer"
              >
                Tutup
              </button>
              <button
                type="button"
                @click="askBorrowBook(selectedDetailBook)"
                :disabled="selectedDetailBook.stock === 0"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition flex items-center gap-2 cursor-pointer shadow-md shadow-indigo-600/20"
                :class="
                  selectedDetailBook.stock > 0
                    ? 'bg-indigo-600 hover:bg-indigo-700'
                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                "
              >
                <BookOpen class="w-4 h-4" />
                <span>{{
                  selectedDetailBook.stock > 0
                    ? "Pinjam Buku Ini"
                    : "Stok Tidak Tersedia"
                }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Confirmation Modal Reusable -->
    <ConfirmModal
      :isOpen="showConfirmModal"
      title="Konfirmasi Peminjaman Buku"
      :message="`Apakah Anda yakin ingin meminjam buku '${bookToBorrow?.title}'? Batas pengembalian adalah 7 hari dari sekarang.`"
      confirmText="Ya, Pinjam Sekarang"
      cancelText="Batalkan"
      :loading="borrowing"
      @confirm="executeBorrow"
      @cancel="showConfirmModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../services/api";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import ConfirmModal from "../components/ConfirmModal.vue";
import {
  Sparkles,
  Search,
  X,
  BookOpen,
  MapPin,
  User,
  Bookmark,
  Filter,
  Eye,
  RotateCcw,
} from "lucide-vue-next";
// Use the actual path where you placed the image
import schoolImg from "../assets/library-frontend/sd027.jpg";

const router = useRouter();
const allBooks = ref([]);
const searchQuery = ref("");
const selectedFilter = ref("all");
const selectedCategory = ref("");
const selectedShelf = ref("");
const loading = ref(false);

const selectedDetailBook = ref(null);
const showConfirmModal = ref(false);
const bookToBorrow = ref(null);
const borrowing = ref(false);

let debounceTimer = null;

const filterOptions = [
  { label: "Semua Koleksi", value: "all" },
  { label: "Tersedia Saja", value: "available" },
  { label: "Stok Habis", value: "out_of_stock" },
];

const gradients = [
  "bg-gradient-to-tr from-indigo-600 to-violet-700",
  "bg-gradient-to-tr from-blue-600 to-indigo-700",
  "bg-gradient-to-tr from-teal-600 to-emerald-700",
  "bg-gradient-to-tr from-purple-600 to-pink-700",
  "bg-gradient-to-tr from-amber-600 to-orange-700",
  "bg-gradient-to-tr from-rose-600 to-red-700",
];

const getCardGradient = (id) => {
  return gradients[(id || 0) % gradients.length];
};

const fetchBooks = async () => {
  loading.value = true;
  try {
    const res = await api.get("/books", {
      params: { search: searchQuery.value },
    });
    allBooks.value = res.data.data.data || [];
  } catch (error) {
    console.error("Error fetching books:", error);
    toastStore.error("Gagal memuat data buku.");
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchBooks();
  }, 350);
};

const clearSearch = () => {
  searchQuery.value = "";
  fetchBooks();
};

const resetFilters = () => {
  searchQuery.value = "";
  selectedFilter.value = "all";
  selectedCategory.value = "";
  selectedShelf.value = "";
  fetchBooks();
};

const uniqueCategories = computed(() => {
  const cats = allBooks.value.map((b) => b.category).filter(Boolean);
  return [...new Set(cats)].sort();
});

const uniqueShelves = computed(() => {
  const shelves = allBooks.value.map((b) => b.shelf_location).filter(Boolean);
  return [...new Set(shelves)].sort();
});

const uniqueShelvesCount = computed(() => uniqueShelves.value.length);
const availableBooksCount = computed(
  () => allBooks.value.filter((b) => b.stock > 0).length,
);

const filteredBooks = computed(() => {
  return allBooks.value.filter((book) => {
    if (selectedFilter.value === "available" && book.stock <= 0) return false;
    if (selectedFilter.value === "out_of_stock" && book.stock > 0) return false;
    if (selectedCategory.value && book.category !== selectedCategory.value)
      return false;
    if (selectedShelf.value && book.shelf_location !== selectedShelf.value)
      return false;
    return true;
  });
});

const openDetail = (book) => {
  selectedDetailBook.value = book;
};

const askBorrowBook = (book) => {
  if (!authStore.isAuthenticated()) {
    toastStore.warning(
      "Silakan masuk (login) terlebih dahulu untuk meminjam buku.",
    );
    return router.push({ path: "/login", query: { redirect: "/" } });
  }
  if (book.stock <= 0) {
    toastStore.error("Maaf, stok buku ini sedang habis.");
    return;
  }
  bookToBorrow.value = book;
  showConfirmModal.value = true;
};

const executeBorrow = async () => {
  if (!bookToBorrow.value) return;
  borrowing.value = true;
  try {
    const res = await api.post("/transactions/borrow", {
      book_id: bookToBorrow.value.id,
    });
    toastStore.success(res.data.message || "Buku berhasil dipinjam!");
    showConfirmModal.value = false;
    selectedDetailBook.value = null;
    fetchBooks();
  } catch (err) {
    const msg = err.response?.data?.message || "Gagal meminjam buku.";
    toastStore.error(msg);
  } finally {
    borrowing.value = false;
  }
};

onMounted(() => {
  fetchBooks();
});
</script>
