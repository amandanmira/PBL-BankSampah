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
        Schema::table('sampahs', function (Blueprint $table) {
            $table->decimal('stok', 10, 2)->default(0.00)->change();
        });

        Schema::table('penimbangans', function (Blueprint $table) {
            $table->decimal('berat_timbang', 10, 2)->change();
        });

        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->decimal('berat', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->integer('berat')->change();
        });

        Schema::table('penimbangans', function (Blueprint $table) {
            $table->integer('berat_timbang')->change();
        });

        Schema::table('sampahs', function (Blueprint $table) {
            $table->integer('stok')->default(0)->change();
        });
    }
};
