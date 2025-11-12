<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Lexicon') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-white shadow-md sticky top-0 z-20" x-data="{ open: false, userMenu: false }">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center relative">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="h-8 w-8 rounded-full object-cover">
                <span class="font-bold text-indigo-600 text-lg">{{ config('app.name', 'Lexicon') }}</span>
            </a>

            {{-- Hamburger (Mobile) --}}
            <div class="md:hidden">
                <button @click="open = !open"
                    class="focus:outline-none text-indigo-600 transition-transform duration-300"
                    :class="open ? 'rotate-90' : ''">
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="open" x-transition
                class="absolute right-0 mt-3 w-48 bg-gray-800 text-white rounded-xl shadow-xl overflow-hidden z-30 md:hidden">
                <div class="flex flex-col divide-y divide-gray-700">
                    <a href="{{ route('home') }}" class="px-4 py-2 hover:bg-gray-700 transition">Home</a>
                    <a href="{{ route('books.index') }}" class="px-4 py-2 hover:bg-gray-700 transition">Books</a>
                    <a href="{{ route('about') }}" class="px-4 py-2 hover:bg-gray-700 transition">About</a>
                    <a href="{{ route('contact') }}" class="px-4 py-2 hover:bg-gray-700 transition">Contact</a>

                    @auth
                        <a href="{{ auth()->user()->role === 'member' ? route('user.dashboard') : route('admin.dashboard') }}"
                            class="px-4 py-2 hover:bg-gray-700 transition">Dashboard</a>
                        <span class="px-4 py-2 text-gray-200 font-medium">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 w-full text-left text-red-400 hover:text-red-500 hover:bg-gray-700 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 hover:bg-gray-700 transition">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 hover:bg-gray-700 transition">Register</a>
                    @endauth
                </div>
            </div>

            {{-- Desktop Links --}}
            <div class="hidden md:flex items-center space-x-5">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Home</a>
                <a href="{{ route('books.index') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Books</a>
                <a href="{{ route('about') }}" class="text-gray-700 hover:text-indigo-600 font-medium">About</a>
                <a href="{{ route('contact') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Contact</a>

                {{-- User Icon + Name Dropdown --}}
                <div class="relative flex items-center space-x-2" @mouseenter="userMenu = true"
                    @mouseleave="userMenu = false">
                    @auth
                        {{-- Username --}}
                        <div class="text-sm text-green-500 font-medium">{{ auth()->user()->name }}</div>

                        {{-- User Icon --}}
                        <button @click="userMenu = !userMenu"
                            class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-200 hover:bg-gray-300 transition shadow-sm">
                            <i class="fa fa-user text-gray-700"></i>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="userMenu" x-transition
                            class="absolute right-0 mt-3 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
                            <a href="{{ auth()->user()->role === 'member' ? route('user.dashboard') : route('admin.dashboard') }}"
                                class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-gauge-high mr-2 text-indigo-500"></i> Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center w-full text-left px-4 py-2 text-red-500 hover:bg-gray-100">
                                    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    @else
                        {{-- User Icon --}}
                        <button @click="userMenu = !userMenu"
                            class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-200 hover:bg-gray-300 transition shadow-sm">
                            <i class="fa fa-user text-gray-700"></i>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="userMenu" x-transition
                            class="absolute right-0 mt-3 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
                            <div class="flex flex-col space-y-2 px-3 py-2">
                                <a href="{{ route('login') }}"
                                    class="flex items-center justify-center h-10 w-full rounded-full bg-gray-200 hover:bg-gray-300 transition">
                                    <i class="fas fa-sign-in-alt mr-2 text-gray-700"></i> Login
                                </a>
                                <a href="{{ route('register') }}"
                                    class="flex items-center justify-center h-10 w-full rounded-full bg-gray-200 hover:bg-gray-300 transition">
                                    <i class="fas fa-user-plus mr-2 text-gray-700"></i> Register
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-grow py-12 px-4">
        <div class="w-full max-w-6xl mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-black text-gray-200 mt-auto overflow-hidden">
        <div class="container mx-auto px-4 py-10 grid md:grid-cols-3 gap-8">
            {{-- Left: Logo and Tagline --}}
            <div class="text-center md:text-left" data-aos="fade-up" data-aos-duration="1000">
                <a href="{{ route('home') }}"
                    class="flex items-center justify-center md:justify-start mb-4 group transition-transform duration-500 hover:scale-105">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Lexicon Logo"
                        class="rounded-full w-20 h-20 md:w-24 md:h-24 shadow-[0_0_20px_rgba(255,112,77,0.8)] group-hover:shadow-[0_0_30px_rgba(255,69,0,0.8)] transition-all duration-500">
                    <span
                        class="ml-3 text-xl md:text-2xl font-bold text-[#FF9966] group-hover:text-[#FF4500] transition-colors duration-500">Lexicon</span>
                </a>
                <p class="text-[#FF9966] opacity-80 animate-pulse-slow">YOUR GATEWAY TO ENDLESS LEARNING</p>
            </div>

            {{-- Center: Address & Contact --}}
            <div class="text-center md:text-left" data-aos="fade-up" data-aos-duration="1200">
                <h5 class="text-[#FF704D] font-semibold mb-2">Address</h5>
                <p>123 Lexicon Library St, Knowledge City</p>

                <h5 class="text-[#FF704D] font-semibold mt-4 mb-2">Contact</h5>
                <div class="flex flex-col space-y-1">
                    <a href="tel:+31123456789" class="hover:text-[#FF4500] transition">+31 12 345 6789</a>
                    <a href="mailto:info@lexicon.com" class="hover:text-[#FF4500] transition">info@lexicon.com</a>
                    <a href="mailto:info@lexicon.com" class="hover:text-[#FF4500] transition">helpline@lexicon.com</a>
                </div>
            </div>

            {{-- Right: Quick Links & Social --}}
            <div class="text-center md:text-left" data-aos="fade-up" data-aos-duration="1400">
                <h5 class="text-[#FF704D] font-semibold mb-2">Quick Links</h5>
                <div class="flex flex-col space-y-1 mb-4">
                    <a href="{{ route('home') }}" class="hover:text-[#FF4500] transition">Home</a>
                    <a href="{{ route('books.index') }}" class="hover:text-[#FF4500] transition">Books</a>
                    <a href="{{ route('about') }}" class="hover:text-[#FF4500] transition">About</a>
                    <a href="{{ route('contact') }}" class="hover:text-[#FF4500] transition">Contact</a>
                </div>

                <div class="flex justify-center md:justify-start space-x-4 mt-2 text-xl">
                    <a href="https://facebook.com" target="_blank"
                        class="hover:text-[#FF4500] transform transition duration-300 hover:scale-110"><i
                            class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com" target="_blank"
                        class="hover:text-[#FF4500] transform transition duration-300 hover:scale-110"><i
                            class="fab fa-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank"
                        class="hover:text-[#FF4500] transform transition duration-300 hover:scale-110"><i
                            class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>

        <hr class="border-gray-700 my-4">

        <div class="text-center text-gray-400 text-sm pb-4 space-y-2" data-aos="fade-up" data-aos-duration="1600">
            <div class="space-x-2">
                <a href="#privacy" class="hover:text-[#FF4500] transition">Privacy Policy</a> |
                <a href="#terms" class="hover:text-[#FF4500] transition">Terms of Service</a>
            </div>
            <p>© {{ date('Y') }} Lexicon. All rights reserved.</p>
        </div>

        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AOS.init({
                    duration: 1000,
                    once: false,
                    mirror: true
                });
            });
        </script>

        <style>
            @keyframes pulseSlow {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.6;
                }

                50% {
                    transform: scale(1.05);
                    opacity: 0.8;
                }
            }

            .animate-pulse-slow {
                animation: pulseSlow 4s ease-in-out infinite;
            }
        </style>
    </footer>
</body>

</html>
