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
        Schema::create('konfigurasi_webs', function (Blueprint $table) {
            $table->id('config_id');
            $table->text('logo')->nullable();
            $table->text('quote')->nullable();
            $table->text('instagram')->nullable();
            $table->text('facebook')->nullable();
            $table->text('linkedin')->nullable();
            $table->text('youtube')->nullable();
            $table->string('no_telp', 16)->nullable();
            $table->string('email', 50)->nullable();
            $table->integer('lama_deadline')->nullable();
            $table->text('alamat')->nullable();
            $table->text('tentang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_webs');
    }
};
