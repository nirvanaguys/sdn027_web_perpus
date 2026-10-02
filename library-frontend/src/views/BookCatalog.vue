<template>
  <div class="catalog-page">
    <!-- Modern Hero Section with Ambient Glow -->
    <section
      :style="{ backgroundImage: `url(${schoolImg})` }"
      class="relative overflow-hidden bg-cover bg-center text-white px-4 py-12 sm:px-6 sm:py-16 lg:px-8"
    >
      <div class="absolute inset-0 bg-emerald-950/75 pointer-events-none"></div>

      <div class="relative max-w-4xl mx-auto space-y-4 py-2 text-left z-10">
        <!-- Badge -->
        <div
          class="inline-flex items-center gap-2 text-emerald-100 text-sm font-semibold"
        >
          <span>Perpustakaan Ebook Digital </span>
        </div>

        <!-- Headline -->
        <h1
          class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight text-white"
        >
          Perpustakaan Digital
          <br class="hidden sm:block" />
          SDN 027 Balikpapan Utara
        </h1>

        <!-- Subtitle -->
        <p
          class="max-w-xl text-emerald-50 text-sm sm:text-base leading-relaxed"
        >
          Mari membangun dunia mulai dari membaca buku!
        </p>

        <!-- Prominent Search Bar -->
        <div class="w-full max-w-2xl pt-2">
          <div
            class="relative flex items-center bg-white border border-white/20 rounded-md p-1.5 shadow-xl focus-within:ring-2 focus-within:ring-emerald-300 focus-within:border-transparent transition-all"
          >
            <div class="pl-3.5 text-slate-400">
              <Search class="w-5 h-5 text-emerald-800" />
            </div>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="search"
              placeholder="Judul, penulis, penerbit, atau ISBN"
              class="w-full px-3.5 py-2.5 bg-transparent text-slate-900 placeholder-slate-500 text-sm outline-none"
            />
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="p-2 text-slate-500 hover:text-slate-900 transition cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content: Catalog Grid & Filters -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">
      <!-- Filter Bar -->
      <div
        class="bg-white border-b border-slate-200 py-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4"
      >
        <!-- Format Filter Tabs -->
        <div
          class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0"
        >
          <button
            v-for="filter in formatFilterOptions"
            :key="filter.value"
            @click="selectedFormat = filter.value"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer"
            :class="
              selectedFormat === filter.value
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            "
          >
            {{ filter.label }}
          </button>
        </div>

        <!-- Dropdown Filter: Category -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <Filter class="w-4 h-4 text-slate-400 shrink-0" />
          <select
            v-model="selectedCategory"
            class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
              {{ cat }}
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
        <h3 class="text-lg font-bold text-slate-900">Ebook tidak ditemukan</h3>
        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
          Tidak ada koleksi ebook yang cocok dengan filter atau kata kunci "{{
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
          class="group flex min-w-0 flex-col"
        >
          <!-- Card Header Spine Gradient -->
          <div
            class="relative mb-3 aspect-[3/4] overflow-hidden bg-emerald-900 text-white"
          >
            <img
              v-if="book.cover_url"
              :src="assetUrl(book.cover_url)"
              :alt="`Sampul ${book.title}`"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
            />
            <div
              v-else
              class="flex h-full flex-col justify-between p-4 sm:p-5"
              :class="getCardGradient(book.id)"
            >
              <span
                class="text-[10px] font-semibold uppercase tracking-widest text-white/75"
                >{{ book.category || "Koleksi ebook" }}</span
              >
              <div>
                <div class="mb-2 h-px w-8 bg-white/60"></div>
                <p
                  class="line-clamp-4 font-serif text-lg font-semibold leading-tight sm:text-xl"
                >
                  {{ book.title }}
                </p>
                <p class="mt-2 line-clamp-1 text-xs text-white/75">
                  {{ book.author }}
                </p>
              </div>
            </div>
            <!-- Decorative circle -->
            <div
              class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-white/10 blur-sm pointer-events-none"
            ></div>

            <div class="flex items-start justify-between z-10">
              <!-- Format Badge (PDF / EPUB) -->
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md shadow-xs"
                :class="
                  (book.file_format || '').toLowerCase() === 'pdf'
                    ? 'bg-rose-500/90 text-white'
                    : 'bg-indigo-900/80 text-white'
                "
              >
                <FileText class="w-3.5 h-3.5" />
                <span>{{ (book.file_format || "PDF").toUpperCase() }}</span>
              </span>

              <!-- Digital Read Ready Badge -->
              <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/90 text-white backdrop-blur-md"
              >
                <CheckCircle2 class="w-3 h-3" />
                <span>Siap Baca</span>
              </span>
            </div>

            <!-- Stylized Spine Typography -->
            <div class="z-10 flex items-center justify-between">
              <span
                class="font-mono text-[11px] text-white/80 bg-black/20 px-2 py-0.5 rounded-md"
              >
                ISBN: {{ book.isbn }}
              </span>
              <span
                v-if="book.file_size_formatted"
                class="text-[11px] text-white/80 bg-black/20 px-2 py-0.5 rounded-md"
              >
                {{ book.file_size_formatted }}
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

            <!-- Action Area: Detail & Baca Online -->
            <div class="mt-auto pt-5 grid grid-cols-[1fr_auto_1fr] gap-2">
              <button
                @click="openDetail(book)"
                class="py-2.5 px-3 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Eye class="w-3.5 h-3.5" />
                <span>Detail</span>
              </button>

              <button
                v-if="authStore.isAuthenticated()"
                type="button"
                @click="toggleReadingList(book)"
                :aria-label="
                  savedBookIds.has(book.id)
                    ? 'Hapus dari Reading List'
                    : 'Simpan ke Reading List'
                "
                :title="
                  savedBookIds.has(book.id)
                    ? 'Hapus dari Reading List'
                    : 'Simpan ke Reading List'
                "
                class="p-2 text-slate-500 hover:text-emerald-800"
              >
                <Bookmark
                  class="h-4 w-4"
                  :fill="savedBookIds.has(book.id) ? 'currentColor' : 'none'"
                />
              </button>
              <button
                @click="handleReadBook(book)"
                class="py-2.5 px-3 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/20"
              >
                <BookOpen class="w-3.5 h-3.5" />
                <span>Baca Ebook</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Detail Ebook -->
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
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-black/30 mb-3"
            >
              <FileText class="w-3.5 h-3.5" />
              <span
                >Format:
                {{
                  (selectedDetailBook.file_format || "PDF").toUpperCase()
                }}</span
              >
            </span>
            <h2 class="text-xl font-bold leading-snug">
              {{ selectedDetailBook.title }}
            </h2>
            <p class="text-sm text-white/80 mt-1">
              Karya {{ selectedDetailBook.author }}
            </p>
          </div>

          <div class="p-6 space-y-4">
            <!-- Book Metadata -->
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
                <span class="text-slate-400 block mb-0.5">Ukuran File</span>
                <strong class="text-slate-800 text-sm font-semibold">
                  {{ selectedDetailBook.file_size_formatted || "Tersedia" }}
                </strong>
              </div>
            </div>

            <!-- Description -->
            <div v-if="selectedDetailBook.description" class="space-y-1">
              <span
                class="text-xs font-semibold text-slate-500 uppercase tracking-wider"
                >Sinopsis / Deskripsi:</span
              >
              <p
                class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100"
              >
                {{ selectedDetailBook.description }}
              </p>
            </div>

            <div
              class="text-xs text-slate-500 leading-relaxed bg-indigo-50/60 p-3.5 rounded-xl border border-indigo-100/80"
            >
              📖 <strong>Akses Membaca:</strong> Ebook ini dapat dibaca langsung
              secara online menggunakan pembaca terintegrasi. Anda wajib masuk
              (login) untuk membuka isi dokumen.
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
                @click="handleReadBook(selectedDetailBook)"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition flex items-center gap-2 cursor-pointer shadow-md shadow-indigo-600/20"
              >
                <BookOpen class="w-4 h-4" />
                <span>Baca Ebook Sekarang</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import api, { assetUrl } from "../services/api";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import {
  Sparkles,
  Search,
  X,
  BookOpen,
  User,
  Bookmark,
  Filter,
  Eye,
  RotateCcw,
  FileText,
  CheckCircle2,
} from "lucide-vue-next";
import schoolImg from "../assets/library-frontend/sd027.jpg";

const router = useRouter();
const allBooks = ref([]);
const searchQuery = ref("");
const selectedFormat = ref("all");
const selectedCategory = ref("");
const loading = ref(false);

const selectedDetailBook = ref(null);
const savedBookIds = ref(new Set());

let debounceTimer = null;

const formatFilterOptions = [
  { label: "Semua Format", value: "all" },
  { label: "PDF", value: "pdf" },
  { label: "EPUB", value: "epub" },
];

const coverColors = [
  "bg-emerald-900",
  "bg-slate-800",
  "bg-teal-800",
  "bg-stone-800",
  "bg-green-900",
  "bg-cyan-900",
];

const getCardGradient = (id) => {
  return coverColors[(id || 0) % coverColors.length];
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
    toastStore.error("Gagal memuat katalog ebook.");
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
  selectedFormat.value = "all";
  selectedCategory.value = "";
  fetchBooks();
};

const uniqueCategories = computed(() => {
  const cats = allBooks.value.map((b) => b.category).filter(Boolean);
  return [...new Set(cats)].sort();
});

const pdfCount = computed(
  () =>
    allBooks.value.filter((b) => (b.file_format || "").toLowerCase() === "pdf")
      .length,
);

const epubCount = computed(
  () =>
    allBooks.value.filter((b) => (b.file_format || "").toLowerCase() === "epub")
      .length,
);

const filteredBooks = computed(() => {
  return allBooks.value.filter((book) => {
    const bookFmt = (book.file_format || "").toLowerCase();
    if (selectedFormat.value === "pdf" && bookFmt !== "pdf") return false;
    if (selectedFormat.value === "epub" && bookFmt !== "epub") return false;
    if (selectedCategory.value && book.category !== selectedCategory.value)
      return false;
    return true;
  });
});

const openDetail = (book) => {
  selectedDetailBook.value = book;
};

// Handle Click "Baca Ebook"
const handleReadBook = (book) => {
  selectedDetailBook.value = null;

  if (!authStore.isAuthenticated()) {
    toastStore.warning(
      "Silakan masuk (login) terlebih dahulu untuk membaca ebook.",
    );
    return router.push({
      path: "/login",
      query: { redirect: `/read/${book.id}` },
    });
  }

  router.push(`/read/${book.id}`);
};

const fetchReadingList = async () => {
  if (!authStore.isAuthenticated()) return;
  try {
    const res = await api.get("/reading-list");
    savedBookIds.value = new Set(
      (res.data.data || []).map((entry) => entry.book_id),
    );
  } catch (error) {
    console.error("Gagal memuat Reading List:", error);
  }
};

const toggleReadingList = async (book) => {
  const isSaved = savedBookIds.value.has(book.id);
  try {
    if (isSaved) {
      await api.delete(`/reading-list/${book.id}`);
      toastStore.success("Ebook dihapus dari Reading List.");
    } else {
      await api.post(`/reading-list/${book.id}`);
      toastStore.success("Ebook disimpan ke Reading List.");
    }
    const next = new Set(savedBookIds.value);
    isSaved ? next.delete(book.id) : next.add(book.id);
    savedBookIds.value = next;
  } catch (error) {
    toastStore.error("Gagal memperbarui Reading List.");
  }
};

onMounted(() => {
  fetchBooks();
  fetchReadingList();
});

onBeforeUnmount(() => clearTimeout(debounceTimer));
</script>
