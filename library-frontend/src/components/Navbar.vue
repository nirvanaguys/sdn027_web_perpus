<template>
  <header
    class="sticky top-0 z-40 backdrop-blur-xl bg-white/85 border-b border-slate-200/80 transition-all"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Brand Logo -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div
            class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-700 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 group-hover:shadow-indigo-500/40 transition-all duration-300"
          >
            <BookOpen class="w-6 h-6 stroke-[2.2]" />
          </div>
          <div>
            <span
              class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-800 bg-clip-text text-transparent"
            >
              Perpus<span class="text-indigo-600">Ku</span>
            </span>
            <span
              class="hidden sm:block text-[11px] font-medium text-slate-400 tracking-wider uppercase"
            >
              Perpustakaan SDN 027 Balikpapan Utara
            </span>
          </div>
        </router-link>

        <!-- Desktop Navigation Links -->
        <nav
          class="hidden md:flex items-center gap-1.5 bg-slate-100/70 p-1.5 rounded-2xl border border-slate-200/60"
        >
          <router-link
            to="/"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
            :class="
              $route.path === '/'
                ? 'bg-white text-indigo-600 shadow-sm'
                : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'
            "
          >
            <Library class="w-4 h-4" />
            <span>Katalog Buku</span>
          </router-link>

          <!-- Member: My Loans -->
          <router-link
            v-if="authStore.isAuthenticated() && !authStore.isAdmin()"
            to="/my-loans"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
            :class="
              $route.path === '/my-loans'
                ? 'bg-white text-indigo-600 shadow-sm'
                : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'
            "
          >
            <Bookmark class="w-4 h-4" />
            <span>Buku Saya</span>
            <span
              v-if="activeLoansCount > 0"
              class="ml-1 px-1.5 py-0.5 text-xs font-bold rounded-full bg-indigo-600 text-white"
            >
              {{ activeLoansCount }}
            </span>
          </router-link>

          <!-- Admin: Inventory -->
          <router-link
            v-if="authStore.isAdmin()"
            to="/admin/books"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
            :class="
              $route.path.startsWith('/admin')
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 hover:text-indigo-600 hover:bg-white/50'
            "
          >
            <ShieldCheck class="w-4 h-4" />
            <span>Kelola Inventaris</span>
          </router-link>
        </nav>

        <!-- Desktop User Actions -->
        <div class="hidden md:flex items-center gap-3">
          <template v-if="authStore.isAuthenticated()">
            <div
              class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-2xl bg-slate-50 border border-slate-200/80"
            >
              <div
                class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs uppercase text-white shadow-xs"
                :class="
                  authStore.isAdmin()
                    ? 'bg-gradient-to-tr from-amber-500 to-orange-500'
                    : 'bg-gradient-to-tr from-indigo-500 to-violet-500'
                "
              >
                {{ getUserInitials() }}
              </div>
              <div class="text-left">
                <div class="text-xs font-bold text-slate-800 leading-tight">
                  {{ authStore.user?.name }}
                </div>
                <div class="flex items-center gap-1.5">
                  <span
                    class="text-[10px] font-bold px-1.5 py-0.2 rounded-md tracking-wider uppercase"
                    :class="
                      authStore.isAdmin()
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-indigo-100 text-indigo-800'
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
              class="p-2.5 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer border border-transparent hover:border-rose-100"
            >
              <LogOut class="w-5 h-5" />
            </button>
          </template>

          <template v-else>
            <router-link
              to="/login"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/35 transition duration-200 cursor-pointer"
            >
              <User class="w-4 h-4" />
              <span>Masuk Akun</span>
            </router-link>
          </template>
        </div>

        <!-- Mobile Menu Toggle Button -->
        <div class="flex md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2.5 rounded-xl text-slate-700 hover:bg-slate-100 transition cursor-pointer"
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
        class="md:hidden border-t border-slate-200 bg-white/95 backdrop-blur-xl px-4 pt-3 pb-6 space-y-3"
      >
        <router-link
          @click="mobileMenuOpen = false"
          to="/"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition"
          :class="
            $route.path === '/'
              ? 'bg-indigo-50 text-indigo-600'
              : 'text-slate-700 hover:bg-slate-50'
          "
        >
          <Library class="w-5 h-5" />
          <span>Katalog Buku</span>
        </router-link>

        <router-link
          v-if="authStore.isAuthenticated() && !authStore.isAdmin()"
          @click="mobileMenuOpen = false"
          to="/my-loans"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition"
          :class="
            $route.path === '/my-loans'
              ? 'bg-indigo-50 text-indigo-600'
              : 'text-slate-700 hover:bg-slate-50'
          "
        >
          <Bookmark class="w-5 h-5" />
          <span>Buku Saya</span>
        </router-link>

        <router-link
          v-if="authStore.isAdmin()"
          @click="mobileMenuOpen = false"
          to="/admin/books"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition bg-indigo-600 text-white"
        >
          <ShieldCheck class="w-5 h-5" />
          <span>Dashboard Admin</span>
        </router-link>

        <div class="pt-3 border-t border-slate-100">
          <template v-if="authStore.isAuthenticated()">
            <div
              class="flex items-center justify-between px-3 py-2 bg-slate-50 rounded-xl mb-3"
            >
              <div>
                <p class="text-xs text-slate-500">Login sebagai</p>
                <p class="text-sm font-bold text-slate-900">
                  {{ authStore.user?.name }}
                </p>
              </div>
              <span
                class="text-xs px-2 py-0.5 rounded-md font-semibold bg-indigo-100 text-indigo-700 uppercase"
              >
                {{ authStore.user?.role }}
              </span>
            </div>
            <button
              @click="handleLogout"
              class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 transition cursor-pointer"
            >
              <LogOut class="w-4 h-4" />
              <span>Keluar (Logout)</span>
            </button>
          </template>
          <template v-else>
            <router-link
              @click="mobileMenuOpen = false"
              to="/login"
              class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition"
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
import { ref, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import api from "../services/api";
import {
  BookOpen,
  Library,
  Bookmark,
  ShieldCheck,
  User,
  LogOut,
  Menu,
  X,
} from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const mobileMenuOpen = ref(false);
const activeLoansCount = ref(0);

const getUserInitials = () => {
  const name = authStore.user?.name || "User";
  return name
    .split(" ")
    .map((w) => w[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
};

const fetchLoansCount = async () => {
  if (authStore.isAuthenticated() && !authStore.isAdmin()) {
    try {
      const res = await api.get("/transactions/my");
      const loans = res.data.data || [];
      activeLoansCount.value = loans.filter(
        (l) => l.status === "borrowed",
      ).length;
    } catch {
      activeLoansCount.value = 0;
    }
  } else {
    activeLoansCount.value = 0;
  }
};

watch(
  () => route.path,
  () => {
    mobileMenuOpen.value = false;
    fetchLoansCount();
  },
);

onMounted(() => {
  fetchLoansCount();
});

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
