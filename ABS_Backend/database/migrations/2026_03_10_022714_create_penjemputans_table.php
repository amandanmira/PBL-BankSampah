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
            $table->dateTime('jadwal');
            $table->enum('status', ['pending', 'proses', 'selesai', 'tolak']);
            $table->text('ket_status')->nullable();

            $table->foreignId('tukang_id')
                ->constrained('tukangs','tukang_id')
                ->cascadeOnDelete();

            $table->foreignId('nasabah_id')
                ->constrained('nasabahs','nasabah_id')
                ->cascadeOnDelete();

            $table->foreignId('petugas_id')
                ->constrained('petugas','petugas_id')
                ->cascadeOnDelete();

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
