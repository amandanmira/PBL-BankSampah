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
        Schema::create('penjemputans', function (Blueprint $table) {
            $table->id('penjemputan_id');
            $table->text('deskripsi')->nullable();
            $table->text('alamat');
            $table->text('foto')->nullable();
            $table->dateTime('jadwal')->nullable();
            $table->enum('status', ['pending', 'proses', 'selesai', 'tolak']);
            $table->text('ket_status')->nullable();

            $table->foreignId('tukang_id')
                ->nullable()
                ->constrained('tukangs','tukang_id')
                ->nullOnDelete();

            $table->foreignId('nasabah_id')
                ->constrained('nasabahs','nasabah_id')
                ->cascadeOnDelete();

            $table->foreignId('petugas_id')
                ->nullable()
                ->constrained('petugas','petugas_id')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjemputans');
    }
};
