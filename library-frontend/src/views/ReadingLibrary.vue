<template>
  <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <header
      class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200 pb-6"
    >
      <div>
        <p class="text-sm font-semibold text-emerald-700">PerpusKu</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">
          {{ isHistory ? "Riwayat bacaan" : "Reading List" }}
        </h1>
        <p class="mt-2 text-sm text-slate-600">
          {{
            isHistory
              ? "Ebook yang pernah Anda buka."
              : "Ebook yang Anda simpan untuk dibaca nanti."
          }}
        </p>
      </div>
      <nav
        class="flex gap-5 text-sm font-semibold"
        aria-label="Perpustakaan saya"
      >
        <router-link
          to="/history"
          :class="
            isHistory
              ? 'text-emerald-700'
              : 'text-slate-500 hover:text-slate-900'
          "
          >Riwayat</router-link
        >
        <router-link
          to="/reading-list"
          :class="
            !isHistory
              ? 'text-emerald-700'
              : 'text-slate-500 hover:text-slate-900'
          "
          >Reading List</router-link
        >
      </nav>
    </header>

    <div v-if="loading" class="py-16 text-center text-sm text-slate-500">
      Memuat koleksi...
    </div>
    <div v-else-if="items.length === 0" class="py-20 text-center">
      <BookOpen class="mx-auto h-9 w-9 text-emerald-700" />
      <h2 class="mt-4 text-lg font-semibold text-slate-900">
        {{
          isHistory ? "Belum ada riwayat bacaan" : "Reading List masih kosong"
        }}
      </h2>
      <p class="mt-1 text-sm text-slate-500">
        Jelajahi katalog dan pilih ebook yang ingin dibaca.
      </p>
      <router-link
        to="/"
        class="mt-5 inline-flex items-center gap-2 font-semibold text-emerald-700 hover:text-emerald-900"
      >
        Jelajahi katalog <ArrowRight class="h-4 w-4" />
      </router-link>
    </div>
    <ul v-else class="divide-y divide-slate-200">
      <li
        v-for="entry in items"
        :key="entry.id"
        class="flex items-center gap-4 py-5"
      >
        <img
          v-if="entry.book?.cover_url"
          :src="assetUrl(entry.book.cover_url)"
          :alt="`Sampul ${entry.book.title}`"
          class="h-24 w-16 rounded object-cover"
        />
        <div
          v-else
          class="flex h-24 w-16 shrink-0 items-end rounded bg-emerald-800 p-2 text-xs font-bold text-white"
        >
          {{ entry.book?.title }}
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="truncate font-semibold text-slate-900">
            {{ entry.book?.title || "Ebook tidak tersedia" }}
          </h2>
          <p class="mt-1 text-sm text-slate-600">
            {{ entry.book?.author || "-" }}
          </p>
          <p class="mt-2 text-xs text-slate-500">
            {{
              isHistory
                ? `Terakhir dibaca ${formatDate(entry.last_read_at)}`
                : (entry.book?.file_format || "").toUpperCase()
            }}
          </p>
        </div>
        <button
          v-if="!isHistory"
          type="button"
          @click="removeBook(entry.book_id)"
          class="p-2 text-slate-500 hover:text-rose-700"
          aria-label="Hapus dari Reading List"
          title="Hapus dari Reading List"
        >
          <BookmarkX class="h-5 w-5" />
        </button>
        <router-link
          v-if="entry.book"
          :to="`/read/${entry.book.id}`"
          class="inline-flex items-center gap-2 rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
        >
          <BookOpen class="h-4 w-4" /> Baca
        </router-link>
      </li>
    </ul>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { ArrowRight, BookOpen, BookmarkX } from "lucide-vue-next";
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
