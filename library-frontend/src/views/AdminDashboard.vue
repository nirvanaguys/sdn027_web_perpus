<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header Section -->
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-8 gap-4"
    >
      <div>
        <div
          class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold mb-2"
        >
          <ShieldCheck class="w-3.5 h-3.5" />
          <span>Panel Kontrol Pustakawan</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
          Manajemen Koleksi Ebook
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Kelola metadata katalog digital, unggah dan ganti berkas ebook (PDF &
          EPUB).
        </p>
      </div>

      <button
        @click="openModal('add')"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white text-sm font-semibold rounded-2xl shadow-[0_16px_35px_-16px_rgba(36,137,0,0.7)] transition duration-200 cursor-pointer self-start sm:self-auto"
      >
        <Plus class="w-5 h-5" />
        <span>Tambah Ebook Baru</span>
      </button>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Total Koleksi</span>
          <div
            class="w-9 h-9 rounded-xl bg-[#eef9e6] text-[#1c6d00] flex items-center justify-center"
          >
            <BookOpen class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-800 mt-2">
          {{ pagination.total }}
        </div>
        <div class="text-xs text-slate-400 mt-1">Judul terdaftar</div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Format PDF</span>
          <div
            class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center"
          >
            <FileText class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-rose-600 mt-2">
          {{ counts.pdf }}
        </div>
        <div class="text-xs text-slate-400 mt-1">Dokumen PDF</div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Format EPUB</span>
          <div
            class="w-9 h-9 rounded-xl bg-[#eef9e6] text-[#1c6d00] flex items-center justify-center"
          >
            <Bookmark class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-[#1c6d00] mt-2">
          {{ counts.epub }}
        </div>
        <div class="text-xs text-slate-400 mt-1">Ebook interaktif</div>
      </div>

      <div
        class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400"
            >Berkas Terunggah</span
          >
          <div
            class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"
          >
            <CheckCircle class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-emerald-600 mt-2">
          {{ counts.ready }}
        </div>
        <div class="text-xs text-slate-400 mt-1">Siap dibaca anggota</div>
      </div>
    </div>

    <!-- Table Container with Search & Filters -->
    <div
      class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]"
    >
      <div
        class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3"
      >
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
          <input
            v-model="tableSearch"
            @input="handleSearchInput"
            type="text"
            placeholder="Cari judul, penulis, kategori, atau ISBN..."
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
          />
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
          <span class="text-slate-400">Filter Format:</span>
          <select
            v-model="formatFilter"
            @change="fetchBooks(1)"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="all">Semua Format</option>
            <option value="pdf">PDF Saja</option>
            <option value="epub">EPUB Saja</option>
          </select>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
          <span class="text-slate-400">Filter Kelas:</span>
          <select
            v-model="gradeFilter"
            @change="fetchBooks(1)"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="">Semua Kelas</option>
            <option v-for="g in gradeLevels" :key="g.value" :value="g.value">
              {{ g.label }}
            </option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table
          class="min-w-full divide-y divide-slate-100 text-left text-xs sm:text-sm"
        >
          <thead
            class="bg-slate-50/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold"
          >
            <tr>
              <th class="px-6 py-4">Sampul</th>
              <th class="px-6 py-4">Judul Ebook & Penulis</th>
              <th class="px-6 py-4">Kategori</th>
              <th class="px-6 py-4">Kelas</th>
              <th class="px-6 py-4">Penerbit & ISBN</th>
              <th class="px-6 py-4 text-center">Format</th>
              <th class="px-6 py-4 text-center">Ukuran File</th>
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
                <img
                  v-if="book.cover_url"
                  :src="assetUrl(book.cover_url)"
                  :alt="`Sampul ${book.title}`"
                  loading="lazy"
                  class="h-16 w-12 rounded-lg object-cover ring-1 ring-slate-200"
                />
                <div
                  v-else
                  class="flex h-16 w-12 items-end rounded-lg bg-gradient-to-br from-[#14532d] to-[#0e2f14] p-1.5 text-[10px] font-bold leading-tight text-white"
                >
                  <span class="line-clamp-4">{{ book.title }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="font-bold text-slate-900 leading-snug">
                  {{ book.title }}
                </div>
                <div class="text-xs text-slate-500 mt-0.5">
                  Penulis: {{ book.author }}
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"
                >
                  {{ book.category || "Umum" }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#eef9e6] text-[#1c6d00]"
                >
                  {{ gradeLabel(book.grade_level) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-slate-700 font-medium">
                  {{ book.publisher }}
                </div>
                <div class="font-mono text-[11px] text-slate-400 mt-0.5">
                  {{ book.isbn }}
                </div>
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider"
                  :class="
                    (book.file_format || '').toLowerCase() === 'pdf'
                      ? 'bg-rose-50 text-rose-700 border border-rose-200/80'
                      : 'bg-indigo-50 text-indigo-700 border border-indigo-200/80'
                  "
                >
                  {{ (book.file_format || "PDF").toUpperCase() }}
                </span>
              </td>
              <td
                class="px-6 py-4 text-center font-mono text-xs text-slate-500"
              >
                {{ book.file_size_formatted || "-" }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <router-link
                    :to="`/read/${book.id}`"
                    title="Pratinjau / Baca Ebook"
                    class="p-2 rounded-xl text-emerald-600 hover:bg-emerald-50 transition cursor-pointer"
                  >
                    <BookOpen class="w-4 h-4" />
                  </router-link>
                  <button
                    @click="openModal('edit', book)"
                    title="Edit Metadata & Berkas"
                    class="p-2 rounded-xl text-[#1c6d00] hover:bg-[#eef9e6] transition cursor-pointer"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    @click="askDeleteBook(book)"
                    title="Hapus Ebook"
                    class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredTableBooks.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <BookOpen class="w-8 h-8 mx-auto mb-2 opacity-50" />
                <span>Tidak ada data ebook yang sesuai.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SD-08: Kontrol Navigasi Pagination Tabel Buku Admin -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} ({{ pagination.total }} koleksi)</span>
        <div class="flex gap-2">
          <button
            @click="fetchBooks(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-1.5 rounded-lg border border-slate-200 font-semibold hover:bg-slate-50 disabled:opacity-50 cursor-pointer"
          >
            Sebelumnya
          </button>
          <button
            @click="fetchBooks(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-1.5 rounded-lg border border-slate-200 font-semibold hover:bg-slate-50 disabled:opacity-50 cursor-pointer"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Ebook) -->
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
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm overflow-y-auto"
        @click.self="isModalOpen = false"
      >
        <div
          ref="bookModalEl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="modal-book-title"
          class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 sm:p-7 border border-slate-100 relative my-8"
        >
          <div
            class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5"
          >
            <div>
              <h3 id="modal-book-title" class="text-xl font-bold text-slate-900">
                {{
                  modalMode === "add"
                    ? "Tambah Ebook Baru"
                    : "Perbarui Data & Berkas Ebook"
                }}
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">
                Kelola metadata dan berkas digital untuk pembaca web.
              </p>
            </div>
            <button
              @click="isModalOpen = false"
              aria-label="Tutup dialog modal"
              class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="saveBook" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                >Judul Ebook</label
              >
              <input
                ref="bookFirstInput"
                v-model="form.title"
                required
                type="text"
                placeholder="Contoh: Belajar Vue 3 & Vite"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
              <p v-if="formErrors.title" class="text-xs text-rose-600 mt-1">{{ formErrors.title[0] }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                  >Penulis</label
                >
                <input
                  v-model="form.author"
                  required
                  type="text"
                  placeholder="Nama Penulis"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
                <p v-if="formErrors.author" class="text-xs text-rose-600 mt-1">{{ formErrors.author[0] }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                  >Penerbit</label
                >
                <input
                  v-model="form.publisher"
                  required
                  type="text"
                  placeholder="Nama Penerbit"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
                <p v-if="formErrors.publisher" class="text-xs text-rose-600 mt-1">{{ formErrors.publisher[0] }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                  >Kode ISBN</label
                >
                <input
                  v-model="form.isbn"
                  required
                  type="text"
                  placeholder="978-xxx-xxx"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
                <p v-if="formErrors.isbn" class="text-xs text-rose-600 mt-1">{{ formErrors.isbn[0] }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                  >Kategori</label
                >
                <input
                  v-model="form.category"
                  type="text"
                  placeholder="Contoh: Teknologi, Fiksi, Sains"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
                />
                <p v-if="formErrors.category" class="text-xs text-rose-600 mt-1">{{ formErrors.category[0] }}</p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                >Jenjang Kelas</label
              >
              <select
                v-model="form.grade_level"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
              >
                <option v-for="g in gradeLevels" :key="g.value" :value="g.value">
                  {{ g.label }}
                </option>
              </select>
              <p v-if="formErrors.grade_level" class="text-xs text-rose-600 mt-1">{{ formErrors.grade_level[0] }}</p>
              <p class="mt-1 text-xs text-slate-500">
                Pilih kelas agar guru mudah memfilter buku pelajaran per kelas.
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5"
                >Sinopsis / Ringkasan Buku</label
              >
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Tuliskan deskripsi singkat mengenai isi buku ini..."
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              ></textarea>
              <p v-if="formErrors.description" class="text-xs text-rose-600 mt-1">{{ formErrors.description[0] }}</p>
            </div>

            <div class="border-b border-slate-200 pb-4">
              <label class="mb-2 block text-xs font-semibold text-slate-700"
                >Sampul ebook (opsional)</label
              >
              <img
                v-if="currentCoverUrl && !selectedCover"
                :src="assetUrl(currentCoverUrl)"
                alt="Sampul ebook saat ini"
                class="mb-3 h-28 w-20 object-cover"
              />
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="handleCoverChange"
                class="block w-full text-xs text-slate-600"
              />
              <p v-if="formErrors.cover" class="text-xs text-rose-600 mt-1">{{ formErrors.cover[0] }}</p>
              <p class="mt-1 text-xs text-slate-500">
                JPG, PNG, atau WebP, maksimal 5 MB.
              </p>
            </div>

            <!-- Upload File Ebook (PDF / EPUB) -->
            <div
              class="p-4 rounded-2xl bg-[#eef9e6] border border-[#248900]/20"
            >
              <label class="block text-xs font-bold text-[#0e2f14] mb-1">
                {{
                  modalMode === "add"
                    ? "Berkas Ebook (PDF / EPUB)"
                    : "Ganti Berkas Ebook (Opsional)"
                }}
              </label>

              <div
                v-if="modalMode === 'edit' && currentFileInfo"
                class="text-xs text-[#1c6d00] mb-2.5 flex items-center gap-2"
              >
                <CheckCircle class="w-4 h-4 text-emerald-600" />
                <span
                  >Berkas aktif:
                  <strong>{{ currentFileInfo.format?.toUpperCase() }}</strong>
                  ({{ currentFileInfo.size || "Tersedia" }})</span
                >
              </div>

              <input
                type="file"
                accept=".pdf,.epub"
                @change="handleFileChange"
                class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#248900] file:text-white hover:file:bg-[#1c6d00] cursor-pointer"
              />
              <p v-if="formErrors.file" class="text-xs text-rose-600 mt-1">{{ formErrors.file[0] }}</p>
              <p class="text-[11px] text-slate-400 mt-1.5">
                Mendukung format file <strong>.pdf</strong> atau
                <strong>.epub</strong> (maksimal 50MB). Berkas akan disimpan di
                storage privat.
              </p>
            </div>

            <!-- UI-12: Indikator Progres Upload Berkas -->
            <div
              v-if="submitting && selectedFile"
              class="space-y-1.5 bg-slate-50 p-3 rounded-2xl border border-slate-200"
            >
              <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                <span class="flex items-center gap-1.5 truncate max-w-[80%]">
                  <FileText class="w-4 h-4 text-[#1c6d00] shrink-0" />
                  Mengunggah berkas: {{ selectedFile.name }}
                </span>
                <span>{{ uploadProgress }}%</span>
              </div>
              <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                <div
                  class="bg-[#248900] h-full transition-all duration-200 rounded-full"
                  :style="{ width: `${uploadProgress}%` }"
                ></div>
              </div>
            </div>

            <div
              class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100"
            >
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
                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] rounded-xl shadow-md shadow-[#248900]/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-70"
              >
                <div
                  v-if="submitting"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                ></div>
                <span>{{ submitting ? "Menyimpan..." : "Simpan Ebook" }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :isOpen="showDeleteModal"
      title="Hapus Koleksi Ebook"
      :message="`Apakah Anda yakin ingin menghapus ebook '${bookToDelete?.title}'? Berkas ebook yang terkait di storage privat juga akan dihapus permanen.`"
      confirmText="Ya, Hapus Ebook"
      cancelText="Batal"
      :isDanger="true"
      :loading="deleting"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import api, { assetUrl } from "../services/api";
import { toastStore } from "../stores/toast";
import ConfirmModal from "../components/ConfirmModal.vue";
import {
  useCatalog,
  GRADES as gradeLevels,
  gradeLabel,
} from "../composables/useCatalog";
import { useFocusTrap } from "../composables/useFocusTrap";
import {
  ShieldCheck,
  Plus,
  BookOpen,
  CheckCircle,
  Search,
  Edit3,
  Trash2,
  X,
  FileText,
  Bookmark,
} from "lucide-vue-next";

// Halaman buku, ringkasan katalog dan daftar kategori disediakan module katalog.
const { books, pagination, counts, load } = useCatalog(15);
const tableSearch = ref("");
const formatFilter = ref("all");
const gradeFilter = ref("");
const formErrors = ref({});
const uploadProgress = ref(0);
const bookFirstInput = ref(null);

const isModalOpen = ref(false);
const bookModalEl = ref(null);
// UI-04: fokus terkunci di modal selama terbuka.
useFocusTrap(bookModalEl, isModalOpen);
const modalMode = ref("add");
const submitting = ref(false);
const selectedBookId = ref(null);
const currentFileInfo = ref(null);
const selectedFile = ref(null);
const selectedCover = ref(null);
const currentCoverUrl = ref(null);

const showDeleteModal = ref(false);
const bookToDelete = ref(null);
const deleting = ref(false);

const form = ref({
  title: "",
  author: "",
  publisher: "",
  isbn: "",
  category: "Umum",
  grade_level: "umum",
  description: "",
});

let searchTimer = null;

const fetchBooks = async (page = 1) => {
  await load(
    {
      search: tableSearch.value.trim(),
      format: formatFilter.value !== "all" ? formatFilter.value : "",
      grade_level: gradeFilter.value,
    },
    page,
  );
};

const handleSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchBooks(1);
  }, 350);
};

const filteredTableBooks = computed(() => books.value);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
  }
};

const handleCoverChange = (e) => {
  selectedCover.value = e.target.files[0] || null;
};

const openModal = (mode, book = null) => {
  modalMode.value = mode;
  selectedFile.value = null;
  selectedCover.value = null;
  formErrors.value = {};
  uploadProgress.value = 0;

  if (mode === "edit" && book) {
    selectedBookId.value = book.id;
    currentCoverUrl.value = book.cover_url || null;
    currentFileInfo.value = {
      format: book.file_format,
      size: book.file_size_formatted,
    };
    form.value = {
      title: book.title,
      author: book.author,
      publisher: book.publisher,
      isbn: book.isbn,
      category: book.category || "Umum",
      grade_level: book.grade_level || "umum",
      description: book.description || "",
    };
  } else {
    selectedBookId.value = null;
    currentCoverUrl.value = null;
    currentFileInfo.value = null;
    form.value = {
      title: "",
      author: "",
      publisher: "",
      isbn: "",
      category: "Umum",
      grade_level: "umum",
      description: "",
    };
  }
  isModalOpen.value = true;
  nextTick(() => {
    bookFirstInput.value?.focus();
  });
};

const saveBook = async () => {
  submitting.value = true;
  formErrors.value = {};
  uploadProgress.value = 0;

  const formData = new FormData();
  formData.append("title", form.value.title);
  formData.append("author", form.value.author);
  formData.append("publisher", form.value.publisher);
  formData.append("isbn", form.value.isbn);
  formData.append("category", form.value.category || "Umum");
  formData.append("grade_level", form.value.grade_level || "umum");
  if (form.value.description) {
    formData.append("description", form.value.description);
  }

  if (selectedFile.value) {
    formData.append("file", selectedFile.value);
  }
  if (selectedCover.value) {
    formData.append("cover", selectedCover.value);
  }

  const uploadConfig = {
    headers: { "Content-Type": "multipart/form-data" },
    onUploadProgress: (progressEvent) => {
      if (progressEvent.total) {
        uploadProgress.value = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total,
        );
      }
    },
  };

  try {
    if (modalMode.value === "add") {
      await api.post("/admin/books", formData, uploadConfig);
      toastStore.success("Ebook baru berhasil ditambahkan!");
    } else {
      await api.post(`/admin/books/${selectedBookId.value}`, formData, uploadConfig);
      toastStore.success("Data dan berkas ebook berhasil diperbarui!");
    }
    isModalOpen.value = false;
    fetchBooks(pagination.value.current_page);
  } catch (err) {
    if (err.response?.data?.errors) {
      formErrors.value = err.response.data.errors;
    }
    const msg =
      err.response?.data?.message || "Terjadi kesalahan saat menyimpan data.";
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
    toastStore.success("Ebook dan berkas berhasil dihapus.");
    showDeleteModal.value = false;
    fetchBooks(pagination.value.current_page);
  } catch (err) {
    const msg = err.response?.data?.message || "Gagal menghapus ebook.";
    toastStore.error(msg);
  } finally {
    deleting.value = false;
  }
};

const onKeydown = (e) => {
  if (e.key === "Escape" && isModalOpen.value && !submitting.value) {
    isModalOpen.value = false;
  }
};

onMounted(() => {
  fetchBooks();
  window.addEventListener("keydown", onKeydown);
});

onBeforeUnmount(() => {
  clearTimeout(searchTimer);
  window.removeEventListener("keydown", onKeydown);
});
</script>
