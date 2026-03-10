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
        Schema::create('pengepuls', function (Blueprint $table) {
            $table->id('pengepul_id');
            $table->string('nama', 50);
            $table->string('username', 20)->unique();
            $table->string('email', 50)->unique();
            $table->string('password', 32);
            $table->string('no_telp', 16);
            $table->string('nama_lembaga', 50);
            $table->text('alamat');
            $table->enum('status', ['pending', 'aktif', 'nonaktif']);
            $table->string('ket_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengepuls');
    }
};
