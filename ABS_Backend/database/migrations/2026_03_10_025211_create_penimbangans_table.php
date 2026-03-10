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
        Schema::create('penimbangans', function (Blueprint $table) {
            $table->id('penimbangan_id');
            $table->integer('berat_timbang');
            $table->text('foto')->nullable();

            $table->foreignId('nasabah_id')
                ->constrained('nasabahs','nasabah_id')
                ->cascadeOnDelete();

            $table->foreignId('transaksi_id')
                ->constrained('transaksi_nasabahs','transaksi_id')
                ->cascadeOnDelete();

            $table->foreignId('kategori_id')
                ->constrained('kategori_sampahs','kategori_id')
                ->cascadeOnDelete();

            $table->foreignId('tukang_id')
                ->constrained('tukangs','tukang_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penimbangans');
    }
};
