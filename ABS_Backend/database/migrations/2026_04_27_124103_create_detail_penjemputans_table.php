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
        Schema::create('detail_penjemputans', function (Blueprint $table) {
            $table->id('detail_id');

            $table->foreignId('penjemputan_id')
                ->constrained('penjemputans','penjemputan_id')
                ->cascadeOnDelete();

                $table->foreignId('sampah_id')
                ->constrained('sampahs','sampah_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::table('penjemputans', function (Blueprint $table) {
            $table->string('estimasi_berat', 25);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_sampahs', function (Blueprint $table) {
            $table->dropColumn('estimasi_berat');
        });

        Schema::dropIfExists('detail_penjemputans');
    }
};
