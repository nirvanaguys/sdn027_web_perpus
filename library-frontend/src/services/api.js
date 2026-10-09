import axios from "axios";
import { authStore } from "../stores/auth";

const api = axios.create({
  baseURL: "http://127.0.0.1:8000/api",
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

// Request Interceptor: Attach Sanctum Bearer Token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response Interceptor: Handle 401 Unauthorized
// Catatan: JANGAN redirect saat request-nya sendiri adalah /login atau
// /register — kalau tidak, kredensial salah justru melempar user ke /login
// berulang + toast warning dari guard, yang terlihat seperti halaman rusak.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const url = error.config?.url || "";
      const isAuthAttempt = url.includes("/login") || url.includes("/register");
      authStore.logout();
      if (!isAuthAttempt) {
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  },
);

export default api;

export const assetUrl = (path) => {
  if (!path) return null;
  if (/^https?:\/\//i.test(path)) return path;

  const apiOrigin = new URL(api.defaults.baseURL).origin;
  return new URL(path, apiOrigin).toString();
};
