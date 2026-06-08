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
        Schema::table('penarikans', function (Blueprint $table) {
            $table->decimal('saldo_sebelum', 15, 2)->nullable()->after('jumlah');
            $table->decimal('saldo_sesudah', 15, 2)->nullable()->after('saldo_sebelum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penarikans', function (Blueprint $table) {
            $table->dropColumn(['saldo_sebelum', 'saldo_sesudah']);
        });
    }
};
