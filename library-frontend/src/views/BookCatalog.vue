<template>
  <div class="catalog-page">
    <!-- Hero Section: foto sekolah sd027.jpg tetap sebagai background -->
    <section class="relative overflow-hidden text-white">
      <div class="absolute inset-0" aria-hidden="true">
        <img
          :src="schoolImg"
          alt=""
          class="hero-bg-zoom h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-[#08170c]/92 via-[#0e2f14]/78 to-[#14532d]/55"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#08170c]/70 via-transparent to-transparent"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
        <div class="max-w-3xl space-y-4 sm:space-y-5">
          <!-- Headline -->
          <h1
            class="anim-fade-up-1 text-3xl sm:text-4xl lg:text-[3.25rem] font-extrabold leading-[1.08] tracking-tight text-white drop-shadow-[0_2px_18px_rgba(0,0,0,0.35)]"
          >
            Perpustakaan Digital
            <span class="block">SDN 027 Balikpapan Utara</span>
          </h1>

          <!-- Subtitle -->
          <p
            class="anim-fade-up-2 max-w-xl text-sm sm:text-base leading-relaxed text-emerald-50/90"
          >
            Mari membangun dunia mulai dari membaca buku! Cari koleksi PDF dan
            EPUB, simpan favoritmu, lalu baca langsung tanpa antre.
          </p>

          <!-- Prominent Search Bar -->
          <div class="anim-fade-up-3 w-full max-w-2xl pt-1">
            <div
              class="relative flex items-center gap-1 rounded-2xl border border-white/30 bg-white p-1.5 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)] transition-all focus-within:border-emerald-200 focus-within:ring-4 focus-within:ring-emerald-300/30"
            >
              <div class="pl-3 text-[#1c6d00]">
                <Search class="w-5 h-5" />
              </div>
              <input
                v-model="searchQuery"
                @input="handleSearch"
                type="search"
                placeholder="Cari judul, penulis, penerbit, atau ISBN…"
                aria-label="Cari ebook"
                class="w-full bg-transparent px-2.5 py-2.5 text-sm text-slate-900 placeholder-slate-500 outline-none"
              />
              <button
                v-if="searchQuery"
                @click="clearSearch"
                aria-label="Bersihkan pencarian"
                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
              >
                <X class="w-4 h-4" />
              </button>
              <span
                class="mr-1 hidden shrink-0 items-center gap-1.5 rounded-xl bg-[#0e2f14] px-3.5 py-2.5 text-xs font-bold text-white sm:inline-flex"
              >
                <BookOpen class="w-4 h-4" />
                {{ pagination.total }} Ebook
              </span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-emerald-50/85">
              <span class="uppercase tracking-widest text-emerald-100/70">Populer:</span>
              <button type="button" @click="searchQuery=''; handleSearch()" class="rounded-full border border-white/25 bg-white/10 px-3 py-1 backdrop-blur transition hover:bg-white/20 cursor-pointer">Semua</button>
              <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1">PDF: {{ counts.pdf }}</span>
              <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1">EPUB: {{ counts.epub }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stat strip -->
      <div class="relative border-t border-white/15 bg-black/25 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 grid grid-cols-3 gap-3 text-center sm:text-left">
          <div class="flex items-center justify-center gap-2.5 sm:justify-start">
            <Library class="h-4 w-4 text-emerald-200" />
            <p class="text-xs sm:text-sm text-emerald-50"><strong class="font-extrabold text-white">{{ pagination.total }}</strong> koleksi terdaftar</p>
          </div>
          <div class="flex items-center justify-center gap-2.5">
            <Zap class="h-4 w-4 text-emerald-200" />
            <p class="text-xs sm:text-sm text-emerald-50">Baca <strong class="font-extrabold text-white">langsung online</strong></p>
          </div>
          <div class="flex items-center justify-center gap-2.5 sm:justify-end">
            <ShieldCheck class="h-4 w-4 text-emerald-200" />
            <p class="text-xs sm:text-sm text-emerald-50">Aman untuk <strong class="font-extrabold text-white">siswa & guru</strong></p>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content: Catalog Grid & Filters -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8 pb-16 relative z-10">
      <!-- Filter Bar -->
      <div
        class="rounded-2xl border border-slate-200/80 bg-white/95 p-3 sm:p-4 shadow-[0_20px_45px_-30px_rgba(12,36,18,0.4)] backdrop-blur flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
      >
        <!-- Format Filter Tabs -->
        <div
          class="flex items-center gap-1.5 overflow-x-auto rounded-xl bg-slate-100/80 p-1.5"
          role="tablist"
          aria-label="Filter format ebook"
        >
          <button
            v-for="filter in formatFilterOptions"
            :key="filter.value"
            @click="setFormat(filter.value)"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold whitespace-nowrap transition cursor-pointer"
            :class="
              selectedFormat === filter.value
                ? 'bg-white text-[#1c6d00] shadow-sm ring-1 ring-[#248900]/25'
                : 'text-slate-600 hover:text-slate-900 hover:bg-white/70'
            "
          >
            {{ filter.label }}
          </button>
        </div>

        <!-- Dropdown Filter: Category -->
        <div class="flex items-center gap-2">
          <span class="hidden sm:inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#eef9e6] text-[#1c6d00]">
            <Filter class="w-4 h-4 shrink-0" />
          </span>
          <label class="sr-only" for="kategori">Filter kategori</label>
          <select
            id="kategori"
            v-model="selectedCategory"
            @change="fetchBooks(1)"
            class="w-full lg:w-auto px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:ring-2 focus:ring-[#248900]/40 outline-none cursor-pointer"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
          <label class="sr-only" for="kelas">Filter jenjang kelas</label>
          <select
            id="kelas"
            v-model="selectedGrade"
            @change="fetchBooks(1)"
            class="w-full lg:w-auto px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:ring-2 focus:ring-[#248900]/40 outline-none cursor-pointer"
          >
            <option value="">Semua Kelas</option>
            <option v-for="g in gradeLevels" :key="g.value" :value="g.value">
              {{ g.label }}
            </option>
          </select>
          <p class="hidden xl:block text-xs text-slate-400 whitespace-nowrap">
            Menampilkan <strong class="text-slate-700">{{ pagination.total }}</strong> Ebook
          </p>
        </div>
      </div>

      <div class="pt-7"></div>

      <!-- Loading Skeleton Cards -->
      <div
        v-if="loading"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5"
      >
        <div
          v-for="n in 8"
          :key="n"
          class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm animate-pulse"
        >
          <div class="aspect-[3/4] bg-slate-200"></div>
          <div class="space-y-3 p-5">
            <div class="h-4 bg-slate-200 rounded w-3/4"></div>
            <div class="h-3 bg-slate-100 rounded w-1/2"></div>
            <div class="h-9 bg-slate-200 rounded-xl mt-2"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredBooks.length === 0"
        class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 sm:p-12 text-center max-w-lg mx-auto shadow-sm"
      >
        <div
          class="w-16 h-16 rounded-2xl bg-[#eef9e6] text-[#1c6d00] flex items-center justify-center mx-auto mb-4"
        >
          <BookOpen class="w-8 h-8 opacity-80" />
        </div>
        <h3 class="text-lg font-bold text-slate-900">Ebook tidak ditemukan</h3>
        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
          Tidak ada koleksi ebook yang cocok dengan filter atau kata kunci "{{
            searchQuery
          }}".
        </p>
        <button
          @click="resetFilters"
          class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-[#eef9e6] text-[#1c6d00] hover:bg-[#dcefd0] transition cursor-pointer"
        >
          <RotateCcw class="w-4 h-4" />
          <span>Reset Pencarian</span>
        </button>
      </div>

      <!-- Books Grid -->
      <div
        v-else
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5"
      >
        <article
          v-for="book in filteredBooks"
          :key="book.id"
          class="lift group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_18px_40px_-30px_rgba(15,23,42,0.45)] hover:border-[#248900]/35 hover:shadow-[0_28px_55px_-28px_rgba(36,137,0,0.5)]"
        >
          <!-- Cover -->
          <div
            class="relative aspect-[3/4] overflow-hidden bg-[#0e2f14] text-white"
          >
            <img
              v-if="book.cover_url && !brokenCovers.has(book.id)"
              :src="assetUrl(book.cover_url)"
              :alt="`Sampul ${book.title}`"
              loading="lazy"
              @error="onCoverError(book)"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
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
            <!-- Soft overlay -->
            <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/45 to-transparent"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/45 to-transparent"></div>

            <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
              <!-- Format Badge (PDF / EPUB / Katalog) -->
              <span
                v-if="book.has_ebook && book.file_format"
                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider shadow backdrop-blur-md"
                :class="
                  (book.file_format || '').toLowerCase() === 'pdf'
                    ? 'bg-rose-500/90 text-white'
                    : 'bg-[#0e2f14]/85 text-emerald-50 ring-1 ring-white/25'
                "
              >
                <FileText class="w-3.5 h-3.5" />
                <span>{{ (book.file_format || '').toUpperCase() }}</span>
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider shadow backdrop-blur-md bg-slate-800/80 text-slate-200 ring-1 ring-white/20"
              >
                <Library class="w-3.5 h-3.5" />
                <span>Katalog</span>
              </span>

              <!-- Digital Read Ready Badge (UI-03) -->
              <span
                v-if="book.has_ebook"
                class="inline-flex items-center gap-1 rounded-full bg-emerald-500/90 px-2 py-1 text-[11px] font-semibold text-white backdrop-blur-md"
              >
                <CheckCircle2 class="w-3 h-3" />
                <span>Siap Baca</span>
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 rounded-full bg-amber-600/90 px-2 py-1 text-[11px] font-semibold text-white backdrop-blur-md"
              >
                <AlertCircle class="w-3 h-3" />
                <span>Belum Tersedia</span>
              </span>
            </div>

            <!-- ISBN + size -->
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between p-3">
              <span
                class="rounded-md bg-black/45 px-2 py-1 font-mono text-[11px] text-white/90 backdrop-blur"
              >
                ISBN: {{ book.isbn }}
              </span>
              <span
                v-if="book.file_size_formatted"
                class="rounded-md bg-black/45 px-2 py-1 text-[11px] text-white/90 backdrop-blur"
              >
                {{ book.file_size_formatted }}
              </span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-5 flex-1 flex flex-col">
            <div class="flex items-center gap-1.5 mb-2.5">
              <span
                class="inline-flex items-center rounded-lg bg-[#eef9e6] px-2.5 py-1 text-[11px] font-bold text-[#1c6d00]"
              >
                {{ book.category || "Umum" }}
              </span>
              <span
                class="inline-flex items-center rounded-lg bg-[#0e2f14] px-2.5 py-1 text-[11px] font-bold text-emerald-50"
              >
                {{ gradeLabel(book.grade_level) }}
              </span>
            </div>

            <h3
              class="font-bold text-[15px] text-slate-900 group-hover:text-[#1c6d00] transition-colors line-clamp-2 leading-snug cursor-pointer"
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
                class="flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 cursor-pointer"
              >
                <Eye class="w-3.5 h-3.5" />
                <span>Detail</span>
              </button>

              <button
                v-if="authStore.isAuthenticated() && book.has_ebook"
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
                class="rounded-xl border p-2.5 transition cursor-pointer"
                :class="savedBookIds.has(book.id) ? 'border-[#248900]/30 bg-[#eef9e6] text-[#1c6d00]' : 'border-slate-200 text-slate-500 hover:border-[#248900]/30 hover:text-[#1c6d00] hover:bg-[#eef9e6]'"
              >
                <Bookmark
                  class="h-4 w-4"
                  :fill="savedBookIds.has(book.id) ? 'currentColor' : 'none'"
                />
              </button>
              <button
                v-if="book.has_ebook"
                @click="handleReadBook(book)"
                class="flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#248900] to-[#14532d] px-3 py-2.5 text-xs font-semibold text-white shadow-[0_12px_25px_-12px_rgba(36,137,0,0.7)] transition hover:brightness-[1.07] cursor-pointer"
              >
                <BookOpen class="w-3.5 h-3.5" />
                <span>Baca Ebook</span>
              </button>
              <button
                v-else
                disabled
                title="Berkas digital belum diunggah untuk buku ini"
                class="flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2.5 text-xs font-semibold text-slate-400 cursor-not-allowed border border-slate-200"
                :class="authStore.isAuthenticated() ? 'col-span-2' : ''"
              >
                <AlertCircle class="w-3.5 h-3.5" />
                <span>Berkas Belum Ada</span>
              </button>
            </div>
          </div>
        </article>
      </div>

      <!-- UI-01: Pagination Navigasi Katalog Ebook -->
      <div v-if="pagination.last_page > 1" class="mt-12 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl bg-white p-4 shadow-sm border border-slate-200/80">
        <p class="text-xs sm:text-sm text-slate-500">
          Menampilkan halaman <strong class="text-slate-800">{{ pagination.current_page }}</strong> dari <strong class="text-slate-800">{{ pagination.last_page }}</strong> (total {{ pagination.total }} ebook)
        </p>
        <div class="flex items-center gap-2">
          <button
            @click="fetchBooks(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-4 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1.5"
          >
            <ChevronLeft class="w-4 h-4" />
            <span>Sebelumnya</span>
          </button>
          <button
            @click="fetchBooks(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-4 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1.5"
          >
            <span>Berikutnya</span>
            <ChevronRight class="w-4 h-4" />
          </button>
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
          role="dialog"
          aria-modal="true"
          aria-labelledby="detail-book-title"
          class="max-h-[90vh] overflow-y-auto bg-white rounded-3xl shadow-2xl max-w-lg w-full border border-slate-100"
        >
          <div
            class="flex gap-4 p-6 text-white relative overflow-hidden"
            :class="selectedDetailBook.cover_url ? 'bg-[#0e2f14]' : getCardGradient(selectedDetailBook.id)"
          >
            <img
              v-if="selectedDetailBook.cover_url"
              :src="assetUrl(selectedDetailBook.cover_url)"
              :alt="`Sampul ${selectedDetailBook.title}`"
              class="h-44 w-32 sm:h-52 sm:w-36 shrink-0 rounded-xl object-cover shadow-lg ring-1 ring-white/30"
            />
            <div class="min-w-0 flex-1">
            <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
            <button
              @click="selectedDetailBook = null"
              aria-label="Tutup detail buku"
              class="absolute top-4 right-4 p-1.5 rounded-full bg-black/20 hover:bg-black/30 text-white transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
            <span
              v-if="selectedDetailBook.has_ebook && selectedDetailBook.file_format"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-black/30 mb-3"
            >
              <FileText class="w-3.5 h-3.5" />
              <span>Format: {{ selectedDetailBook.file_format.toUpperCase() }}</span>
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-black/30 mb-3"
            >
              <Library class="w-3.5 h-3.5" />
              <span>Format: Belum Ada Berkas</span>
            </span>
            <h2 id="detail-book-title" class="text-xl font-bold leading-snug">
              {{ selectedDetailBook.title }}
            </h2>
            <p class="text-sm text-white/80 mt-1">
              Karya {{ selectedDetailBook.author }}
            </p>
            </div>
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
                <span class="text-slate-400 block mb-0.5">Jenjang Kelas</span>
                <strong class="text-slate-800 text-sm font-semibold">{{
                  gradeLabel(selectedDetailBook.grade_level)
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
              class="text-xs text-slate-600 leading-relaxed bg-[#eef9e6] p-3.5 rounded-xl border border-[#248900]/20"
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
                v-if="selectedDetailBook.has_ebook"
                type="button"
                @click="handleReadBook(selectedDetailBook)"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] transition flex items-center gap-2 cursor-pointer shadow-md shadow-[#248900]/25"
              >
                <BookOpen class="w-4 h-4" />
                <span>Baca Ebook Sekarang</span>
              </button>
              <button
                v-else
                disabled
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed flex items-center gap-2"
              >
                <AlertCircle class="w-4 h-4" />
                <span>Berkas Ebook Belum Tersedia</span>
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
  Search,
  X,
  BookOpen,
  User,
  Bookmark,
  Filter,
  Eye,
  Library,
  RotateCcw,
  ShieldCheck,
  FileText,
  CheckCircle2,
  AlertCircle,
  Zap,
  ChevronLeft,
  ChevronRight,
} from "lucide-vue-next";
import schoolImg from "../assets/library-frontend/sd027.jpg";
import {
  useCatalog,
  GRADES as gradeLevels,
  gradeLabel,
} from "../composables/useCatalog";

const router = useRouter();
// Halaman buku, ringkasan katalog dan daftar kategori disediakan module katalog.
const {
  books: allBooks,
  pagination,
  counts,
  categories: uniqueCategories,
  loading,
  load,
} = useCatalog(12);
const searchQuery = ref("");
const selectedFormat = ref("all");
const selectedCategory = ref("");
const selectedGrade = ref("");

const selectedDetailBook = ref(null);
const savedBookIds = ref(new Set());
// ID buku yang gambar sampulnya gagal dimuat -> tampilkan fallback gradien.
const brokenCovers = ref(new Set());

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

const fetchBooks = async (page = 1) => {
  await load(
    {
      search: searchQuery.value.trim(),
      format: selectedFormat.value !== "all" ? selectedFormat.value : "",
      category: selectedCategory.value,
      grade_level: selectedGrade.value,
    },
    page,
  );
};

const handleSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchBooks(1);
  }, 350);
};

const clearSearch = () => {
  searchQuery.value = "";
  fetchBooks(1);
};

const setFormat = (fmt) => {
  selectedFormat.value = fmt;
  fetchBooks(1);
};

const resetFilters = () => {
  searchQuery.value = "";
  selectedFormat.value = "all";
  selectedCategory.value = "";
  selectedGrade.value = "";
  fetchBooks(1);
};

// Server-side filtering diutamakan (UI-02)
const filteredBooks = computed(() => allBooks.value);

const openDetail = (book) => {
  selectedDetailBook.value = book;
};

const onCoverError = (book) => {
  const next = new Set(brokenCovers.value);
  next.add(book.id);
  brokenCovers.value = next;
};

// Handle Click "Baca Ebook"
const handleReadBook = (book) => {
  if (!book.has_ebook) {
    toastStore.warning("Berkas ebook belum tersedia untuk buku ini.");
    return;
  }

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
  if (!book.has_ebook) {
    toastStore.warning("Buku tanpa berkas ebook tidak dapat disimpan ke Reading List.");
    return;
  }

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

    // UI-10: Beritahu tab lain via localStorage
    localStorage.setItem("reading-list-updated", Date.now().toString());
  } catch (error) {
    toastStore.error("Gagal memperbarui Reading List.");
  }
};

const handleStorage = (e) => {
  if (e.key === "reading-list-updated") {
    fetchReadingList();
  }
};

const handleVisibility = () => {
  if (document.visibilityState === "visible") {
    fetchReadingList();
  }
};

const onKeydown = (e) => {
  if (e.key === "Escape" && selectedDetailBook.value) {
    selectedDetailBook.value = null;
  }
};

onMounted(() => {
  fetchBooks();
  fetchReadingList();
  window.addEventListener("storage", handleStorage);
  window.addEventListener("visibilitychange", handleVisibility);
  window.addEventListener("keydown", onKeydown);
});

onBeforeUnmount(() => {
  clearTimeout(debounceTimer);
  window.removeEventListener("storage", handleStorage);
  window.removeEventListener("visibilitychange", handleVisibility);
  window.removeEventListener("keydown", onKeydown);
});
</script>
