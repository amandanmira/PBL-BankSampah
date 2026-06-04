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
        Schema::table('penjemputans', function (Blueprint $table) {
            $table->string('rentang_hari', 100)->nullable()->after('jadwal');
            $table->string('rentan_waktu', 50)->nullable()->after('rentang_hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjemputans', function (Blueprint $table) {
            $table->dropColumn(['rentang_hari', 'rentan_waktu']);
        });
    }
};
