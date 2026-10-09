<template>
  <div class="relative min-h-[88vh] overflow-hidden">
    <!-- Background foto sekolah sd027.jpg -->
    <div class="absolute inset-0" aria-hidden="true">
      <img :src="schoolImg" alt="" class="h-full w-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-br from-[#08170c]/90 via-[#0e2f14]/72 to-[#14532d]/55"></div>
      <div class="absolute inset-0 bg-[radial-gradient(700px_320px_at_20%_10%,rgba(139,207,120,0.22),transparent_65%)]"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 grid items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
      <!-- Panel kiri: penjelasan -->
      <div class="anim-fade-up hidden lg:flex flex-col justify-center self-stretch py-10 text-white">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-50 backdrop-blur">
            <KeyRound class="h-4 w-4 text-emerald-200" />
            Lupa Kata Sandi
          </div>
          <h1 class="mt-5 text-4xl xl:text-5xl font-extrabold leading-[1.08] tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
            Reset Sendiri,
            <span class="block bg-gradient-to-r from-[#c9ecb4] via-white to-[#d9f2c7] bg-clip-text text-transparent">
              Kapan Saja
            </span>
          </h1>
          <p class="mt-4 max-w-md text-sm leading-relaxed text-emerald-50/90">
            Cukup isi <strong>email</strong> dan <strong>NISN</strong> (untuk siswa)
            atau <strong>NIP</strong> (untuk guru). Tidak perlu menunggu pustakawan —
            verifikasi berhasil, langsung buat kata sandi baru.
          </p>
        </div>
      </div>

      <!-- Branding ringkas mobile -->
      <div class="anim-fade-up lg:hidden text-white text-center">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-50 backdrop-blur">
          <KeyRound class="h-4 w-4 text-emerald-200" />
          Lupa Kata Sandi
        </div>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
          Reset Sendiri, Kapan Saja
        </h1>
        <p class="mt-3 max-w-md mx-auto text-xs leading-relaxed text-emerald-50/90">
          Isi email dan NISN (siswa) atau NIP (guru), lalu buat kata sandi baru.
        </p>
      </div>

      <!-- Card form -->
      <div class="anim-fade-up-1 mx-auto w-full max-w-md">
        <div class="rounded-3xl border-2 border-[#14532d] bg-white p-7 shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] sm:p-8 relative overflow-hidden">
          <div class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#14532d] via-[#248900] to-[#8bcf78]" aria-hidden="true"></div>

          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#248900] to-[#14532d] text-white shadow-lg shadow-[#248900]/30 mb-3">
              <KeyRound class="w-6 h-6" />
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
              {{ step === 1 ? 'Verifikasi Identitas' : 'Buat Sandi Baru' }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              {{ step === 1
                ? 'Langkah 1 dari 2 — buktikan akun ini milikmu'
                : 'Langkah 2 dari 2 — tiket verifikasi berlaku 10 menit' }}
            </p>
          </div>

          <!-- Progress -->
          <div class="flex items-center gap-2 mb-6" aria-hidden="true">
            <div class="h-1.5 flex-1 rounded-full bg-[#248900]"></div>
            <div class="h-1.5 flex-1 rounded-full" :class="step === 2 ? 'bg-[#248900]' : 'bg-slate-200'"></div>
          </div>

          <!-- Langkah 1 -->
          <form v-if="step === 1" @submit.prevent="handleVerify" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fp-email">Alamat Email Terdaftar</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="fp-email"
                  v-model="email"
                  type="email"
                  required
                  autocomplete="email"
                  placeholder="nama@email.com"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fp-identity">NISN / NIP</label>
              <div class="relative">
                <IdCard class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="fp-identity"
                  v-model="identityNumber"
                  type="text"
                  required
                  inputmode="numeric"
                  placeholder="Siswa: NISN • Guru: NIP"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
              <p class="mt-1.5 text-[11px] leading-relaxed text-slate-400">
                NISN ada di rapor atau tanya wali kelas. Masih gagal? Hubungi pustakawan.
              </p>
            </div>

            <button
              :disabled="loading"
              type="submit"
              class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 flex items-center justify-center gap-2 cursor-pointer mt-2 disabled:opacity-70"
            >
              <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              <span>{{ loading ? 'Memverifikasi...' : 'Verifikasi Identitas' }}</span>
              <ArrowRight v-if="!loading" class="w-4 h-4" />
            </button>
          </form>

          <!-- Langkah 2 -->
          <form v-else @submit.prevent="handleReset" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fp-new">Kata Sandi Baru (Min. 8)</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="fp-new"
                  v-model="newPassword"
                  type="password"
                  required
                  minlength="8"
                  autocomplete="new-password"
                  placeholder="••••••••"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fp-new2">Konfirmasi Sandi Baru</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="fp-new2"
                  v-model="newPasswordConfirm"
                  type="password"
                  required
                  minlength="8"
                  autocomplete="new-password"
                  placeholder="••••••••"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
            </div>

            <button
              :disabled="loading"
              type="submit"
              class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 flex items-center justify-center gap-2 cursor-pointer mt-2 disabled:opacity-70"
            >
              <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              <span>{{ loading ? 'Menyimpan...' : 'Ganti Kata Sandi' }}</span>
              <CheckCircle v-if="!loading" class="w-4 h-4" />
            </button>

            <button
              type="button"
              @click="backToStep1"
              class="w-full py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer"
            >
              ← Kembali ke verifikasi identitas
            </button>
          </form>

          <p class="mt-5 text-center text-[11px] leading-relaxed text-slate-400">
            Sudah ingat sandimu?
            <router-link to="/login" class="font-bold text-[#1c6d00] hover:underline">Kembali masuk</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { toastStore } from '../stores/toast';
import schoolImg from '../assets/library-frontend/sd027.jpg';
import {
  KeyRound,
  Mail,
  IdCard,
  Lock,
  ArrowRight,
  CheckCircle,
} from 'lucide-vue-next';

const router = useRouter();

const step = ref(1);
const loading = ref(false);

const email = ref('');
const identityNumber = ref('');
const resetTicket = ref('');

const newPassword = ref('');
const newPasswordConfirm = ref('');

const handleVerify = async () => {
  loading.value = true;
  try {
    const res = await api.post('/forgot-password/verify', {
      email: email.value,
      identity_number: identityNumber.value,
    });
    resetTicket.value = res.data.data.reset_ticket;
    step.value = 2;
    toastStore.success('Identitas cocok! Silakan buat kata sandi baru.');
  } catch (err) {
    const msg = err.response?.data?.message || 'Verifikasi gagal. Periksa email dan NISN/NIP Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};

const handleReset = async () => {
  if (newPassword.value !== newPasswordConfirm.value) {
    toastStore.error('Konfirmasi kata sandi tidak cocok!');
    return;
  }
  loading.value = true;
  try {
    await api.post('/forgot-password/reset', {
      reset_ticket: resetTicket.value,
      password: newPassword.value,
      password_confirmation: newPasswordConfirm.value,
    });
    toastStore.success('Kata sandi diganti! Silakan masuk dengan sandi baru.');
    router.push('/login');
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal mengganti kata sandi.';
    toastStore.error(msg);
    // Tiket kedaluwarsa -> kembali ke langkah 1.
    if (err.response?.status === 422) {
      step.value = 1;
      resetTicket.value = '';
    }
  } finally {
    loading.value = false;
  }
};

const backToStep1 = () => {
  step.value = 1;
  resetTicket.value = '';
  newPassword.value = '';
  newPasswordConfirm.value = '';
};
</script>
