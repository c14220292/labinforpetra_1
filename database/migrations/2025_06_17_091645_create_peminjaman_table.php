<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id('id_peminjaman');
            $table->unsignedBigInteger('id_perlengkapan');
            $table->string('laboratorium', 20);
            $table->string('email_peminjaman', 100);
            $table->datetime('waktu_peminjaman');
            $table->datetime('waktu_pengembalian')->nullable();
            $table->enum('status', ['Dalam Proses', 'Selesai']);
            $table->timestamps();

            $table->foreign('id_perlengkapan')->references('id_perlengkapan')->on('perlengkapan')->onDelete('cascade');
            $table->foreign('laboratorium')->references('kode_lab')->on('laboratorium')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
