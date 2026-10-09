<template>
  <div class="relative min-h-[88vh] overflow-hidden">
    <!-- Background foto sekolah sd027.jpg -->
    <div class="absolute inset-0" aria-hidden="true">
      <img :src="schoolImg" alt="" class="h-full w-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-br from-[#08170c]/90 via-[#0e2f14]/72 to-[#14532d]/55"></div>
      <div class="absolute inset-0 bg-[radial-gradient(700px_320px_at_20%_10%,rgba(139,207,120,0.22),transparent_65%)]"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 grid items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
      <!-- Panel kiri: branding (tengah vertikal, sejajar kartu login) -->
      <div class="anim-fade-up hidden lg:flex flex-col justify-center self-stretch py-10 text-white">
        <div>
        <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-50 backdrop-blur">
          <BookOpen class="h-4 w-4 text-emerald-200" />
          Perpustakaan Ebook Digital
        </div>
        <h1 class="mt-5 text-4xl xl:text-5xl font-extrabold leading-[1.08] tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
          Perpustakaan Digital
          <span class="block bg-gradient-to-r from-[#c9ecb4] via-white to-[#d9f2c7] bg-clip-text text-transparent">
            SDN 027 Balikpapan Utara
          </span>
        </h1>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-emerald-50/90">
          Masuk untuk membaca ebook, menyimpan Reading List, dan melihat riwayat
          bacaanmu. Akun baru otomatis menjadi anggota perpustakaan.
        </p>
        </div>
      </div>

      <!-- Branding ringkas untuk layar kecil (panel kiri disembunyikan di mobile) -->
      <div class="anim-fade-up lg:hidden text-white text-center">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-50 backdrop-blur">
          <BookOpen class="h-4 w-4 text-emerald-200" />
          Perpustakaan Ebook Digital
        </div>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
          Perpustakaan Digital
          <span class="block bg-gradient-to-r from-[#c9ecb4] via-white to-[#d9f2c7] bg-clip-text text-transparent">
            SDN 027 Balikpapan Utara
          </span>
        </h1>
        <p class="mt-3 max-w-md mx-auto text-xs leading-relaxed text-emerald-50/90">
          Masuk untuk membaca ebook, menyimpan Reading List, dan melihat riwayat
          bacaanmu. Akun baru otomatis menjadi anggota perpustakaan.
        </p>
      </div>

      <!-- Card form -->
      <div class="anim-fade-up-1 mx-auto w-full max-w-md">
        <div class="rounded-3xl border-2 border-[#14532d] bg-white p-7 shadow-[0_40px_90px_-30px_rgba(0,0,0,0.6)] sm:p-8 relative overflow-hidden">
          <div class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#14532d] via-[#248900] to-[#8bcf78]" aria-hidden="true"></div>
          <!-- Header Branding -->
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#248900] to-[#14532d] text-white shadow-lg shadow-[#248900]/30 mb-3">
              <Library class="w-6 h-6" />
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
              {{ isRegister ? 'Daftar Anggota Baru' : 'Selamat Datang Kembali' }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              {{ isRegister ? 'Buat akun untuk mulai membaca koleksi ebook' : 'Akses akun perpustakaan PerpusKu' }}
            </p>
          </div>

          <!-- Mode Toggle Tabs (Masuk vs Daftar) -->
          <div class="flex p-1 bg-slate-100 rounded-2xl mb-5" role="tablist" aria-label="Pilih mode akun">
            <button
              type="button"
              @click="isRegister = false"
              class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer"
              :class="!isRegister ? 'bg-white text-[#1c6d00] shadow-sm ring-1 ring-[#248900]/20' : 'text-slate-500 hover:text-slate-800'"
            >
              Masuk (Login)
            </button>
            <button
              type="button"
              @click="isRegister = true"
              class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer"
              :class="isRegister ? 'bg-white text-[#1c6d00] shadow-sm ring-1 ring-[#248900]/20' : 'text-slate-500 hover:text-slate-800'"
            >
              Daftar Akun
            </button>
          </div>

          <!-- Login Form -->
          <form v-if="!isRegister" @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="login-email">Alamat Email</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="login-email"
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
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="login-password">Kata Sandi</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="login-password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="••••••••"
                  class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                  class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <EyeOff v-if="showPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <button
              :disabled="loading"
              type="submit"
              class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 flex items-center justify-center gap-2 cursor-pointer mt-2 disabled:opacity-70"
            >
              <div
                v-if="loading"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              ></div>
              <span>{{ loading ? 'Memverifikasi...' : 'Masuk Sekarang' }}</span>
              <ArrowRight v-if="!loading" class="w-4 h-4" />
            </button>
          </form>

          <!-- Register Form -->
          <form v-else @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-name">Nama Lengkap</label>
              <div class="relative">
                <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="reg-name"
                  v-model="regName"
                  type="text"
                  required
                  autocomplete="name"
                  placeholder="Nama Anda"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-email">Alamat Email</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="reg-email"
                  v-model="regEmail"
                  type="email"
                  required
                  autocomplete="email"
                  placeholder="nama@email.com"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                />
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-pass">Kata Sandi (Min. 8)</label>
                <div class="relative">
                  <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                  <input
                    id="reg-pass"
                    v-model="regPassword"
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
                <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-pass2">Konfirmasi Sandi</label>
                <div class="relative">
                  <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                  <input
                    id="reg-pass2"
                    v-model="regPasswordConfirm"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25 outline-none transition"
                  />
                </div>
              </div>
            </div>

            <button
              :disabled="loading"
              type="submit"
              class="w-full py-3 bg-gradient-to-r from-[#248900] to-[#14532d] hover:brightness-[1.07] text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-[#248900]/30 flex items-center justify-center gap-2 cursor-pointer mt-2 disabled:opacity-70"
            >
              <div
                v-if="loading"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              ></div>
              <span>{{ loading ? 'Mendaftarkan...' : 'Buat Akun Anggota' }}</span>
            </button>
          </form>

          <p class="mt-5 text-center text-[11px] leading-relaxed text-slate-400">
            Dengan masuk, kamu menyetujui tata tertib perpustakaan
            SDN 027 Balikpapan Utara.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../services/api';
import { authStore } from '../stores/auth';
import { toastStore } from '../stores/toast';
import schoolImg from '../assets/library-frontend/sd027.jpg';
import {
  Library,
  BookOpen,
  User,
  Mail,
  Lock,
  Eye,
  EyeOff,
  ArrowRight
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

const isRegister = ref(false);
const showPassword = ref(false);
const loading = ref(false);

// Login state
const email = ref('');
const password = ref('');

// Register state
const regName = ref('');
const regEmail = ref('');
const regPassword = ref('');
const regPasswordConfirm = ref('');

const handleLogin = async () => {
  loading.value = true;
  try {
    const res = await api.post('/login', {
      email: email.value,
      password: password.value,
    });

    const { user, token } = res.data.data;
    authStore.setAuth(user, token);
    toastStore.success(`Selamat datang kembali, ${user.name}!`);

    if (route.query.redirect) {
      router.push(route.query.redirect);
    } else if (user.role === 'admin') {
      router.push('/admin/books');
    } else {
      router.push('/');
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Login gagal. Periksa kembali email dan kata sandi Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};

const handleRegister = async () => {
  if (regPassword.value !== regPasswordConfirm.value) {
    toastStore.error('Konfirmasi kata sandi tidak cocok!');
    return;
  }

  loading.value = true;
  try {
    const res = await api.post('/register', {
      name: regName.value,
      email: regEmail.value,
      password: regPassword.value,
      password_confirmation: regPasswordConfirm.value,
    });

    const { user, token } = res.data.data;
    authStore.setAuth(user, token);
    toastStore.success(`Pendaftaran berhasil! Selamat bergabung, ${user.name}.`);
    router.push(route.query.redirect || '/');
  } catch (err) {
    const msg = err.response?.data?.message || 'Registrasi gagal. Mohon periksa kembali data Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};
</script>
