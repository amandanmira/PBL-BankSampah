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
        Schema::table('kategori_sampahs', function (Blueprint $table) {
            // Menambahkan aturan 'unique' pada kolom nama yang sudah ada
            $table->unique('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_sampahs', function (Blueprint $table) {
            // Menghapus aturan 'unique' jika Anda melakukan rollback
            $table->dropUnique(['nama']);
        });
    }
};