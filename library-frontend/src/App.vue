<template>
  <div
    class="min-h-screen bg-[#f6f8f7] flex flex-col font-sans selection:bg-[#248900] selection:text-white text-slate-900"
  >
    <Navbar />
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer
      class="relative mt-16 overflow-hidden border-t border-white/10 bg-[#0c2412] text-emerald-50/80"
    >
      <div
        class="pointer-events-none absolute inset-0 opacity-40"
        aria-hidden="true"
        style="
          background-image:
            radial-gradient(560px 180px at 15% 0%, rgba(46, 163, 0, 0.35), transparent 65%),
            radial-gradient(520px 200px at 88% 100%, rgba(20, 83, 45, 0.6), transparent 60%);
        "
      ></div>
      <div
        class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid gap-8 sm:grid-cols-[1.2fr_1fr_1fr]"
      >
        <div>
          <div class="flex items-center gap-2.5">
            <span
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-[#248900] to-[#14532d] text-white shadow-lg shadow-black/20"
            >
              <BookOpen class="h-5 w-5" />
            </span>
            <div>
              <p class="text-base font-extrabold tracking-tight text-white">
                Perpus<span class="text-[#8bcf78]">Ku</span>
              </p>
              <p class="text-[11px] font-medium uppercase tracking-widest text-emerald-100/70">
                SDN 027 Balikpapan Utara
              </p>
            </div>
          </div>
          <p class="mt-3 max-w-sm text-xs leading-relaxed text-emerald-50/70">
            Perpustakaan ebook digital sekolah. Cari koleksi, simpan ke Reading
            List, dan baca langsung dari peramban.
          </p>
          <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-emerald-50">
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Layanan perpustakaan aktif
          </div>
        </div>

        <nav aria-label="Navigasi perpustakaan">
          <p class="text-xs font-bold uppercase tracking-widest text-emerald-100/60">
            Jelajahi
          </p>
          <ul class="mt-3 space-y-2 text-sm font-medium">
            <li>
              <router-link to="/" class="text-emerald-50/85 transition hover:text-white">
                Katalog Ebook
              </router-link>
            </li>
            <li>
              <router-link to="/history" class="text-emerald-50/85 transition hover:text-white">
                Riwayat Bacaan
              </router-link>
            </li>
            <li>
              <router-link to="/reading-list" class="text-emerald-50/85 transition hover:text-white">
                Reading List
              </router-link>
            </li>
          </ul>
        </nav>

        <div>
          <p class="text-xs font-bold uppercase tracking-widest text-emerald-100/60">
            Sekolah
          </p>
          <ul class="mt-3 space-y-2 text-xs leading-relaxed text-emerald-50/75">
            <li>Perpustakaan Ebook Digital</li>
            <li>SDN 027 Balikpapan Utara</li>
            <li class="pt-1 text-emerald-100/60">
              &copy; {{ new Date().getFullYear() }} PerpusKu. Seluruh koleksi
              untuk kegiatan belajar.
            </li>
          </ul>
        </div>
      </div>
    </footer>

    <!-- Global Floating Toast Container -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { BookOpen } from "lucide-vue-next";
import Navbar from "./components/Navbar.vue";
import ToastContainer from "./components/ToastContainer.vue";
</script>
