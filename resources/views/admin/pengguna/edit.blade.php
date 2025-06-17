@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.pengguna.index') }}"
                class="bg-gray-400 text-black px-4 py-2 rounded font-semibold hover:bg-gray-500 transition">
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2 mb-6">Edit Data Pengguna</h2>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.pengguna.update', $pengguna) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin mengubah data pengguna ini?')">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_pengguna"
                            value="{{ old('nama_pengguna', $pengguna->nama_pengguna) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $pengguna->email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password (kosongkan jika tidak ingin
                            mengubah)</label>
                        <input type="password" name="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role', $pengguna->role) == 'admin' ? 'selected' : '' }}>Admin
                            </option>
                            <option value="kepalalab" {{ old('role', $pengguna->role) == 'kepalalab' ? 'selected' : '' }}>
                                Kepala Lab</option>
                            <option value="asistenlab" {{ old('role', $pengguna->role) == 'asistenlab' ? 'selected' : '' }}>
                                Asisten Lab</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Akses Laboratorium</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($laboratoriums as $lab)
                                <label
                                    class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 
                                      {{ $loop->index % 2 == 0 ? 'bg-gradient-to-r from-petra-orange to-orange-500 text-white' : 'bg-gradient-to-r from-petra-blue to-blue-600 text-white' }}
                                      {{ in_array($lab->kode_lab, old('lab_akses', $selectedLabs)) ? 'ring-2 ring-white' : '' }}">
                                    <input type="checkbox" name="lab_akses[]" value="{{ $lab->kode_lab }}"
                                        {{ in_array($lab->kode_lab, old('lab_akses', $selectedLabs)) ? 'checked' : '' }}
                                        class="rounded">
                                    <div class="text-center">
                                        <div class="font-bold">{{ $lab->kode_lab }}</div>
                                        <div class="text-sm">{{ $lab->nama_lab }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex space-x-4 pt-4">
                        <a href="{{ route('admin.pengguna.index') }}"
                            class="flex-1 bg-gray-300 text-black text-center py-3 rounded font-semibold hover:bg-gray-400 transition">
                            Kembali
                        </a>
                        <button type="submit"
                            class="flex-1 bg-petra-blue text-white py-3 rounded font-semibold hover:bg-blue-800 transition">
                            Simpan Pengguna
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
