@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-400 text-black px-4 py-2 rounded font-semibold hover:bg-gray-500 transition">
                Kembali
            </a>
            <a href="{{ route('admin.pengguna.create') }}"
                class="bg-green-500 text-white px-4 py-2 rounded font-semibold hover:bg-green-600 transition">
                Tambah Pengguna
            </a>
        </div>

        <h2 class="text-2xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2">Daftar Pengguna</h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-petra-orange text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Akses Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($penggunas as $pengguna)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pengguna->id_pengguna }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pengguna->nama_pengguna }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pengguna->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($pengguna->role) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $pengguna->last_login ? $pengguna->last_login->format('d-m-Y H:i') : 'Belum pernah login' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if ($pengguna->laboratoriums->count() > 0)
                                    @foreach ($pengguna->laboratoriums as $lab)
                                        <span
                                            class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                            {{ $lab->kode_lab }} - {{ $lab->nama_lab }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-500">Tidak ada akses lab</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('admin.pengguna.edit', $pengguna) }}"
                                    class="bg-yellow-400 text-black px-3 py-1 rounded hover:bg-yellow-500 transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.pengguna.destroy', $pengguna) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $pengguna->nama_pengguna }}?')"
                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
