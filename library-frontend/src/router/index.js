import { createRouter, createWebHistory } from 'vue-router';
import { authStore } from '../stores/auth';
import { toastStore } from '../stores/toast';
import BookCatalog from '../views/BookCatalog.vue';
import Login from '../views/Login.vue';
import AdminDashboard from '../views/AdminDashboard.vue';
import MyLoans from '../views/MyLoans.vue';

const routes = [
  {
    path: '/',
    name: 'BookCatalog',
    component: BookCatalog,
    meta: { requiresAuth: false }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guestOnly: true }
  },
  {
    path: '/my-loans',
    name: 'MyLoans',
    component: MyLoans,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/books',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = authStore.isAuthenticated();
  const isAdmin = authStore.isAdmin();

  if (to.meta.guestOnly && isAuthenticated) {
    return next({ path: '/' });
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    toastStore.warning('Silakan masuk ke akun Anda terlebih dahulu.');
    return next({ path: '/login', query: { redirect: to.fullPath } });
  }

  if (to.meta.role === 'admin' && !isAdmin) {
    toastStore.error('Akses Ditolak: Hanya Pustakawan/Admin yang diizinkan.');
    return next({ path: '/' });
  }

  next();
});

export default router;
