<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('id_pengguna');
            $table->string('nama_pengguna', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['kepalalab', 'asistenlab', 'admin'])->nullable();
            $table->datetime('last_login')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
