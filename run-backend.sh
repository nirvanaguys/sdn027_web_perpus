#!/bin/bash
cd "$(dirname "$0")/library-api"
echo "=== Menjalankan Laravel API (Backend) di http://127.0.0.1:8000 ==="
php artisan serve --host=127.0.0.1 --port=8000
