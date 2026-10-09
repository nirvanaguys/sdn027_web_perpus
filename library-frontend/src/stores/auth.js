import { reactive } from 'vue';

// localStorage bisa berisi JSON rusak (mis. tertulis "undefined" oleh versi
// lama). Tanpa try/catch, JSON.parse melempar error saat modul dimuat dan
// SELURUH aplikasi jadi halaman putih blank — termasuk /login.
const readStoredUser = () => {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null');
  } catch {
    localStorage.removeItem('user');
    return null;
  }
};

export const authStore = reactive({
  user: readStoredUser(),
  token: localStorage.getItem('token') || null,

  setAuth(user, token) {
    this.user = user;
    this.token = token;
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('token', token);
  },

  logout() {
    this.user = null;
    this.token = null;
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  },

  isAuthenticated() {
    return !!this.token;
  },

  isAdmin() {
    return this.user?.role === 'admin';
  }
});
