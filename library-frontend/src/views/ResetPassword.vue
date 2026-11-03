<template>
  <div class="relative min-h-[88vh] overflow-hidden">
    <div class="absolute inset-0" aria-hidden="true">
      <img :src="schoolImg" alt="" class="h-full w-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-br from-[#08170c]/90 via-[#0e2f14]/72 to-[#14532d]/55"></div>
    </div>

    <div class="relative max-w-md mx-auto px-4 py-14">
      <div class="relative overflow-hidden rounded-3xl border-2 border-[#14532d] bg-white p-7 shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] sm:p-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#14532d] via-[#248900] to-[#8bcf78]" aria-hidden="true"></div>

        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#248900] to-[#14532d] text-white shadow-lg shadow-[#248900]/30 mb-3">
            <KeyRound class="w-6 h-6" />
          </div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            {{ isReset ? 'Buat Sandi Baru' : 'Lupa Kata Sandi?' }}
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            {{ isReset
              ? 'Tautan dari email Anda. Sandi baru berlaku setelah disimpan.'
              : 'Masukkan email akun, kami kirimkan tautan untuk membuat sandi baru.' }}
          </p>
        </div>

        <!-- Sukses kirim tautan -->
        <div v-if="sent" class="rounded-2xl bg-[#eef9e6] border border-[#8bcf78] p-4 text-sm text-[#1c6d00]">
          <p class="font-semibold">Tautan dikirim.</p>
          <p class="mt-1 leading-relaxed">
            Periksa kotak masuk (atau folder spam) email yang Anda masukkan. Tautan berlaku 60 menit.
            Kalau tidak ada email masuk, hubungi pustakawan SDN 027.
          </p>
        </div>

        <!-- Minta tautan -->
        <form v-else-if="!isReset" @submit.prevent="submitRequest" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="fp-email">Alamat Email</label>
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
          <button
            :disabled="loading"
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 cursor-pointer disabled:opacity-70"
          >
            {{ loading ? 'Mengirim...' : 'Kirim Tautan Reset' }}
          </button>
        </form>

        <!-- Sandi baru -->
        <form v-else-if="!done" @submit.prevent="submitReset" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="rp-pass">Kata Sandi Baru (min. 8)</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                id="rp-pass"
                v-model="password"
                type="password"
                required
                autocomplete="new-password"
                minlength="8"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
              />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="rp-pass2">Ulangi Kata Sandi</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                id="rp-pass2"
                v-model="passwordConfirm"
                type="password"
                required
                autocomplete="new-password"
                minlength="8"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
              />
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Minimal 8 karakter, kombinasi huruf dan angka.</p>
          </div>
          <button
            :disabled="loading"
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 cursor-pointer disabled:opacity-70"
          >
            {{ loading ? 'Menyimpan...' : 'Simpan Sandi Baru' }}
          </button>
        </form>

        <div v-if="done" class="rounded-2xl bg-[#eef9e6] border border-[#8bcf78] p-4 text-sm text-[#1c6d00]">
          <p class="font-semibold">Sandi berhasil diganti.</p>
          <p class="mt-1">Silakan masuk dengan sandi baru Anda.</p>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
          <router-link to="/login" class="font-bold text-[#1c6d00] hover:underline">Kembali ke halaman masuk</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { toastStore } from '../stores/toast';
import schoolImg from '../assets/library-frontend/sd027.jpg';
import { KeyRound, Mail, Lock } from 'lucide-vue-next';

const route = useRoute();

const email = ref(route.query.email || '');
const token = route.query.token || '';
const isReset = computed(() => !!token);

const password = ref('');
const passwordConfirm = ref('');
const loading = ref(false);
const sent = ref(false);
const done = ref(false);

const submitRequest = async () => {
  loading.value = true;
  try {
    await api.post('/forgot-password', { email: email.value });
    sent.value = true;
  } catch (err) {
    toastStore.error(err.response?.data?.message || 'Gagal mengirim tautan. Coba lagi sebentar.');
  } finally {
    loading.value = false;
  }
};

const submitReset = async () => {
  if (password.value !== passwordConfirm.value) {
    toastStore.error('Konfirmasi kata sandi tidak cocok!');
    return;
  }
  loading.value = true;
  try {
    await api.post('/reset-password', {
      token,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    });
    done.value = true;
  } catch (err) {
    const data = err.response?.data;
    toastStore.error(data?.errors?.password?.[0] || data?.message || 'Gagal menyimpan sandi baru.');
  } finally {
    loading.value = false;
  }
};
</script>
