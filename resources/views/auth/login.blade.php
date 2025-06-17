<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Petra Informatics Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'petra-orange': '#f7941d',
                        'petra-blue': '#1d3c74',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="h-screen flex">
    <div class="flex-1 flex flex-col justify-center bg-gray-50">
        <div class="max-w-md mx-auto w-full px-8">
            <h1 class="text-4xl font-bold mb-2">Welcome to Petra<br>Informatics Laboratory</h1>
            <p class="text-gray-600 mb-8">Log in to access our lab properties</p>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
                    class="w-full px-4 py-3 bg-gray-300 border-none rounded text-base focus:outline-none focus:ring-2 focus:ring-petra-orange"
                    required>

                <input type="password" name="password" placeholder="Password"
                    class="w-full px-4 py-3 bg-gray-300 border-none rounded text-base focus:outline-none focus:ring-2 focus:ring-petra-orange"
                    required>

                <button type="submit"
                    class="w-full bg-petra-blue text-white py-3 rounded text-base font-medium hover:bg-blue-800 transition mt-6">
                    Log In
                </button>
            </form>

            <p class="text-sm mt-4">
                Don't have an account? You can register
                <a href="{{ route('register') }}" class="text-black underline hover:text-petra-orange">here</a>
            </p>
        </div>
    </div>

    <div class="flex-1 relative bg-cover bg-center"
        style="background-image: url('https://informatics.petra.ac.id/wp-content/uploads/2023/07/GSP_7832.jpg')">
        <div class="absolute top-5 right-5 flex items-center space-x-4">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/4/4d/UK_PETRA_LOGO.svg/1200px-UK_PETRA_LOGO.svg.png"
                alt="Petra Logo" class="h-10">
            <img src="https://petra.ac.id/img/logo-text.2e8a4502.png" alt="PCU Logo" class="h-10">
        </div>
    </div>
</body>

</html>
