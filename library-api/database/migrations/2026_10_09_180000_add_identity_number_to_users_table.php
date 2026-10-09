<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomor identitas unik untuk verifikasi mandiri "lupa kata sandi"
     * tanpa email/SMTP: NISN untuk siswa, NIP untuk guru/karyawan.
     * Nullable agar akun lama yang belum punya tetap bisa login;
     * admin melengkapi via panel Pengelolaan Akun.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_number', 30)->nullable()->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('identity_number');
        });
    }
};
