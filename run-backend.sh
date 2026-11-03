#!/bin/bash
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
API_DIR="$PROJECT_DIR/library-api"
ROUTER="$API_DIR/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"

echo "=== Menjalankan Laravel API (Backend) di http://localhost:8001 ==="
cd "$API_DIR/public" || exit 1
exec php -d expose_php=Off -d upload_max_filesize=50M -d post_max_size=60M -S 0.0.0.0:8001 "$ROUTER"
