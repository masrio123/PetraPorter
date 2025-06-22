<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Login</title>
    
    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Memuat Google Fonts: Inter -->
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    
    <!-- Memuat Library Ikon: Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /* Menggunakan font Inter sebagai default */
        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Mencegah scroll karena elemen background */
        }
        
        /* Animated Gradient Background */
        .gradient-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Custom Input Style for white background */
        .login-input {
            background-color: #F3F4F6; /* bg-gray-100 */
            border: 1px solid #D1D5DB; /* border-gray-300 */
            color: #111827; /* text-gray-900 */
            transition: all 0.2s ease-in-out;
        }

        .login-input::placeholder {
            color: #9CA3AF; /* text-gray-400 */
        }

        .login-input:focus {
            background-color: #FFFFFF;
            border-color: #ff7622;
            box-shadow: 0 0 0 2px rgba(255, 118, 34, 0.4);
            outline: none;
        }

        /* Custom Button Style */
        .btn-primary {
            background-color: #ff7622;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            background-color: #e6600d;
        }
    </style>
</head>
<body>
    <!-- Animated Gradient Background -->
    <div class="gradient-bg"></div>

    <!-- Container Utama -->
    <div class="flex flex-col items-center justify-center min-h-screen px-4">
        
        <!-- Kotak Login Putih -->
        <div class="w-full max-w-sm p-8 space-y-8 bg-white rounded-2xl shadow-xl">
            
            <!-- Header Formulir -->
            <div class="text-center">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Petra" class="w-32 mx-auto mb-5">
                <h1 class="text-3xl font-bold text-gray-900">ADMIN PORTAL</h1>
            </div>

            <!-- Formulir -->
            <form id="formAuthentication" action="{{ route('login.post') }}" method="POST" class="space-y-6">
                <!-- Token CSRF untuk keamanan Laravel -->
                @csrf

                <!-- Input User -->
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">User</label>
                    <input 
                        type="text" 
                        id="email" 
                        name="email"
                        placeholder="Masukan User Anda" 
                        autofocus
                        required
                        class="w-full px-4 py-3 rounded-lg login-input"
                    >
                </div>

                <!-- Input Sandi dengan Ikon -->
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Sandi</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Masukan Sandi Anda" 
                            required
                            class="w-full px-4 py-3 pr-10 rounded-lg login-input"
                        >
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="bx bx-hide text-gray-500 hover:text-gray-700 text-xl cursor-pointer"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Tombol Masuk -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="w-full px-4 py-3 font-semibold text-white rounded-lg btn-primary"
                    >
                        Masuk
                    </button>
                </div>
            </form>
        </div>

     
    </div>

    <script>
        // Script untuk toggle show/hide password
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                const passwordInput = document.getElementById('password');
                const icon = togglePassword.querySelector('i');

                togglePassword.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    icon.classList.toggle('bx-show', isPassword);
                    icon.classList.toggle('bx-hide', !isPassword);
                });
            }
        });
    </script>
</body>
</html>
