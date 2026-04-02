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
        Schema::create('jenis_sampahs', function (Blueprint $table) {
            $table->id("jenis_id");
            $table->string('nama', 50);
            $table->integer('stok_jenis')->default(0);
            $table->timestamps();
        });

        Schema::table('kategori_sampahs', function (Blueprint $table) {
            $table->foreignId('jenis_id')
                ->constrained('jenis_sampahs','jenis_id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_sampahs', function (Blueprint $table) {
            $table->dropForeign(['jenis_id']);
            $table->dropColumn('jenis_id');
        });

        Schema::dropIfExists('jenis_sampahs');
    }
};
