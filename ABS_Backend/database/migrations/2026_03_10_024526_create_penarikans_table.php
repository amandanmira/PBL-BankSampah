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
        Schema::create('penarikans', function (Blueprint $table) {
            $table->id('penarikan_id');
            $table->integer('jumlah');
            $table->enum('status', ['pending', 'proses', 'selesai', 'tolak', 'batal']);
            $table->text('ket_status')->nullable();
            $table->text('bukti_tf')->nullable();
            $table->string('no_rekening', 20)->nullable();
            $table->enum('nama_bank', ['BRI', 'BCA', 'DANA', 'Bank Jago', 'Bank Mandiri', 'BNI', 'OVO', 'GoPay', 'LinkAja', 'ShopeePay', 'CIMB Niaga', 'Bank Permata', 'BSI'])->nullable();
            $table->string('nama_rek', 50)->nullable();

            $table->foreignId('nasabah_id')
                ->constrained('nasabahs','nasabah_id')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penarikans');
    }
};
