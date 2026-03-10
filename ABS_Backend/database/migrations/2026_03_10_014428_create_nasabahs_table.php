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
        Schema::create('nasabahs', function (Blueprint $table) {
            $table->id('nasabah_id');
            $table->string('username', 20)->unique();
            $table->string('email', 50)->unique();
            $table->string('password', 32);
            $table->string('nama_nasabah', 50);
            $table->text('alamat')->nullable();
            $table->text('gmap')->nullable();
            $table->string('no_telp', 16)->nullable();
            $table->enum('status', ['aktif', 'nonaktif']);
            $table->text('ket_status')->nullable();
            $table->string('no_rekening', 20)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nasabahs');
    }
};
