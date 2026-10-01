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
        Schema::table('books', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('category');
            $table->string('file_format', 20)->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_format');
            $table->text('description')->nullable()->after('file_size');
            $table->integer('stock')->nullable()->default(null)->change();
            $table->string('shelf_location')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_format', 'file_size', 'description']);
            $table->integer('stock')->default(0)->nullable(false)->change();
            $table->string('shelf_location')->nullable(false)->change();
        });
    }
};
