<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter enum column in MySQL using raw SQL for maximum compatibility
        DB::statement("ALTER TABLE transaksi_pengepuls MODIFY COLUMN status ENUM('pending', 'proses', 'siap_diambil', 'selesai', 'tolak', 'batal') NOT NULL;");

        // Add status column to detail_transaksis table
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->enum('status', ['pending', 'setuju', 'tolak'])->default('pending')->after('sampah_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert detail_transaksis status column
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Revert enum column in MySQL
        DB::statement("ALTER TABLE transaksi_pengepuls MODIFY COLUMN status ENUM('pending', 'proses', 'selesai', 'tolak') NOT NULL;");
    }
};
