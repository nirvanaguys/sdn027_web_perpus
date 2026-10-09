<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jenjang kelas buku (SD kelas 1-6 + Umum) agar guru mudah
     * memfilter buku pelajaran per kelas dari katalog.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('grade_level', 20)->default('umum')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('grade_level');
        });
    }
};
