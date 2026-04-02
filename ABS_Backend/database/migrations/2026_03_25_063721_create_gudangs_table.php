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
        Schema::create('gudangs', function (Blueprint $table) {
            $table->id("gudang_id");
            $table->text('alamat');
            $table->timestamps();
        });

        Schema::table('tukangs', function (Blueprint $table) {
            $table->foreignId('gudang_id')
                ->constrained('gudangs','gudang_id')
                ->cascadeOnDelete();
        });

        Schema::table('penjemputans', function (Blueprint $table) {
            $table->foreignId('gudang_id')
                ->constrained('gudangs','gudang_id')
                ->cascadeOnDelete();
        });

        Schema::table('petugas', function (Blueprint $table) {
            $table->foreignId('gudang_id')
                ->constrained('gudangs','gudang_id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petugas', function (Blueprint $table) {
            $table->dropForeign(['gudang_id']);
            $table->dropColumn('gudang_id');
        });

        Schema::table('penjemputans', function (Blueprint $table) {
            $table->dropForeign(['gudang_id']);
            $table->dropColumn('gudang_id');
        });

        Schema::table('tukangs', function (Blueprint $table) {
            $table->dropForeign(['gudang_id']);
            $table->dropColumn('gudang_id');
        });

        Schema::dropIfExists('gudangs');
    }
};
