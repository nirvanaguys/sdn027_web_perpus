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
        <h1 class="text-4xl xl:text-5xl font-extrabold leading-[1.08] tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
          Perpustakaan Digital
          <span class="block">SDN 027 Balikpapan Utara</span>
        </h1>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-emerald-50/90">
          Masuk untuk membaca ebook, menyimpan Reading List, dan melihat riwayat
          bacaanmu. Akun baru otomatis menjadi anggota perpustakaan.
        </p>
        </div>
      </div>

      <!-- Branding ringkas untuk layar kecil (panel kiri disembunyikan di mobile) -->
      <div class="anim-fade-up lg:hidden text-white text-center">
        <h1 class="text-3xl font-extrabold leading-tight tracking-tight drop-shadow-[0_2px_20px_rgba(0,0,0,0.4)]">
          Perpustakaan Digital
          <span class="block">SDN 027 Balikpapan Utara</span>
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
              @click="switchMode(false)"
              class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer"
              :class="!isRegister ? 'bg-white text-[#1c6d00] shadow-sm ring-1 ring-[#248900]/20' : 'text-slate-500 hover:text-slate-800'"
            >
              Masuk (Login)
            </button>
            <button
              type="button"
              @click="switchMode(true)"
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
                  @input="loginErrors.email = null"
                  type="email"
                  required
                  autocomplete="email"
                  placeholder="nama@email.com"
                  :aria-invalid="!!loginErrors.email"
                  :aria-describedby="loginErrors.email ? 'login-email-err' : undefined"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                  :class="loginErrors.email ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                />
              </div>
              <p v-if="loginErrors.email" id="login-email-err" class="mt-1 text-xs text-rose-600 font-medium">
                {{ Array.isArray(loginErrors.email) ? loginErrors.email[0] : loginErrors.email }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="login-password">Kata Sandi</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="login-password"
                  v-model="password"
                  @input="loginErrors.password = null"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="••••••••"
                  :aria-invalid="!!loginErrors.password"
                  :aria-describedby="loginErrors.password ? 'login-password-err' : undefined"
                  class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                  :class="loginErrors.password ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  :aria-pressed="showPassword"
                  :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                  class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <EyeOff v-if="showPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
              <p v-if="loginErrors.password" id="login-password-err" class="mt-1 text-xs text-rose-600 font-medium">
                {{ Array.isArray(loginErrors.password) ? loginErrors.password[0] : loginErrors.password }}
              </p>
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

            <div class="pt-1 text-[11px] leading-relaxed text-slate-500">
              <router-link to="/reset-password" class="font-bold text-[#1c6d00] hover:underline">
                Lupa kata sandi?
              </router-link>
              Minta tautan reset via email, atau lapor ke pustakawan SDN 027.
            </div>
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
                  @input="regErrors.name = null"
                  type="text"
                  required
                  autocomplete="name"
                  placeholder="Nama Anda"
                  :aria-invalid="!!regErrors.name"
                  :aria-describedby="regErrors.name ? 'reg-name-err' : undefined"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                  :class="regErrors.name ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                />
              </div>
              <p v-if="regErrors.name" id="reg-name-err" class="mt-1 text-xs text-rose-600 font-medium">
                {{ Array.isArray(regErrors.name) ? regErrors.name[0] : regErrors.name }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-email">Alamat Email</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input
                  id="reg-email"
                  v-model="regEmail"
                  @input="regErrors.email = null"
                  type="email"
                  required
                  autocomplete="email"
                  placeholder="nama@email.com"
                  :aria-invalid="!!regErrors.email"
                  :aria-describedby="regErrors.email ? 'reg-email-err' : undefined"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                  :class="regErrors.email ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                />
              </div>
              <p v-if="regErrors.email" id="reg-email-err" class="mt-1 text-xs text-rose-600 font-medium">
                {{ Array.isArray(regErrors.email) ? regErrors.email[0] : regErrors.email }}
              </p>
            </div>


            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-pass">Kata Sandi (Min. 8)</label>
                <div class="relative">
                  <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                  <input
                    id="reg-pass"
                    v-model="regPassword"
                    @input="regErrors.password = null"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :aria-invalid="!!regErrors.password"
                    :aria-describedby="regErrors.password ? 'reg-pass-err' : undefined"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                    :class="regErrors.password ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                  />
                </div>
                <p v-if="regErrors.password" id="reg-pass-err" class="mt-1 text-xs text-rose-600 font-medium">
                  {{ Array.isArray(regErrors.password) ? regErrors.password[0] : regErrors.password }}
                </p>
                <p v-else class="mt-1 text-[11px] text-slate-400">Min. 8 karakter (huruf & angka).</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="reg-pass2">Konfirmasi Sandi</label>
                <div class="relative">
                  <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                  <input
                    id="reg-pass2"
                    v-model="regPasswordConfirm"
                    @input="regErrors.password_confirmation = null"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :aria-invalid="!!regErrors.password_confirmation"
                    :aria-describedby="regErrors.password_confirmation ? 'reg-pass2-err' : undefined"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none transition"
                    :class="regErrors.password_confirmation ? 'border-rose-400 bg-rose-50/20 focus:border-rose-500 focus:ring-2 focus:ring-rose-200' : 'border-slate-200 focus:border-[#248900]/50 focus:ring-2 focus:ring-[#248900]/25'"
                  />
                </div>
                <p v-if="regErrors.password_confirmation" id="reg-pass2-err" class="mt-1 text-xs text-rose-600 font-medium">
                  {{ Array.isArray(regErrors.password_confirmation) ? regErrors.password_confirmation[0] : regErrors.password_confirmation }}
                </p>
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
const loginErrors = ref({});

// Register state
const regName = ref('');
const regEmail = ref('');
const regPassword = ref('');
const regPasswordConfirm = ref('');
const regErrors = ref({});

const switchMode = (mode) => {
  isRegister.value = mode;
  loginErrors.value = {};
  regErrors.value = {};
};

const handleLogin = async () => {
  loginErrors.value = {};
  if (!email.value) {
    loginErrors.value.email = 'Alamat email wajib diisi.';
    return;
  }
  if (!password.value) {
    loginErrors.value.password = 'Kata sandi wajib diisi.';
    return;
  }

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
    const data = err.response?.data;
    if (data?.errors) {
      loginErrors.value = data.errors;
    } else if (err.response?.status === 401 || err.response?.status === 422) {
      loginErrors.value = {
        email: data?.message || 'Kredensial yang diberikan tidak cocok dengan data kami.',
      };
    }
    const msg = data?.message || 'Login gagal. Periksa kembali email dan kata sandi Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};

const handleRegister = async () => {
  regErrors.value = {};
  let hasError = false;

  if (!regName.value) {
    regErrors.value.name = 'Nama lengkap wajib diisi.';
    hasError = true;
  }
  if (!regEmail.value) {
    regErrors.value.email = 'Alamat email wajib diisi.';
    hasError = true;
  }
  if (!regPassword.value) {
    regErrors.value.password = 'Kata sandi wajib diisi.';
    hasError = true;
  } else if (regPassword.value.length < 8) {
    regErrors.value.password = 'Kata sandi minimal harus 8 karakter.';
    hasError = true;
  }
  if (regPassword.value !== regPasswordConfirm.value) {
    regErrors.value.password_confirmation = 'Konfirmasi kata sandi tidak cocok!';
    hasError = true;
  }

  if (hasError) {
    toastStore.error('Mohon periksa dan lengkapi data pendaftaran Anda.');
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
    const data = err.response?.data;
    if (data?.errors) {
      regErrors.value = data.errors;
    }
    const msg = data?.message || 'Registrasi gagal. Mohon periksa kembali data Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};
</script>
