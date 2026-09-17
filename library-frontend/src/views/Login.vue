<template>
  <div class="min-h-[85vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
      <!-- Card Container -->
      <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-200/80 backdrop-blur-xl relative overflow-hidden">
        
        <!-- Header Branding -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 mb-3">
            <Sparkles class="w-6 h-6" />
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            {{ isRegister ? 'Daftar Anggota Baru' : 'Selamat Datang Kembali' }}
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">
            {{ isRegister ? 'Buat akun untuk mulai meminjam koleksi buku' : 'Akses akun sistem perpustakaan PustakaKu' }}
          </p>
        </div>

        <!-- Mode Toggle Tabs (Masuk vs Daftar) -->
        <div class="flex p-1 bg-slate-100 rounded-2xl mb-6">
          <button
            type="button"
            @click="isRegister = false"
            class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer"
            :class="!isRegister ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
          >
            Masuk (Login)
          </button>
          <button
            type="button"
            @click="isRegister = true"
            class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer"
            :class="isRegister ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
          >
            Daftar Akun
          </button>
        </div>

        <!-- 1-Click Quick Demo Accounts (Only on Login tab) -->
        <div v-if="!isRegister" class="mb-6 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">
            Akses Cepat Akun Demo (1-Click Login):
          </span>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="fillDemo('admin')"
              class="px-3 py-2 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <ShieldCheck class="w-3.5 h-3.5" />
              <span>Admin Demo</span>
            </button>
            <button
              type="button"
              @click="fillDemo('member')"
              class="px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100 transition flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <User class="w-3.5 h-3.5" />
              <span>Member Demo</span>
            </button>
          </div>
        </div>

        <!-- Login Form -->
        <form v-if="!isRegister" @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="email"
                type="email"
                required
                placeholder="nama@email.com"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
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
            class="w-full py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 cursor-pointer mt-2"
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
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
            <div class="relative">
              <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="regName"
                type="text"
                required
                placeholder="Nama Anda"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="regEmail"
                type="email"
                required
                placeholder="nama@email.com"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi (Min. 8 Karakter)</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="regPassword"
                type="password"
                required
                minlength="8"
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input
                v-model="regPasswordConfirm"
                type="password"
                required
                minlength="8"
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition"
              />
            </div>
          </div>

          <button
            :disabled="loading"
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold rounded-2xl text-sm transition shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 cursor-pointer mt-2"
          >
            <div
              v-if="loading"
              class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
            ></div>
            <span>{{ loading ? 'Mendaftarkan...' : 'Buat Akun Anggota' }}</span>
          </button>
        </form>
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
import {
  Sparkles,
  ShieldCheck,
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
const email = ref('admin@perpustakaan.com');
const password = ref('admin123');

// Register state
const regName = ref('');
const regEmail = ref('');
const regPassword = ref('');
const regPasswordConfirm = ref('');

const fillDemo = (role) => {
  if (role === 'admin') {
    email.value = 'admin@perpustakaan.com';
    password.value = 'admin123';
  } else {
    email.value = 'member@perpustakaan.com';
    password.value = 'member123';
  }
  handleLogin();
};

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

    if (user.role === 'admin') {
      router.push('/admin/books');
    } else {
      const redirect = route.query.redirect || '/';
      router.push(redirect);
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
    router.push('/');
  } catch (err) {
    const msg = err.response?.data?.message || 'Registrasi gagal. Mohon periksa kembali data Anda.';
    toastStore.error(msg);
  } finally {
    loading.value = false;
  }
};
</script>
