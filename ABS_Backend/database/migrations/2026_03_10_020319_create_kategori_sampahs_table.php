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
        Schema::create('kategori_sampahs', function (Blueprint $table) {
            $table->id('kategori_id');
            $table->string('nama', 50);
            $table->enum('satuan_berat', ['ton', 'kg', 'gram']);
            $table->integer('harga_beli')->default(0);
            $table->integer('harga_jual')->default(0);
            $table->decimal('diskon', 4, 4)->default(0);
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sampahs');
    }
};
