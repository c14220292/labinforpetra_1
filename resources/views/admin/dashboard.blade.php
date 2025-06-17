@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2">MENU APLIKASI</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-petra-orange to-orange-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-users text-3xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Pengguna</h3>
                        <p class="text-2xl font-bold">{{ $stats['total_pengguna'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-petra-blue to-blue-800 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-building text-3xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Lab</h3>
                        <p class="text-2xl font-bold">{{ $stats['total_laboratorium'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-tools text-3xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Perlengkapan</h3>
                        <p class="text-2xl font-bold">{{ $stats['total_perlengkapan'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-3xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Tersedia</h3>
                        <p class="text-2xl font-bold">{{ $stats['perlengkapan_tersedia'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <a href="{{ route('admin.pengguna.index') }}"
                class="group bg-gradient-to-br from-petra-orange to-orange-600 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-users text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">MANAJEMEN PENGGUNA</h2>
                </div>
            </a>

            <a href="{{ route('admin.laboratorium.index') }}"
                class="group bg-gradient-to-br from-petra-blue to-blue-800 text-white p-8 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                <div class="text-center">
                    <i class="fas fa-toolbox text-6xl mb-4 group-hover:scale-110 transition duration-300"></i>
                    <h2 class="text-xl font-bold">MANAJEMEN LABORATORIUM</h2>
                </div>
            </a>
        </div>
    </div>
@endsection
