@extends('layouts.app')

@section('title', 'Edit Laboratorium')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.laboratorium.index') }}"
                class="bg-gray-400 text-black px-4 py-2 rounded font-semibold hover:bg-gray-500 transition">
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2 mb-6">Edit Laboratorium</h2>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.laboratorium.update', $laboratorium) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menambahkan laboratorium ini?')">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Lab</label>
                        <input type="text" name="kode_lab" value="{{ old('kode_lab', $laboratorium->kode_lab) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lab</label>
                        <input type="text" name="nama_lab" value="{{ old('nama_lab', $laboratorium->nama_lab) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gedung</label>
                        <select name="gedung"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                            <option value="P" {{ old('gedung', $laboratorium->gedung) == 'P' ? 'selected' : '' }}>P
                            </option>
                            <option value="T" {{ old('gedung', $laboratorium->gedung) == 'T' ? 'selected' : '' }}>T
                            </option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-petra-orange text-white py-3 rounded font-semibold hover:bg-orange-600 transition">
                        Perbarui Lab
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
