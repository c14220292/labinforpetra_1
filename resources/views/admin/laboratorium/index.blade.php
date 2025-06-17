@extends('layouts.app')

@section('title', 'Manajemen Laboratorium')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-400 text-black px-4 py-2 rounded font-semibold hover:bg-gray-500 transition">
                Kembali
            </a>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4 mb-6">
            <button onclick="openModal('add')"
                class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition flex items-center">
                <i class="fas fa-folder-plus mr-2"></i> Tambah Lab
            </button>
            <button onclick="openModal('delete')"
                class="bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition flex items-center">
                <i class="fas fa-folder-minus mr-2"></i> Hapus Lab
            </button>
            <button onclick="openModal('edit')"
                class="bg-petra-blue text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-800 transition flex items-center">
                <i class="fas fa-edit mr-2"></i> Edit Lab
            </button>
        </div>

        <h2 class="text-2xl font-bold text-petra-blue border-b-4 border-petra-orange pb-2">PILIH LABORATORIUM</h2>

        <!-- Laboratory Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($laboratoriums as $lab)
                <div
                    class="bg-gradient-to-br from-petra-orange to-orange-600 text-white p-6 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300 cursor-pointer">
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2">{{ $lab->kode_lab }}</div>
                        <div class="text-sm">{{ $lab->nama_lab }}</div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-8">
                    Tidak ada data laboratorium.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tambah Laboratorium Baru</h3>
                <button onclick="closeModal('add')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.laboratorium.store') }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menambahkan laboratorium ini?')">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Lab</label>
                        <input type="text" name="kode_lab"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lab</label>
                        <input type="text" name="nama_lab"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gedung</label>
                        <select name="gedung"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange"
                            required>
                            <option value="">-- Pilih Gedung --</option>
                            <option value="P">P</option>
                            <option value="T">T</option>
                        </select>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="closeModal('add')"
                            class="flex-1 bg-gray-300 text-black py-2 rounded hover:bg-gray-400 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 bg-petra-orange text-white py-2 rounded hover:bg-orange-600 transition">
                            Tambah Lab
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Pilih Laboratorium untuk Dihapus</h3>
                <button onclick="closeModal('delete')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium:</label>
                    <select id="deleteLabSelect"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange">
                        <option value="">-- Pilih Laboratorium --</option>
                        @foreach ($laboratoriums as $lab)
                            <option value="{{ $lab->id_lab }}">{{ $lab->kode_lab }} - {{ $lab->nama_lab }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeModal('delete')"
                        class="flex-1 bg-petra-blue text-white py-2 rounded hover:bg-blue-800 transition">
                        Tutup
                    </button>
                    <button type="button" onclick="confirmDeleteLab()"
                        class="flex-1 bg-petra-orange text-white py-2 rounded hover:bg-orange-600 transition">
                        Hapus Lab
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Pilih Laboratorium untuk Diedit</h3>
                <button onclick="closeModal('edit')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium:</label>
                    <select id="editLabSelect"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-petra-orange">
                        <option value="">-- Pilih Laboratorium --</option>
                        @foreach ($laboratoriums as $lab)
                            <option value="{{ $lab->id_lab }}">{{ $lab->kode_lab }} - {{ $lab->nama_lab }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeModal('edit')"
                        class="flex-1 bg-petra-blue text-white py-2 rounded hover:bg-blue-800 transition">
                        Tutup
                    </button>
                    <button type="button" onclick="editLab()"
                        class="flex-1 bg-petra-orange text-white py-2 rounded hover:bg-orange-600 transition">
                        Edit Lab
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openModal(type) {
                document.getElementById(type + 'Modal').classList.remove('hidden');
            }

            function closeModal(type) {
                document.getElementById(type + 'Modal').classList.add('hidden');
            }

            function confirmDeleteLab() {
                const select = document.getElementById('deleteLabSelect');
                const selectedId = select.value;
                if (selectedId) {
                    if (confirm('Apakah Anda yakin ingin menghapus laboratorium ini?')) {
                        // Create form and submit
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/admin/laboratorium/${selectedId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        document.body.appendChild(form);
                        form.submit();
                    }
                } else {
                    alert('Silakan pilih laboratorium untuk dihapus.');
                }
            }

            function editLab() {
                const select = document.getElementById('editLabSelect');
                const selectedId = select.value;
                if (selectedId) {
                    window.location.href = `/admin/laboratorium/${selectedId}/edit`;
                } else {
                    alert('Silakan pilih laboratorium untuk diedit.');
                }
            }
        </script>
    @endpush
@endsection
