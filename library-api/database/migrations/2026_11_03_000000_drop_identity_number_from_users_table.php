<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NISN/NIP dihapus dari sistem: akun cukup email + kata sandi.
 * Lupa kata sandi ditangani pustakawan lewat panel Pengelolaan Akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identity_number']);
            $table->dropColumn('identity_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_number', 30)->nullable()->unique()->after('email');
        });
    }
};
