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
        Schema::create('transaksi_nasabahs', function (Blueprint $table) {
            $table->id('transaksi_id');
            $table->enum('tipe_transaksi', ['dijemput', 'antar_sendiri']);
            $table->integer('berat_total');
            $table->integer('harga_total');
            $table->dateTime('tanggal');
            $table->enum('status', ['pending', 'proses', 'selesai', 'tolak']);
            $table->text('ket_status')->nullable();

            $table->foreignId('nasabah_id')
                ->constrained('nasabahs','nasabah_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_nasabahs');
    }
};
