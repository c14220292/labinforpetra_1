@extends('layouts.app')

@section('title', 'Asisten Lab Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2">MENU APLIKASI</h1>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <a href="#"
                class="group bg-gradient-to-br from-petra-orange to-orange-600 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-database text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">DATA PERLENGKAPAN</h2>
                </div>
            </a>

            <a href="#"
                class="group bg-gradient-to-br from-petra-blue to-blue-800 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-toolbox text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">PEMELIHARAAN</h2>
                </div>
            </a>

            <a href="#"
                class="group bg-gradient-to-br from-petra-orange to-orange-600 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-handshake text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">PEMINJAMAN</h2>
                </div>
            </a>

            <a href="#"
                class="group bg-gradient-to-br from-petra-blue to-blue-800 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-file-alt text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">LAPORAN</h2>
                </div>
            </a>
        </div>
    </div>
@endsection
