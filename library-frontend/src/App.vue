<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
    <Navbar />
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Modern Minimal Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-8 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
          <span>Sistem Perpustakaan Digital & Sirkulasi Fisik • Full-Stack Decoupled</span>
        </div>
        <div class="text-slate-400">
          &copy; {{ new Date().getFullYear() }} PustakaKu. Modern UI/UX.
        </div>
      </div>
    </footer>

    <!-- Global Floating Toast Container -->
    <ToastContainer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
import ToastContainer from './components/ToastContainer.vue';
</script>
