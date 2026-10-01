#!/bin/bash

# Tangani Ctrl+C agar mematikan kedua proses anak
trap 'kill $(jobs -p) 2>/dev/null' EXIT INT TERM

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
API_ROUTER="$ROOT_DIR/library-api/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"

echo "=========================================="
echo "  Memulai Sistem Manajemen Perpustakaan"
echo "=========================================="

# 1. Jalankan Backend (Laravel API)
echo "[1/2] Menjalankan Backend Laravel API (http://localhost:8000)..."
(cd "$ROOT_DIR/library-api/public" && exec php -d upload_max_filesize=50M -d post_max_size=60M -S 0.0.0.0:8000 "$API_ROUTER") &

# 2. Jalankan Frontend (Vue.js Vite)
echo "[2/2] Menjalankan Frontend Vue (http://localhost:5173)..."
(cd "$ROOT_DIR/library-frontend" && npm run dev) &

echo ""
echo "Aplikasi siap diakses di web browser:"
echo "http://localhost:5173"
echo ""
echo "Tekan [Ctrl + C] untuk berhenti."
echo "=========================================="

wait
