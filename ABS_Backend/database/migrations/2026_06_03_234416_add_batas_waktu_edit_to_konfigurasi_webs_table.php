<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konfigurasi_webs', function (Blueprint $table) {
            // Memberikan nilai default 12 jam
            $table->integer('batas_waktu_edit')->default(12)->after('lama_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('konfigurasi_webs', function (Blueprint $table) {
            $table->dropColumn('batas_waktu_edit');
        });
    }
};