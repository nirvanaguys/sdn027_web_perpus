<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom cover_image pada tabel books jika belum ada
        if (!Schema::hasColumn('books', 'cover_image')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('cover_image')->nullable()->after('description');
            });
        }

        // 2. Tabel Reading List (Ebook yang disimpan per akun)
        if (!Schema::hasTable('reading_lists')) {
            Schema::create('reading_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'book_id']);
            });
        }

        // 3. Tabel Riwayat Baca (Mencatat ebook yang pernah dibuka pengguna)
        if (!Schema::hasTable('reading_histories')) {
            Schema::create('reading_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->timestamp('last_read_at')->useCurrent();
                $table->timestamps();

                $table->unique(['user_id', 'book_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_histories');
        Schema::dropIfExists('reading_lists');

        if (Schema::hasColumn('books', 'cover_image')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('cover_image');
            });
        }
    }
};
