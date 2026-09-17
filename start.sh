#!/bin/bash

# Tangani Ctrl+C agar mematikan kedua proses anak
trap 'kill $(jobs -p) 2>/dev/null' EXIT INT TERM

echo "=========================================="
echo "  Memulai Sistem Manajemen Perpustakaan"
echo "=========================================="

# 1. Jalankan Backend (Laravel API)
echo "[1/2] Menjalankan Backend Laravel API (http://127.0.0.1:8000)..."
cd "$(dirname "$0")/library-api" && php artisan serve --host=127.0.0.1 --port=8000 &

# 2. Jalankan Frontend (Vue.js Vite)
echo "[2/2] Menjalankan Frontend Vue (http://localhost:5173)..."
cd "$(dirname "$0")/library-frontend" && npm run dev &

echo ""
echo "Aplikasi siap diakses di web browser:"
echo "👉 http://localhost:5173"
echo ""
echo "Tekan [Ctrl + C] untuk berhenti."
echo "=========================================="

wait
