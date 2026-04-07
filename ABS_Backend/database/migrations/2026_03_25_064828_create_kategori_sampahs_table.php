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
            $table->id("kategori_id");
            $table->string('nama', 50);
            $table->timestamps();
        });

        Schema::table('item_sampahs', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->constrained('kategori_sampahs','kategori_id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_sampahs', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });

        Schema::dropIfExists('kategori_sampahs');
    }
};
