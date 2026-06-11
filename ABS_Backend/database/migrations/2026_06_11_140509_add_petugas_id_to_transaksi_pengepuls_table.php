<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('transaksi_pengepuls', function (Blueprint $table) {
            // Menambahkan kolom petugas_id setelah kolom pengepul_id
            $table->unsignedBigInteger('petugas_id')->nullable()->after('pengepul_id');
        });
    }

    public function down()
    {
        Schema::table('transaksi_pengepuls', function (Blueprint $table) {
            $table->dropColumn('petugas_id');
        });
    }
};
