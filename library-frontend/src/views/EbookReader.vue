<template>
  <div
    ref="readerContainer"
    class="min-h-screen bg-slate-900 text-slate-100 flex flex-col select-none overflow-hidden"
  >
    <!-- Top Bar Navigation & Controls -->
    <header class="bg-slate-950/90 backdrop-blur-md border-b border-slate-800 px-4 py-3 z-30 shrink-0">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
        <!-- Left: Back Button & Book Info -->
        <div class="flex items-center gap-3 min-w-0">
          <button
            @click="goBack"
            title="Kembali ke Katalog"
            class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer shrink-0"
          >
            <ArrowLeft class="w-5 h-5" />
          </button>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span
                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider shrink-0"
                :class="
                  format === 'pdf'
                    ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                    : 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30'
                "
              >
                {{ format.toUpperCase() }}
              </span>
              <h1 class="text-sm sm:text-base font-bold text-white truncate max-w-xs sm:max-w-md md:max-w-lg">
                {{ book?.title || 'Memuat Ebook...' }}
              </h1>
            </div>
            <p v-if="book?.author" class="text-xs text-slate-400 truncate hidden sm:block">
              {{ book.author }}
            </p>
          </div>
        </div>

        <!-- Right: Reading Controls -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
          <!-- PDF Controls: Zoom -->
          <template v-if="format === 'pdf' && !loading && !error">
            <button
              @click="zoomOut"
              :disabled="zoomScale <= 0.6"
              title="Perkecil (Zoom Out)"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition disabled:opacity-40 cursor-pointer"
            >
              <ZoomOut class="w-4 h-4" />
            </button>
            <span class="text-xs font-mono text-slate-400 hidden sm:inline-block w-12 text-center">
              {{ Math.round(zoomScale * 100) }}%
            </span>
            <button
              @click="zoomIn"
              :disabled="zoomScale >= 2.5"
              title="Perbesar (Zoom In)"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition disabled:opacity-40 cursor-pointer"
            >
              <ZoomIn class="w-4 h-4" />
            </button>
            <button
              @click="resetZoom"
              title="Sesuaikan Lebar Halaman"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition hidden md:block cursor-pointer text-xs font-medium"
            >
              <Maximize2 class="w-4 h-4" />
            </button>
          </template>

          <!-- EPUB Controls: Font Size & Table of Contents -->
          <template v-if="format === 'epub' && !loading && !error">
            <button
              @click="decreaseFontSize"
              title="Perkecil Ukuran Teks"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
            >
              <span class="text-xs font-bold">A-</span>
            </button>
            <span class="text-xs font-mono text-slate-400 hidden sm:inline-block w-12 text-center">
              {{ epubFontSize }}%
            </span>
            <button
              @click="increaseFontSize"
              title="Perbesar Ukuran Teks"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
            >
              <span class="text-xs font-bold">A+</span>
            </button>
            <button
              v-if="toc.length > 0"
              @click="showToc = !showToc"
              title="Daftar Isi"
              class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer relative"
              :class="showToc ? 'bg-indigo-600 text-white' : ''"
            >
              <List class="w-4 h-4" />
            </button>
          </template>

          <!-- Fullscreen Toggle -->
          <button
            @click="toggleFullscreen"
            title="Layar Penuh (Fullscreen)"
            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
          >
            <Minimize v-if="isFullscreen" class="w-4 h-4" />
            <Maximize v-else class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <!-- Main Viewport -->
    <div class="flex-1 relative overflow-hidden flex flex-col justify-center items-center">
      <!-- Loading State -->
      <div v-if="loading" class="text-center space-y-4 p-8">
        <div class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin mx-auto"></div>
        <p class="text-sm font-medium text-slate-300">{{ loadingMessage }}</p>
        <p class="text-xs text-slate-500">Mempersiapkan dokumen ebook secara aman...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center space-y-4 max-w-md p-6 bg-slate-800/80 rounded-3xl border border-slate-700">
        <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto">
          <AlertCircle class="w-6 h-6" />
        </div>
        <h3 class="text-lg font-bold text-white">Gagal Membuka Ebook</h3>
        <p class="text-xs sm:text-sm text-slate-400">{{ error }}</p>
        <div class="flex items-center justify-center gap-3 pt-2">
          <button
            @click="fetchBookAndLoad"
            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition cursor-pointer"
          >
            Coba Lagi
          </button>
          <button
            @click="goBack"
            class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold transition cursor-pointer"
          >
            Kembali ke Katalog
          </button>
        </div>
      </div>

      <!-- PDF Canvas Viewport -->
      <div
        v-show="format === 'pdf' && !loading && !error"
        ref="pdfScrollContainer"
        class="w-full h-full overflow-auto flex justify-center items-start p-4 sm:p-6"
      >
        <div class="relative shadow-2xl rounded-lg overflow-hidden bg-white">
          <canvas ref="pdfCanvas" class="block max-w-full h-auto"></canvas>
        </div>
      </div>

      <!-- EPUB Viewport -->
      <div
        v-show="format === 'epub' && !loading && !error"
        class="w-full h-full relative flex items-center justify-center"
      >
        <div
          ref="epubViewerRef"
          class="w-full h-full max-w-4xl mx-auto px-4 sm:px-8 py-4 overflow-hidden"
        ></div>

        <!-- Left Navigation Arrow (EPUB) -->
        <button
          @click="prevEpubPage"
          class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 p-3 rounded-2xl bg-slate-800/80 hover:bg-indigo-600 text-white shadow-xl backdrop-blur-md transition cursor-pointer opacity-80 hover:opacity-100 z-20"
          title="Halaman Sebelumnya"
        >
          <ChevronLeft class="w-5 h-5 sm:w-6 sm:h-6" />
        </button>

        <!-- Right Navigation Arrow (EPUB) -->
        <button
          @click="nextEpubPage"
          class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 p-3 rounded-2xl bg-slate-800/80 hover:bg-indigo-600 text-white shadow-xl backdrop-blur-md transition cursor-pointer opacity-80 hover:opacity-100 z-20"
          title="Halaman Selanjutnya"
        >
          <ChevronRight class="w-5 h-5 sm:w-6 sm:h-6" />
        </button>

        <!-- EPUB Table of Contents Drawer -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="-translate-x-full opacity-0"
          enter-to-class="translate-x-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-x-0 opacity-100"
          leave-to-class="-translate-x-full opacity-0"
        >
          <div
            v-if="showToc"
            class="absolute top-0 left-0 bottom-0 w-72 sm:w-80 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800 p-5 z-40 overflow-y-auto flex flex-col"
          >
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <List class="w-4 h-4 text-indigo-400" />
                <span>Daftar Isi Ebook</span>
              </h3>
              <button
                @click="showToc = false"
                class="p-1 rounded-lg text-slate-400 hover:text-white cursor-pointer"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
            <ul class="space-y-1.5 flex-1 overflow-y-auto text-xs">
              <li
                v-for="(item, idx) in toc"
                :key="idx"
              >
                <button
                  @click="goToChapter(item.href)"
                  class="w-full text-left px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition truncate cursor-pointer"
                >
                  {{ item.label }}
                </button>
              </li>
            </ul>
          </div>
        </transition>
      </div>
    </div>

    <!-- Bottom Navigation Bar for PDF -->
    <footer
      v-if="format === 'pdf' && !loading && !error"
      class="bg-slate-950/90 backdrop-blur-md border-t border-slate-800 px-4 py-2.5 z-30 shrink-0"
    >
      <div class="max-w-md mx-auto flex items-center justify-between gap-3">
        <button
          @click="prevPdfPage"
          :disabled="pdfCurrentPage <= 1"
          class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition disabled:opacity-40 flex items-center gap-1.5 cursor-pointer"
        >
          <ChevronLeft class="w-4 h-4" />
          <span>Sebelumnya</span>
        </button>

        <!-- Page Indicator & Quick Jump -->
        <div class="flex items-center gap-1.5 text-xs text-slate-400">
          <span>Hal</span>
          <input
            type="number"
            v-model.number="inputPage"
            @change="jumpToPdfPage"
            :min="1"
            :max="pdfTotalPages"
            class="w-12 px-1.5 py-1 rounded bg-slate-800 border border-slate-700 text-center text-white text-xs font-mono outline-none focus:ring-1 focus:ring-indigo-500"
          />
          <span>/ {{ pdfTotalPages }}</span>
        </div>

        <button
          @click="nextPdfPage"
          :disabled="pdfCurrentPage >= pdfTotalPages"
          class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition disabled:opacity-40 flex items-center gap-1.5 cursor-pointer"
        >
          <span>Berikutnya</span>
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import { toastStore } from '../stores/toast';
import {
  ArrowLeft,
  ChevronLeft,
  ChevronRight,
  ZoomIn,
  ZoomOut,
  Maximize2,
  Maximize,
  Minimize,
  List,
  X,
  AlertCircle
} from 'lucide-vue-next';

// Dynamic import or direct import for pdfjs and epub
import * as pdfjsLib from 'pdfjs-dist';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import ePub from 'epubjs';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

const route = useRoute();
const router = useRouter();

const bookId = route.params.id;
const book = ref(null);
const format = ref('pdf');
const loading = ref(true);
const loadingMessage = ref('Menghubungkan ke server...');
const error = ref(null);
const isFullscreen = ref(false);

const readerContainer = ref(null);
let objectUrl = null;

// PDF State
const pdfCanvas = ref(null);
const pdfScrollContainer = ref(null);
let pdfDoc = null;
const pdfCurrentPage = ref(1);
const pdfTotalPages = ref(1);
const inputPage = ref(1);
const zoomScale = ref(1.2);
let isRendering = false;
let pageRenderPending = null;

// EPUB State
const epubViewerRef = ref(null);
let epubBook = null;
let epubRendition = null;
const epubFontSize = ref(100);
const showToc = ref(false);
const toc = ref([]);

const goBack = () => {
  router.push('/');
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    if (readerContainer.value?.requestFullscreen) {
      readerContainer.value.requestFullscreen();
      isFullscreen.value = true;
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
      isFullscreen.value = false;
    }
  }
};

const handleFullscreenChange = () => {
  isFullscreen.value = !!document.fullscreenElement;
};

// --- PDF Logic ---
const renderPdfPage = async (pageNumber) => {
  if (!pdfDoc || !pdfCanvas.value) return;

  if (isRendering) {
    pageRenderPending = pageNumber;
    return;
  }

  isRendering = true;

  try {
    const page = await pdfDoc.getPage(pageNumber);
    const canvas = pdfCanvas.value;
    const ctx = canvas.getContext('2d');

    const viewport = page.getViewport({ scale: zoomScale.value });
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    const renderContext = {
      canvasContext: ctx,
      viewport: viewport,
    };

    await page.render(renderContext).promise;
    isRendering = false;

    if (pageRenderPending !== null) {
      const pending = pageRenderPending;
      pageRenderPending = null;
      renderPdfPage(pending);
    }
  } catch (err) {
    console.error('Error rendering PDF page:', err);
    isRendering = false;
  }
};

const queueRenderPage = (num) => {
  pdfCurrentPage.value = num;
  inputPage.value = num;
  renderPdfPage(num);
};

const prevPdfPage = () => {
  if (pdfCurrentPage.value <= 1) return;
  queueRenderPage(pdfCurrentPage.value - 1);
};

const nextPdfPage = () => {
  if (pdfCurrentPage.value >= pdfTotalPages.value) return;
  queueRenderPage(pdfCurrentPage.value + 1);
};

const jumpToPdfPage = () => {
  let p = parseInt(inputPage.value);
  if (isNaN(p) || p < 1) p = 1;
  if (p > pdfTotalPages.value) p = pdfTotalPages.value;
  queueRenderPage(p);
};

const zoomIn = () => {
  if (zoomScale.value < 2.5) {
    zoomScale.value = parseFloat((zoomScale.value + 0.2).toFixed(1));
    renderPdfPage(pdfCurrentPage.value);
  }
};

const zoomOut = () => {
  if (zoomScale.value > 0.6) {
    zoomScale.value = parseFloat((zoomScale.value - 0.2).toFixed(1));
    renderPdfPage(pdfCurrentPage.value);
  }
};

const resetZoom = () => {
  zoomScale.value = 1.2;
  renderPdfPage(pdfCurrentPage.value);
};

// --- EPUB Logic ---
const initEpub = async (arrayBuffer) => {
  try {
    await nextTick();
    if (!epubViewerRef.value) return;

    epubViewerRef.value.innerHTML = '';
    epubBook = ePub(arrayBuffer);

    epubRendition = epubBook.renderTo(epubViewerRef.value, {
      width: '100%',
      height: '100%',
      spread: 'none',
      flow: 'paginated',
    });

    await epubRendition.display();

    // Load Navigation / TOC
    const nav = await epubBook.loaded.navigation;
    if (nav?.toc) {
      toc.value = nav.toc.map((item) => ({
        label: item.label,
        href: item.href,
      }));
    }

    applyEpubFontSize();
  } catch (err) {
    console.error('Error loading EPUB:', err);
    error.value = 'Gagal memproses format file EPUB.';
  }
};

const prevEpubPage = () => {
  if (epubRendition) {
    epubRendition.prev();
  }
};

const nextEpubPage = () => {
  if (epubRendition) {
    epubRendition.next();
  }
};

const increaseFontSize = () => {
  if (epubFontSize.value < 200) {
    epubFontSize.value += 10;
    applyEpubFontSize();
  }
};

const decreaseFontSize = () => {
  if (epubFontSize.value > 70) {
    epubFontSize.value -= 10;
    applyEpubFontSize();
  }
};

const applyEpubFontSize = () => {
  if (epubRendition?.themes) {
    epubRendition.themes.fontSize(`${epubFontSize.value}%`);
  }
};

const goToChapter = (href) => {
  if (epubRendition) {
    epubRendition.display(href);
    showToc.value = false;
  }
};

// --- Load Book & File ---
const fetchBookAndLoad = async () => {
  loading.value = true;
  error.value = null;
  loadingMessage.value = 'Mengambil informasi buku...';

  try {
    // 1. Fetch metadata
    const metaRes = await api.get(`/books/${bookId}`);
    book.value = metaRes.data.data;
    format.value = (book.value.file_format || 'pdf').toLowerCase();

    // 2. Fetch authenticated binary file stream
    loadingMessage.value = 'Mengunduh file ebook terenkripsi...';
    const fileRes = await api.get(`/books/${bookId}/read`, {
      responseType: 'arraybuffer',
    });

    const arrayBuffer = fileRes.data;

    // Detect format if not clear
    if (format.value === 'pdf') {
      loadingMessage.value = 'Memuat pembaca PDF...';
      const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
      pdfDoc = await loadingTask.promise;
      pdfTotalPages.value = pdfDoc.numPages;
      pdfCurrentPage.value = 1;
      inputPage.value = 1;

      loading.value = false;
      await nextTick();
      renderPdfPage(1);
    } else if (format.value === 'epub') {
      loadingMessage.value = 'Menyiapkan bab EPUB...';
      loading.value = false;
      await nextTick();
      await initEpub(arrayBuffer);
    }
  } catch (err) {
    console.error('Error fetching ebook:', err);
    if (err.response?.status === 401) {
      toastStore.warning('Sesi Anda telah berakhir. Silakan masuk kembali.');
      router.push({ path: '/login', query: { redirect: route.fullPath } });
    } else if (err.response?.status === 404) {
      error.value = 'Berkas ebook belum tersedia untuk buku ini.';
    } else {
      error.value = err.response?.data?.message || 'Gagal memuat berkas ebook.';
    }
    loading.value = false;
  }
};

// Keyboard shortcut navigation (Left/Right arrow)
const handleKeyDown = (e) => {
  if (e.key === 'ArrowLeft') {
    if (format.value === 'pdf') prevPdfPage();
    else if (format.value === 'epub') prevEpubPage();
  } else if (e.key === 'ArrowRight') {
    if (format.value === 'pdf') nextPdfPage();
    else if (format.value === 'epub') nextEpubPage();
  }
};

onMounted(() => {
  document.addEventListener('fullscreenchange', handleFullscreenChange);
  window.addEventListener('keydown', handleKeyDown);
  fetchBookAndLoad();
});

onBeforeUnmount(() => {
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
  window.removeEventListener('keydown', handleKeyDown);

  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
  }
  if (epubBook) {
    try {
      epubBook.destroy();
    } catch {}
  }
});
</script>

<style scoped>
/* Custom styling for reader canvas */
canvas {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
}
</style>
