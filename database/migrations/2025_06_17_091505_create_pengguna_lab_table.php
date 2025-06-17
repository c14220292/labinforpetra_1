<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna_lab', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pengguna');
            $table->string('kode_lab', 20);
            $table->primary(['id_pengguna', 'kode_lab']);

            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->onDelete('cascade');
            $table->foreign('kode_lab')->references('kode_lab')->on('laboratorium')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_lab');
    }
};
