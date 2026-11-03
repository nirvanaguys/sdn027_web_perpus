import { reactive } from 'vue';

export const toastStore = reactive({
  toasts: [],

  add(message, type = 'info', duration = 4000) {
    // UI-06: Hindari toast duplikat bertumpuk saat aksi cepat
    const existing = this.toasts.find((t) => t.message === message && t.type === type);
    if (existing) {
      return;
    }

    const id = Date.now() + Math.random().toString(36).substring(2, 7);
    this.toasts.push({ id, message, type });

    if (duration > 0) {
      setTimeout(() => {
        this.remove(id);
      }, duration);
    }
  },

  remove(id) {
    const index = this.toasts.findIndex((t) => t.id === id);
    if (index !== -1) {
      this.toasts.splice(index, 1);
    }
  },

  success(message, duration = 4000) {
    this.add(message, 'success', duration);
  },

  error(message, duration = 5000) {
    this.add(message, 'error', duration);
  },

  info(message, duration = 4000) {
    this.add(message, 'info', duration);
  },

  warning(message, duration = 4000) {
    this.add(message, 'warning', duration);
  }
});
