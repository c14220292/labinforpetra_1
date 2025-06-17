<header class="bg-white border-b-4 border-petra-orange shadow-sm">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/4/4d/UK_PETRA_LOGO.svg/1200px-UK_PETRA_LOGO.svg.png"
                alt="Petra Logo" class="h-10">
            <img src="https://petra.ac.id/img/logo-text.2e8a4502.png" alt="PCU Logo" class="h-10">
        </div>

        @auth
            <div class="relative">
                <button onclick="toggleUserPopup()"
                    class="flex items-center space-x-2 bg-gray-100 border border-gray-300 rounded px-3 py-2 hover:bg-gray-200 transition">
                    <span class="text-sm">{{ auth()->user()->nama_pengguna }}</span>
                    <i class="fas fa-user-circle text-gray-600"></i>
                </button>

                <div id="userPopup"
                    class="hidden absolute right-0 top-12 bg-white border border-gray-300 rounded-lg shadow-lg p-4 w-72 z-50">
                    <p class="text-sm mb-2"><strong>Nama:</strong> {{ auth()->user()->nama_pengguna }}</p>
                    <p class="text-sm mb-2"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    <p class="text-sm mb-4"><strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}</p>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin logout?')"
                            class="bg-petra-orange text-white px-3 py-2 rounded text-sm hover:bg-orange-600 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>

@push('scripts')
    <script>
        function toggleUserPopup() {
            const popup = document.getElementById('userPopup');
            popup.classList.toggle('hidden');
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('userPopup');
            const button = event.target.closest('button');

            if (!button || button.getAttribute('onclick') !== 'toggleUserPopup()') {
                popup.classList.add('hidden');
            }
        });
    </script>
@endpush
