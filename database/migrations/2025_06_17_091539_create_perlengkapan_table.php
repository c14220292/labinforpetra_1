<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perlengkapan', function (Blueprint $table) {
            $table->id('id_perlengkapan');
            $table->string('kode_seri', 50);
            $table->string('nama_perlengkapan', 100);
            $table->enum('jenis_perlengkapan', [
                'Main Hardware',
                'Cables & Connector',
                'Networking Device',
                'Fasilitas Ruangan'
            ]);
            $table->integer('set_komputer')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->string('laboratorium', 20)->nullable();
            $table->enum('kondisi', ['Tersedia', 'Pemeliharaan', 'Peminjaman']);
            $table->enum('status', ['Bisa Dipakai', 'Tidak Bisa Dipakai']);
            $table->datetime('waktu_masuk');
            $table->timestamps();

            $table->foreign('laboratorium')->references('kode_lab')->on('laboratorium')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perlengkapan');
    }
};
