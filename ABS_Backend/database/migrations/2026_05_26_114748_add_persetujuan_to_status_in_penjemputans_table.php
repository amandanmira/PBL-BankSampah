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
        Schema::table('penjemputans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'menunggu_persetujuan', 'jadwal_ditolak', 'proses', 'dijemput', 'selesai', 'tolak', 'batal', 'perlu_input'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjemputans', function (Blueprint $table) {
            $table->enum('status', ['pending', 'proses', 'dijemput', 'selesai', 'tolak', 'batal', 'perlu_input'])->change();
        });
    }
};
