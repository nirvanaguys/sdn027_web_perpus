<template>
  <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <header
      class="anim-fade-up rounded-3xl border border-[#248900]/15 bg-gradient-to-br from-white via-[#f3faf0] to-[#e7f3df] p-6 sm:p-8 shadow-[0_24px_55px_-35px_rgba(20,83,45,0.55)]"
    >
      <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="inline-flex items-center gap-1.5 rounded-full bg-[#0e2f14] px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-emerald-50">
            <Library class="h-3.5 w-3.5" /> PerpusKu
          </p>
          <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
            {{ isHistory ? "Riwayat bacaan" : "Reading List" }}
          </h1>
          <p class="mt-2 max-w-xl text-sm text-slate-600">
            {{
              isHistory
                ? "Ebook yang pernah Anda buka. Lanjutkan bacaan favoritmu kapan saja."
                : "Ebook yang Anda simpan untuk dibaca nanti. Semua tersimpan rapi di satu tempat."
            }}
          </p>
        </div>
        <nav
          class="flex gap-1 rounded-2xl border border-slate-200/80 bg-white p-1.5 text-sm font-semibold shadow-sm"
          aria-label="Perpustakaan saya"
        >
          <router-link
            to="/history"
            class="rounded-xl px-4 py-2 transition"
            :class="
              isHistory
                ? 'bg-[#0e2f14] text-white shadow'
                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'
            "
            >Riwayat</router-link
          >
          <router-link
            to="/reading-list"
            class="rounded-xl px-4 py-2 transition"
            :class="
              !isHistory
                ? 'bg-[#0e2f14] text-white shadow'
                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'
            "
            >Reading List</router-link
          >
        </nav>
      </div>
      <div class="mt-5 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">
          <BookOpen class="h-3.5 w-3.5 text-[#1c6d00]" /> {{ items.length }} ebook tersimpan
        </span>
        <router-link
          to="/"
          class="inline-flex items-center gap-1.5 rounded-full bg-[#248900] px-3 py-1.5 text-white transition hover:bg-[#1c6d00]"
        >
          Tambah dari katalog <ArrowRight class="h-3.5 w-3.5" />
        </router-link>
      </div>
    </header>

    <div v-if="loading" class="grid gap-4 py-8 sm:grid-cols-2">
      <div v-for="n in 4" :key="n" class="flex animate-pulse items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div class="h-24 w-16 rounded-xl bg-slate-200"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-2/3 rounded bg-slate-200"></div>
          <div class="h-3 w-1/3 rounded bg-slate-100"></div>
          <div class="h-8 w-28 rounded-xl bg-slate-200"></div>
        </div>
      </div>
    </div>
    <div v-else-if="items.length === 0" class="anim-fade-up mx-auto mt-8 max-w-lg rounded-3xl border border-dashed border-slate-300 bg-white px-8 py-14 text-center shadow-sm">
      <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eef9e6] text-[#1c6d00]">
        <BookOpen class="h-8 w-8" />
      </span>
      <h2 class="mt-4 text-lg font-bold text-slate-900">
        {{
          isHistory ? "Belum ada riwayat bacaan" : "Reading List masih kosong"
        }}
      </h2>
      <p class="mt-1 text-sm text-slate-500">
        Jelajahi katalog dan pilih ebook yang ingin dibaca.
      </p>
      <router-link
        to="/"
        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#248900] to-[#14532d] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#248900]/25 transition hover:brightness-[1.07]"
      >
        Jelajahi katalog <ArrowRight class="h-4 w-4" />
      </router-link>
    </div>
    <ul v-else class="mt-6 grid gap-4 sm:grid-cols-2">
      <li
        v-for="entry in items"
        :key="entry.id"
        class="lift anim-fade-up flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)] hover:border-[#248900]/30"
      >
        <img
          v-if="entry.book?.cover_url"
          :src="assetUrl(entry.book.cover_url)"
          :alt="`Sampul ${entry.book.title}`"
          loading="lazy"
          class="h-24 w-16 shrink-0 rounded-xl object-cover ring-1 ring-slate-200"
        />
        <div
          v-else
          class="flex h-24 w-16 shrink-0 items-end rounded-xl bg-gradient-to-br from-[#14532d] to-[#0e2f14] p-2 text-[11px] font-bold leading-tight text-white"
        >
          <span class="line-clamp-4">{{ entry.book?.title }}</span>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="truncate font-bold text-slate-900">
            {{ entry.book?.title || "Ebook tidak tersedia" }}
          </h2>
          <p class="mt-0.5 truncate text-sm text-slate-600">
            {{ entry.book?.author || "-" }}
          </p>
          <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
            {{
              isHistory
                ? `Terakhir dibaca ${formatDate(entry.last_read_at)}`
                : (entry.book?.file_format || "EBOOK").toUpperCase()
            }}
          </p>
          <div class="mt-3 flex items-center gap-2">
            <router-link
              v-if="entry.book"
              :to="`/read/${entry.book.id}`"
              class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#248900] to-[#14532d] px-4 py-2 text-xs font-bold text-white shadow-md shadow-[#248900]/25 transition hover:brightness-[1.07]"
            >
              <BookOpen class="h-3.5 w-3.5" /> Baca
            </router-link>
            <button
              v-if="!isHistory"
              type="button"
              @click="removeBook(entry.book_id)"
              class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 cursor-pointer"
              aria-label="Hapus dari Reading List"
              title="Hapus dari Reading List"
            >
              <BookmarkX class="h-4 w-4" /> Hapus
            </button>
          </div>
        </div>
      </li>
    </ul>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { ArrowRight, BookOpen, BookmarkX, Library } from "lucide-vue-next";
import api, { assetUrl } from "../services/api";
import { toastStore } from "../stores/toast";

const route = useRoute();
const items = ref([]);
const loading = ref(false);
const isHistory = computed(() => route.path === "/history");

const fetchItems = async () => {
  loading.value = true;
  try {
    const endpoint = isHistory.value ? "/reading-history" : "/reading-list";
    const response = await api.get(endpoint);
    items.value = response.data.data || [];
  } catch (error) {
    toastStore.error("Gagal memuat koleksi bacaan.");
  } finally {
    loading.value = false;
  }
};

const removeBook = async (bookId) => {
  try {
    await api.delete(`/reading-list/${bookId}`);
    items.value = items.value.filter((entry) => entry.book_id !== bookId);
    toastStore.success("Ebook dihapus dari Reading List.");
  } catch (error) {
    toastStore.error("Gagal menghapus ebook dari Reading List.");
  }
};

const formatDate = (value) =>
  value
    ? new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(
        new Date(value),
      )
    : "-";

onMounted(fetchItems);
watch(() => route.path, fetchItems);
</script>
