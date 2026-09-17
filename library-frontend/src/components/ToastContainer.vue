<template>
  <div class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl backdrop-blur-xl border text-sm transition-all"
        :class="toastClasses(toast.type)"
      >
        <component :is="getIcon(toast.type)" class="w-5 h-5 shrink-0 mt-0.5" />
        <div class="flex-1 font-medium leading-snug">
          {{ toast.message }}
        </div>
        <button
          @click="toastStore.remove(toast.id)"
          class="shrink-0 p-1 -mr-1 -mt-1 rounded-lg opacity-70 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { toastStore } from '../stores/toast';
import { CheckCircle, AlertCircle, Info, X } from 'lucide-vue-next';

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle;
    case 'error':
      return AlertCircle;
    case 'warning':
      return AlertCircle;
    default:
      return Info;
  }
};

const toastClasses = (type) => {
  switch (type) {
    case 'success':
      return 'bg-emerald-500/90 text-white border-emerald-400/30 shadow-emerald-500/20';
    case 'error':
      return 'bg-rose-500/95 text-white border-rose-400/30 shadow-rose-500/20';
    case 'warning':
      return 'bg-amber-500/95 text-white border-amber-400/30 shadow-amber-500/20';
    default:
      return 'bg-slate-900/95 text-white border-slate-700 shadow-slate-950/30';
  }
};
</script>
