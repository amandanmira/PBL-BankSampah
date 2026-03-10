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
        Schema::create('konfigurasi_webs', function (Blueprint $table) {
            $table->id('config_id');
            $table->text('logo');
            $table->integer('lama_deadline');

            $table->foreignId('manager_id')
                ->constrained('managers','manager_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_webs');
    }
};
