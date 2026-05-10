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
        Schema::table('transaksi_nasabahs', function (Blueprint $table) {
            $table->decimal('saldo_sebelum', 15, 2)->default(0)->after('ket_status');
            $table->decimal('total_harga', 15, 2)->default(0)->after('saldo_sebelum');
            $table->decimal('saldo_sesudah', 15, 2)->default(0)->after('total_harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_nasabahs', function (Blueprint $table) {
            $table->dropColumn(['saldo_sebelum', 'total_harga', 'saldo_sesudah']);
        });
    }
};
