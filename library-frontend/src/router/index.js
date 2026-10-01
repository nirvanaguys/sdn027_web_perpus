import { createRouter, createWebHistory } from "vue-router";
import { authStore } from "../stores/auth";
import { toastStore } from "../stores/toast";
import BookCatalog from "../views/BookCatalog.vue";
import Login from "../views/Login.vue";
import AdminDashboard from "../views/AdminDashboard.vue";
import EbookReader from "../views/EbookReader.vue";

const routes = [
  {
    path: "/",
    name: "BookCatalog",
    component: BookCatalog,
    meta: { requiresAuth: false },
  },
  {
    path: "/read/:id",
    name: "EbookReader",
    component: EbookReader,
    meta: { requiresAuth: true },
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: "/admin/books",
    name: "AdminDashboard",
    component: AdminDashboard,
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    // Redirect rute transaksi lama ke katalog utama
    path: "/my-loans",
    redirect: "/",
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = authStore.isAuthenticated();
  const isAdmin = authStore.isAdmin();

  if (to.meta.guestOnly && isAuthenticated) {
    return next({ path: "/" });
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    toastStore.warning(
      "Silakan masuk ke akun Anda terlebih dahulu untuk membaca ebook.",
    );
    return next({ path: "/login", query: { redirect: to.fullPath } });
  }

  if (to.meta.role === "admin" && !isAdmin) {
    toastStore.error("Akses Ditolak: Hanya Pustakawan/Admin yang diizinkan.");
    return next({ path: "/" });
  }

  next();
});

export default router;
