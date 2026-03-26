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
        Schema::create('sampahs', function (Blueprint $table) {
            $table->id("sampah_id");
            $table->integer('stok')->default(0);

            $table->foreignId('kategori_id')
                ->constrained('kategori_sampahs','kategori_id')
                ->cascadeOnDelete();

            $table->foreignId('gudang_id')
                ->constrained('gudangs','gudang_id')
                ->cascadeOnDelete();

            $table->foreignId('detail_id')
                ->constrained('detail_transaksis','detail_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::table('penimbangans', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');

            $table->foreignId('sampah_id')
                ->constrained('sampahs','sampah_id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penimbangans', function (Blueprint $table) {
            $table->dropForeign(['sampah_id']);
            $table->dropColumn('sampah_id');

            $table->foreignId('kategori_id')
                ->constrained('kategori_sampahs','kategori_id')
                ->cascadeOnDelete();
        });

        Schema::dropIfExists('sampahs');
    }
};
