<template>
  <header
    class="sticky top-0 z-40 overflow-x-clip border-b border-[#e2e8e0]/90 bg-white/90 shadow-[0_8px_30px_-18px_rgba(12,36,18,0.35)] backdrop-blur-xl transition-all"
  >
    <!-- Tipis aksen hijau khas sekolah -->
    <div class="h-1 w-full bg-gradient-to-r from-[#14532d] via-[#248900] to-[#8bcf78]" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3 h-[4.5rem]">
        <!-- Brand Logo -->
        <router-link to="/" class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3 group" aria-label="PerpusKu - Beranda">
          <span class="relative shrink-0">
            <img
              :src="brandLogo"
              alt="Logo Perpustakaan SDN 027"
              class="h-9 w-14 sm:h-11 sm:w-[4.25rem] shrink-0 rounded-xl bg-white object-contain p-1 ring-1 ring-slate-200/80 transition-transform duration-200 group-hover:scale-[1.04]"
            />
            <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-500" aria-hidden="true"></span>
          </span>
          <div class="min-w-0">
            <span
              class="block truncate text-lg sm:text-xl font-extrabold leading-none tracking-tight text-slate-900"
            >
              Perpus<span class="text-[#248900]">Ku</span>
            </span>
            <span
              class="mt-1 hidden truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400 sm:block"
            >
              SDN 027 Balikpapan Utara
            </span>
          </div>
        </router-link>

        <!-- Desktop Navigation Links -->
        <nav
          aria-label="Navigasi utama"
          class="hidden items-center gap-1 rounded-2xl border border-slate-200/70 bg-[#f1f5f0]/80 p-1.5 md:flex"
        >
          <router-link
            to="/"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200"
            :class="
              $route.path === '/'
                ? 'bg-white text-[#1c6d00] shadow-[0_8px_20px_-12px_rgba(36,137,0,0.55)] ring-1 ring-[#248900]/20'
                : 'text-slate-600 hover:bg-white/70 hover:text-slate-900'
            "
          >
            <Library class="w-4 h-4" />
            <span>Katalog Ebook</span>
          </router-link>

          <router-link
            v-if="authStore.isAuthenticated()"
            to="/history"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200"
            :class="
              $route.path === '/history'
                ? 'bg-white text-[#1c6d00] shadow-[0_8px_20px_-12px_rgba(36,137,0,0.55)] ring-1 ring-[#248900]/20'
                : 'text-slate-600 hover:bg-white/70 hover:text-slate-900'
            "
          >
            <History class="w-4 h-4" />
            <span>Riwayat</span>
          </router-link>

          <router-link
            v-if="authStore.isAuthenticated()"
            to="/reading-list"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200"
            :class="
              $route.path === '/reading-list'
                ? 'bg-white text-[#1c6d00] shadow-[0_8px_20px_-12px_rgba(36,137,0,0.55)] ring-1 ring-[#248900]/20'
                : 'text-slate-600 hover:bg-white/70 hover:text-slate-900'
            "
          >
            <Bookmark class="w-4 h-4" />
            <span>Reading List</span>
          </router-link>

          <!-- Admin: Collection Management -->
          <router-link
            v-if="authStore.isAdmin()"
            to="/admin/books"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200"
            :class="
              $route.path.startsWith('/admin')
                ? 'bg-[#248900] text-white shadow-[0_10px_24px_-12px_rgba(36,137,0,0.7)]'
                : 'text-slate-600 hover:bg-white/70 hover:text-[#1c6d00]'
            "
          >
            <ShieldCheck class="w-4 h-4" />
            <span>Koleksi Buku</span>
          </router-link>
        </nav>

        <!-- Desktop User Actions -->
        <div class="hidden md:flex items-center gap-3">
          <template v-if="authStore.isAuthenticated()">
            <div
              class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white py-1.5 pl-2 pr-3 shadow-[0_10px_30px_-22px_rgba(15,23,42,0.5)]"
            >
              <div
                class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-bold uppercase text-white"
                :class="
                  authStore.isAdmin()
                    ? 'bg-gradient-to-tr from-amber-500 to-orange-500'
                    : 'bg-gradient-to-tr from-[#248900] to-[#14532d]'
                "
              >
                {{ getUserInitials() }}
              </div>
              <div class="text-left">
                <div class="max-w-[10rem] truncate text-xs font-bold leading-tight text-slate-800">
                  {{ authStore.user?.name }}
                </div>
                <div class="mt-1 flex items-center gap-1.5">
                  <span
                    class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                    :class="
                      authStore.isAdmin()
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-[#eef9e6] text-[#1c6d00]'
                    "
                  >
                    {{ authStore.isAdmin() ? "Pustakawan" : "Anggota" }}
                  </span>
                </div>
              </div>
            </div>

            <button
              @click="handleLogout"
              title="Keluar dari akun"
              class="rounded-xl border border-transparent p-2.5 text-slate-500 transition hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600 cursor-pointer"
            >
              <LogOut class="w-5 h-5" />
            </button>
          </template>

          <template v-else>
            <router-link
              to="/login"
              class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#248900] to-[#14532d] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_14px_30px_-14px_rgba(36,137,0,0.65)] transition duration-200 hover:brightness-[1.06] cursor-pointer"
            >
              <User class="w-4 h-4" />
              <span>Masuk Akun</span>
            </router-link>
          </template>
        </div>

        <!-- Mobile Menu Toggle Button -->
        <div class="flex shrink-0 md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="rounded-xl border border-slate-200/80 bg-white p-2.5 text-slate-700 shadow-sm transition hover:bg-slate-50 cursor-pointer"
            :aria-expanded="mobileMenuOpen ? 'true' : 'false'"
            aria-label="Buka menu navigasi"
          >
            <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Drawer / Menu -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform -translate-y-4 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-4 opacity-0"
    >
      <div
        v-if="mobileMenuOpen"
        class="border-t border-slate-200 bg-white/95 px-4 pt-3 pb-6 space-y-2 backdrop-blur-xl md:hidden"
      >
        <router-link
          @click="mobileMenuOpen = false"
          to="/"
          class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition"
          :class="
            $route.path === '/'
              ? 'bg-[#eef9e6] text-[#1c6d00] ring-1 ring-[#248900]/20'
              : 'text-slate-700 hover:bg-slate-50'
          "
        >
          <Library class="w-5 h-5" />
          <span>Katalog Ebook</span>
        </router-link>

        <template v-if="authStore.isAuthenticated()">
          <router-link
            @click="mobileMenuOpen = false"
            to="/history"
            class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition"
            :class="$route.path === '/history' ? 'bg-[#eef9e6] text-[#1c6d00]' : 'text-slate-700 hover:bg-slate-50'"
          >
            <History class="w-5 h-5" />
            <span>Riwayat</span>
          </router-link>
          <router-link
            @click="mobileMenuOpen = false"
            to="/reading-list"
            class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition"
            :class="$route.path === '/reading-list' ? 'bg-[#eef9e6] text-[#1c6d00]' : 'text-slate-700 hover:bg-slate-50'"
          >
            <Bookmark class="w-5 h-5" />
            <span>Reading List</span>
          </router-link>
        </template>

        <router-link
          v-if="authStore.isAdmin()"
          @click="mobileMenuOpen = false"
          to="/admin/books"
          class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition bg-[#248900] text-white"
        >
          <ShieldCheck class="w-5 h-5" />
          <span>Koleksi Buku</span>
        </router-link>

        <div class="pt-3 border-t border-slate-100">
          <template v-if="authStore.isAuthenticated()">
            <div
              class="mb-3 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2"
            >
              <div class="min-w-0">
                <p class="text-xs text-slate-500">Login sebagai</p>
                <p class="truncate text-sm font-bold text-slate-900">
                  {{ authStore.user?.name }}
                </p>
              </div>
              <span
                class="rounded-md bg-[#eef9e6] px-2 py-0.5 text-xs font-semibold uppercase text-[#1c6d00]"
              >
                {{ authStore.user?.role }}
              </span>
            </div>
            <button
              @click="handleLogout"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 cursor-pointer"
            >
              <LogOut class="w-4 h-4" />
              <span>Keluar (Logout)</span>
            </button>
          </template>
          <template v-else>
            <router-link
              @click="mobileMenuOpen = false"
              to="/login"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#248900] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1c6d00]"
            >
              <User class="w-4 h-4" />
              <span>Masuk Akun</span>
            </router-link>
          </template>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import api from "../services/api";
import brandLogo from "../assets/logoperpus.svg";
import {
  Library,
  ShieldCheck,
  History,
  Bookmark,
  User,
  LogOut,
  Menu,
  X,
} from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const mobileMenuOpen = ref(false);

const getUserInitials = () => {
  const name = authStore.user?.name || "User";
  return name
    .split(" ")
    .map((w) => w[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
};

watch(
  () => route.path,
  () => {
    mobileMenuOpen.value = false;
  },
);

const handleLogout = async () => {
  try {
    await api.post("/logout");
  } catch (err) {
    console.error("Logout error:", err);
  } finally {
    authStore.logout();
    toastStore.info("Anda telah berhasil keluar.");
    mobileMenuOpen.value = false;
    router.push("/login");
  }
};
</script>
