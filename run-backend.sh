#!/bin/bash
cd "$(dirname "$0")/library-api"
echo "=== Menjalankan Laravel API (Backend) di http://localhost:8000 ==="
php artisan serve --host=0.0.0.0 --port=8000
