<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-8 gap-4">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold mb-2">
          <ShieldCheck class="w-3.5 h-3.5" />
          <span>Panel Kontrol Pustakawan</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
          Pengelolaan Akun Anggota
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Lihat, ubah, reset kata sandi, dan nonaktifkan akun — tanpa hapus data permanen.
        </p>
      </div>
    </div>

    <!-- Info reset password -->
    <div class="mb-6 rounded-2xl border border-amber-200/70 bg-amber-50 p-4 text-xs sm:text-sm text-amber-800 leading-relaxed">
      <strong>Alur lupa kata sandi:</strong> siswa/guru melapor ke pustakawan,
      lalu gunakan tombol <strong>Reset Sandi</strong> pada akun terkait.
      Akun bermasalah cukup <strong>dinonaktifkan</strong> — riwayat baca dan data
      peminjaman tetap tersimpan dan bisa diaktifkan kembali kapan saja.
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Total Akun</span>
          <div class="w-9 h-9 rounded-xl bg-[#eef9e6] text-[#1c6d00] flex items-center justify-center">
            <Users class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-800 mt-2">{{ users.length }}</div>
        <div class="text-xs text-slate-400 mt-1">Terdaftar di sistem</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Anggota Aktif</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <CheckCircle class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-emerald-600 mt-2">{{ activeCount }}</div>
        <div class="text-xs text-slate-400 mt-1">Bisa login & membaca</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Dinonaktifkan</span>
          <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
            <Ban class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-rose-600 mt-2">{{ inactiveCount }}</div>
        <div class="text-xs text-slate-400 mt-1">Ditolak saat login</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-400">Pustakawan</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <ShieldCheck class="w-5 h-5" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-amber-600 mt-2">{{ adminCount }}</div>
        <div class="text-xs text-slate-400 mt-1">Role admin</div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)]">
      <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
          <input
            v-model="tableSearch"
            type="text"
            placeholder="Cari nama, email, atau NISN/NIP..."
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
          />
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
          <span class="text-slate-400">Filter Status:</span>
          <select
            v-model="statusFilter"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
          >
            <option value="all">Semua Status</option>
            <option value="aktif">Aktif Saja</option>
            <option value="nonaktif">Nonaktif Saja</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-xs sm:text-sm">
          <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
            <tr>
              <th class="px-6 py-4">Nama & Email</th>
              <th class="px-6 py-4">NISN / NIP</th>
              <th class="px-6 py-4">Peran</th>
              <th class="px-6 py-4 text-center">Status</th>
              <th class="px-6 py-4">Terdaftar</th>
              <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold uppercase text-white"
                    :class="user.role === 'admin'
                      ? 'bg-gradient-to-tr from-amber-500 to-orange-500'
                      : 'bg-gradient-to-tr from-[#248900] to-[#14532d]'"
                  >
                    {{ userInitials(user.name) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-slate-900 leading-snug truncate">{{ user.name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 font-mono text-xs text-slate-500">
                {{ user.identity_number || "-" }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="user.role === 'admin'
                    ? 'bg-amber-100 text-amber-800'
                    : 'bg-[#eef9e6] text-[#1c6d00]'"
                >
                  {{ user.role === 'admin' ? 'Pustakawan' : 'Anggota' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="user.is_active
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80'
                    : 'bg-rose-50 text-rose-700 border border-rose-200/80'"
                >
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="openEdit(user)"
                    title="Ubah nama / email / peran"
                    class="p-2 rounded-xl text-[#1c6d00] hover:bg-[#eef9e6] transition cursor-pointer"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    @click="openReset(user)"
                    title="Reset kata sandi akun ini"
                    class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 transition cursor-pointer"
                  >
                    <KeyRound class="w-4 h-4" />
                  </button>
                  <button
                    v-if="user.id !== authStore.user?.id"
                    @click="askToggleActive(user)"
                    :title="user.is_active ? 'Nonaktifkan akun' : 'Aktifkan kembali akun'"
                    class="p-2 rounded-xl transition cursor-pointer"
                    :class="user.is_active
                      ? 'text-rose-600 hover:bg-rose-50'
                      : 'text-emerald-600 hover:bg-emerald-50'"
                  >
                    <Ban v-if="user.is_active" class="w-4 h-4" />
                    <CheckCircle v-else class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                <Users class="w-8 h-8 mx-auto mb-2 opacity-50" />
                <span>Tidak ada akun yang sesuai.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} ({{ pagination.total }} akun)</span>
        <div class="flex gap-2">
          <button
            @click="fetchUsers(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-1.5 rounded-lg border border-slate-200 font-semibold hover:bg-slate-50 disabled:opacity-50 cursor-pointer"
          >
            Sebelumnya
          </button>
          <button
            @click="fetchUsers(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-1.5 rounded-lg border border-slate-200 font-semibold hover:bg-slate-50 disabled:opacity-50 cursor-pointer"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Edit -->
    <transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="editOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" @click.self="editOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 border border-slate-100">
          <h3 class="text-xl font-bold text-slate-900">Ubah Data Akun</h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-5">Perubahan tersimpan langsung ke akun anggota.</p>
          <form @submit.prevent="saveEdit" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
              <input v-model="editForm.name" required type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
              <input v-model="editForm.email" required type="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">NISN / NIP</label>
              <input v-model="editForm.identity_number" type="text" placeholder="Kosongkan jika belum ada" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Peran</label>
              <select v-model="editForm.role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                <option value="member">Anggota</option>
                <option value="admin">Pustakawan (Admin)</option>
              </select>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" @click="editOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Batal</button>
              <button type="submit" :disabled="saving" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-[#248900] to-[#14532d] rounded-xl transition disabled:opacity-70 cursor-pointer">
                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Modal Reset Password -->
    <transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="resetOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" @click.self="resetOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 border border-slate-100">
          <h3 class="text-xl font-bold text-slate-900">Reset Kata Sandi</h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-5">
            Akun: <strong class="text-slate-700">{{ resetTarget?.name }}</strong>
            ({{ resetTarget?.email }}). Sampaikan sandi baru ini ke pemilik akun.
          </p>
          <form @submit.prevent="saveReset" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Baru (min. 8 karakter)</label>
              <input v-model="resetForm.password" required minlength="8" type="text" placeholder="Contoh: sdn027baru" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
              <input v-model="resetForm.password_confirmation" required minlength="8" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition" />
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" @click="resetOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Batal</button>
              <button type="submit" :disabled="saving" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition disabled:opacity-70 cursor-pointer">
                {{ saving ? 'Mereset...' : 'Reset Kata Sandi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Konfirmasi nonaktif/aktif -->
    <ConfirmModal
      :isOpen="toggleOpen"
      :title="toggleTarget?.is_active ? 'Nonaktifkan Akun' : 'Aktifkan Kembali Akun'"
      :message="toggleTarget?.is_active
        ? `Akun '${toggleTarget?.name}' tidak bisa login sampai diaktifkan lagi. Riwayat baca dan datanya tetap tersimpan. Lanjutkan?`
        : `Akun '${toggleTarget?.name}' bisa login kembali. Lanjutkan?`"
      :confirmText="toggleTarget?.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
      cancelText="Batal"
      :isDanger="!!toggleTarget?.is_active"
      :loading="saving"
      @confirm="executeToggle"
      @cancel="toggleOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../services/api";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import ConfirmModal from "../components/ConfirmModal.vue";
import {
  ShieldCheck,
  Users,
  Search,
  Edit3,
  KeyRound,
  Ban,
  CheckCircle,
} from "lucide-vue-next";

const users = ref([]);
const tableSearch = ref("");
const statusFilter = ref("all");
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const editOpen = ref(false);
const editTarget = ref(null);
const editForm = ref({ name: "", email: "", identity_number: "", role: "member" });

const resetOpen = ref(false);
const resetTarget = ref(null);
const resetForm = ref({ password: "", password_confirmation: "" });

const toggleOpen = ref(false);
const toggleTarget = ref(null);
const saving = ref(false);

const fetchUsers = async (page = 1) => {
  try {
    const res = await api.get("/admin/users", { params: { per_page: 15, page } });
    const payload = res.data.data;
    users.value = payload.data || [];
    pagination.value = {
      current_page: payload.current_page || 1,
      last_page: payload.last_page || 1,
      total: payload.total || 0,
    };
  } catch (err) {
    console.error("Gagal mengambil data akun:", err);
    toastStore.error("Gagal mengambil data akun anggota.");
  }
};

const activeCount = computed(() => users.value.filter((u) => u.is_active).length);
const inactiveCount = computed(() => users.value.filter((u) => !u.is_active).length);
const adminCount = computed(() => users.value.filter((u) => u.role === "admin").length);

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    if (tableSearch.value) {
      const q = tableSearch.value.toLowerCase();
      const match =
        u.name?.toLowerCase().includes(q) ||
        u.email?.toLowerCase().includes(q) ||
        u.identity_number?.toLowerCase().includes(q);
      if (!match) return false;
    }
    if (statusFilter.value === "aktif" && !u.is_active) return false;
    if (statusFilter.value === "nonaktif" && u.is_active) return false;
    return true;
  });
});

const userInitials = (name) => {
  return (name || "U").split(" ").map((w) => w[0]).join("").slice(0, 2).toUpperCase();
};

const formatDate = (iso) => {
  if (!iso) return "-";
  return new Date(iso).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const openEdit = (user) => {
  editTarget.value = user;
  editForm.value = { name: user.name, email: user.email, identity_number: user.identity_number || "", role: user.role };
  editOpen.value = true;
};

const saveEdit = async () => {
  if (!editTarget.value) return;
  saving.value = true;
  try {
    await api.put(`/admin/users/${editTarget.value.id}`, {
      name: editForm.value.name,
      email: editForm.value.email,
      identity_number: editForm.value.identity_number || null,
      role: editForm.value.role,
    });
    toastStore.success("Data akun berhasil diperbarui!");
    editOpen.value = false;
    fetchUsers(pagination.value.current_page);
  } catch (err) {
    const msg = err.response?.data?.message || "Gagal memperbarui data akun.";
    toastStore.error(msg);
  } finally {
    saving.value = false;
  }
};

const openReset = (user) => {
  resetTarget.value = user;
  resetForm.value = { password: "", password_confirmation: "" };
  resetOpen.value = true;
};

const saveReset = async () => {
  if (!resetTarget.value) return;
  if (resetForm.value.password !== resetForm.value.password_confirmation) {
    toastStore.error("Konfirmasi kata sandi tidak cocok!");
    return;
  }
  saving.value = true;
  try {
    await api.post(`/admin/users/${resetTarget.value.id}/reset-password`, resetForm.value);
    toastStore.success(`Kata sandi ${resetTarget.value.name} berhasil direset!`);
    resetOpen.value = false;
  } catch (err) {
    const msg = err.response?.data?.message || "Gagal mereset kata sandi.";
    toastStore.error(msg);
  } finally {
    saving.value = false;
  }
};

const askToggleActive = (user) => {
  toggleTarget.value = user;
  toggleOpen.value = true;
};

const executeToggle = async () => {
  if (!toggleTarget.value) return;
  saving.value = true;
  try {
    const res = await api.post(`/admin/users/${toggleTarget.value.id}/set-active`, {
      is_active: !toggleTarget.value.is_active,
    });
    toastStore.success(res.data.message || "Status akun diperbarui.");
    toggleOpen.value = false;
    fetchUsers(pagination.value.current_page);
  } catch (err) {
    const msg = err.response?.data?.message || "Gagal mengubah status akun.";
    toastStore.error(msg);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});
</script>
