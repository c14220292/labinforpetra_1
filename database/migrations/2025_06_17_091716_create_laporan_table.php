<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->enum('jenis_laporan', [
                'Tambah Perlengkapan',
                'Hapus Perlengkapan',
                'Edit Perlengkapan',
                'Peminjaman',
                'Pengembalian Peminjaman',
                'Pemeliharaan',
                'Pengembalian Pemeliharaan'
            ]);
            $table->string('user', 100);
            $table->text('keterangan')->nullable();
            $table->string('laboratorium', 100)->nullable();
            $table->datetime('waktu_laporan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
