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
        Schema::create('detail_transaksis', function (Blueprint $table) {
            $table->id('detail_id');
            $table->integer('harga');
            $table->integer('berat');

            $table->foreignId('sampah_id')
                ->constrained('kategori_sampahs','sampah_id')
                ->cascadeOnDelete();

            $table->foreignId('transaksi_id')
                ->constrained('transaksi_pengepuls','transaksi_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};
