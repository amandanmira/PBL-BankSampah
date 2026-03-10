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
        Schema::create('transaksi_pengepuls', function (Blueprint $table) {
            $table->id('transaksi_id');
            $table->enum('status', ['pending', 'proses', 'selesai', 'tolak']);
            $table->text('ket_status')->nullable();
            $table->integer('harga_total');
            $table->date('deadline');
            $table->integer('berat_total');

            $table->foreignId('pengepul_id')
                ->constrained('pengepuls','pengepul_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pengepuls');
    }
};
