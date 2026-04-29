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
        Schema::table('konfigurasi_webs', function (Blueprint $table) {
            $table->text('hero_quote_top')->nullable();
            $table->text('hero_quote_bottom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfigurasi_webs', function (Blueprint $table) {
            $table->dropColumn(['hero_quote_top', 'hero_quote_bottom']);
        });
    }
};
