<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan', function (Blueprint $table) {
            $table->id('id_pemeliharaan');
            $table->unsignedBigInteger('id_perlengkapan');
            $table->string('laboratorium', 20);
            $table->datetime('waktu_pemeliharaan');
            $table->datetime('waktu_pengembalian')->nullable();
            $table->text('deskripsi_pemeliharaan')->nullable();
            $table->enum('status', ['Selesai', 'Dalam Proses']);
            $table->timestamps();

            $table->foreign('id_perlengkapan')->references('id_perlengkapan')->on('perlengkapan')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('laboratorium')->references('kode_lab')->on('laboratorium')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan');
    }
};
