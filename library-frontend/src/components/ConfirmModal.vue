<template>
  <transition
    enter-active-class="ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
      @click.self="cancel"
    >
      <div
        class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 border border-slate-100 transform transition-all"
      >
        <div class="flex items-start gap-4">
          <div
            class="p-3 rounded-2xl shrink-0"
            :class="isDanger ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600'"
          >
            <component :is="isDanger ? AlertTriangle : HelpCircle" class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 leading-snug">
              {{ title }}
            </h3>
            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
              {{ message }}
            </p>
          </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <button
            type="button"
            @click="cancel"
            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer"
          >
            {{ cancelText }}
          </button>
          <button
            type="button"
            @click="confirm"
            :disabled="loading"
            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg transition flex items-center gap-2 cursor-pointer"
            :class="isDanger
              ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25'
              : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25'"
          >
            <div
              v-if="loading"
              class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
            ></div>
            <span>{{ confirmText }}</span>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { AlertTriangle, HelpCircle } from 'lucide-vue-next';

defineProps({
  isOpen: { type: Boolean, default: false },
  title: { type: String, default: 'Konfirmasi Tindakan' },
  message: { type: String, default: 'Apakah Anda yakin ingin melanjutkan?' },
  confirmText: { type: String, default: 'Lanjutkan' },
  cancelText: { type: String, default: 'Batal' },
  isDanger: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

const confirm = () => emit('confirm');
const cancel = () => emit('cancel');
</script>
