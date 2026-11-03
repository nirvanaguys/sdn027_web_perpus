import { ref } from "vue";
import api from "../services/api";
import { toastStore } from "../stores/toast";

// Jenjang kelas dipakai bersama oleh katalog dan panel admin.
export const GRADES = [
  { value: "umum", label: "Umum / Semua Kelas" },
  { value: "kelas-1", label: "Kelas 1" },
  { value: "kelas-2", label: "Kelas 2" },
  { value: "kelas-3", label: "Kelas 3" },
  { value: "kelas-4", label: "Kelas 4" },
  { value: "kelas-5", label: "Kelas 5" },
  { value: "kelas-6", label: "Kelas 6" },
];

export const gradeLabel = (value) =>
  GRADES.find((g) => g.value === (value || "umum"))?.label ?? GRADES[0].label;

const clean = (params) =>
  Object.fromEntries(
    Object.entries(params).filter(([, v]) => v !== "" && v != null),
  );

/**
 * Satu-satunya module yang tahu bentuk respons /books: halaman buku,
 * ringkasan katalog (counts) dan daftar kategori.
 *
 * Pemanggil tetap pemilik filter dan debounce-nya; module ini hanya
 * menerima params siap kirim plus nomor halaman.
 */
export function useCatalog(perPage) {
  const books = ref([]);
  const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
  const counts = ref({ total: 0, pdf: 0, epub: 0, ready: 0 });
  const categories = ref([]);
  const loading = ref(false);

  const load = async (params = {}, page = 1) => {
    loading.value = true;
    try {
      const { data } = await api.get("/books", {
        params: { per_page: perPage, page, ...clean(params) },
      });
      const payload = data.data;
      books.value = payload.data || [];
      pagination.value = {
        current_page: payload.current_page || 1,
        last_page: payload.last_page || 1,
        total: payload.total || 0,
      };
      if (data.meta) {
        counts.value = data.meta.counts;
        categories.value = data.meta.categories || [];
      }
    } catch (error) {
      console.error("Gagal memuat data buku:", error);
      toastStore.error("Gagal memuat data buku.");
    } finally {
      loading.value = false;
    }
  };

  return { books, pagination, counts, categories, loading, load };
}
