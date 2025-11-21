@extends('layouts.app')

@section('content')
    <!-- AOS Animation Styles -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-indigo-50 via-white to-rose-50 py-20 rounded-3xl shadow-lg mx-4 md:mx-0">
        <div class="container mx-auto text-center px-4">
            <div class="flex justify-center mb-6" data-aos="zoom-in" data-aos-duration="1200">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Lexicon Logo"
                    class="h-40 w-40 rounded-full shadow-[0_0_25px_5px_rgba(255,0,102,0.6)] ring-4 ring-pink-400 ring-opacity-50 transition-all duration-500 hover:shadow-[0_0_45px_10px_rgba(255,0,102,0.8)] floating-logo">
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-rose-600" data-aos="fade-up" data-aos-duration="1000">
                Welcome to <span class="text-indigo-700">Lexicon</span>
            </h1>
            <p class="text-lg md:text-xl mb-8 text-gray-700 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                Your gateway to endless learning — explore, discover, and be inspired by books and ideas that shape the
                future.
            </p>
            <a href="{{ route('books.index') }}"
                class="bg-gradient-to-r from-rose-500 to-indigo-600 text-white font-semibold px-8 py-3 rounded-lg shadow-md hover:scale-105 transform transition-all duration-300"
                data-aos="zoom-in" data-aos-delay="400">
                Explore Books
            </a>
        </div>
    </section>

    <!-- Featured Books -->
    <section id="featured-books" class="py-16 container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-10 text-indigo-600" data-aos="fade-up">Featured Books</h2>

        <div class="grid md:grid-cols-3 gap-8">
            @forelse ($books->take(3) as $book)
                @php
                    $coverData = $book->cover ?? null;
                    if (is_array($coverData)) {
                        if (($coverData['type'] ?? null) === 'upload' && !empty($coverData['path'])) {
                            $coverUrl = asset('storage/' . ltrim($coverData['path'], '/'));
                        } elseif (($coverData['type'] ?? null) === 'url' && !empty($coverData['path'])) {
                            $coverUrl = $coverData['path'];
                        } else {
                            $coverUrl = asset('images/default-cover.jpg');
                        }
                    } else {
                        $coverUrl = asset('images/default-cover.jpg');
                    }
                @endphp

                <div class="book-card bg-white shadow-md rounded-2xl overflow-hidden hover:shadow-xl transition transform hover:-translate-y-2 duration-500"
                    data-aos="fade-up" data-aos-duration="1000">
                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}"
                        class="w-full h-56 object-cover {{ $coverUrl === asset('images/default-cover.jpg') ? 'opacity-70' : '' }}">
                    <div class="p-5">
                        <h4 class="text-xl font-semibold mb-2 text-gray-800">{{ $book->title }}</h4>
                        <p class="text-gray-600 mb-4">by {{ $book->author->name ?? 'Unknown Author' }}</p>
                        <a href="{{ route('books.show', $book->id) }}"
                            class="text-indigo-600 font-semibold hover:underline">
                            View Details →
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center col-span-3">No featured books available at the moment.</p>
            @endforelse
        </div>

        <div class="text-center mt-8" data-aos="fade-up" data-aos-delay="200">
            <a href="{{ route('books.index') }}" class="text-indigo-600 font-semibold hover:underline">
                View All Books →
            </a>
        </div>
    </section>

    <!-- Popular Categories -->
    <section class="bg-gradient-to-r from-indigo-50 via-white to-rose-50 py-16">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-3xl font-bold mb-10 text-teal-700" data-aos="fade-up">Popular Categories</h3>

            <div class="flex flex-wrap justify-center gap-4">
                @forelse ($categories as $category)
                    <a href="{{ route('books.index', ['category' => $category->id]) }}"
                        class="bg-white px-6 py-3 rounded-full shadow-md text-gray-700 font-medium
                          hover:bg-gradient-to-r hover:from-indigo-200 hover:to-pink-200
                          hover:text-fuchsia-900 hover:shadow-xl
                          transform hover:-translate-y-1 hover:rotate-1
                          transition-all duration-300 ease-in-out animate-hover-wave"
                        data-aos="fade-up" data-aos-delay="100">
                        {{ $category->name }}
                    </a>
                @empty
                    <p class="text-gray-500">No categories available.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Testimonials / Reviews -->
    <section class="bg-gradient-to-r from-indigo-50 via-white to-rose-50 py-20">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold text-indigo-700 mb-12" data-aos="fade-up"> What Our Readers Say
            </h2>
            <p class="text-gray-700 max-w-3xl mx-auto mb-10" data-aos="fade-up" data-aos-delay="100">
                Lexicon isn’t just a library — it’s a community. Hear what some of our members have to say about their
                experience.
            </p>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-2xl shadow-lg p-8 transform transition hover:-translate-y-3 hover:scale-105 duration-500"
                    data-aos="fade-up" data-aos-delay="100">
                    <p class="text-gray-700 italic mb-4">
                        "Lexicon transformed my reading habit! The recommendations are spot-on, and I love discovering new
                        authors every week."
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <img src="{{ asset('images/reader.jpg') }}" alt="Reader 1"
                            class="w-12 h-12 rounded-full object-cover border-2 border-rose-400">
                        <div>
                            <h4 class="font-semibold text-indigo-700">Samantha K.</h4>
                            <p class="text-gray-500 text-sm">Avid Reader</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl shadow-lg p-8 transform transition hover:-translate-y-3 hover:scale-105 duration-500"
                    data-aos="fade-up" data-aos-delay="200">
                    <p class="text-gray-700 italic mb-4">
                        "The community here is amazing. I’ve connected with fellow book lovers and authors. Lexicon is my
                        digital home for reading!"
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <img src="{{ asset('images/reader.jpg') }}" alt="Reader 2"
                            class="w-12 h-12 rounded-full object-cover border-2 border-indigo-400">
                        <div>
                            <h4 class="font-semibold text-indigo-700">Marcus L.</h4>
                            <p class="text-gray-500 text-sm">Book Enthusiast</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-2xl shadow-lg p-8 transform transition hover:-translate-y-3 hover:scale-105 duration-500"
                    data-aos="fade-up" data-aos-delay="300">
                    <p class="text-gray-700 italic mb-4">
                        "Lexicon’s collection is fantastic. From classics to modern favorites, I always find something
                        exciting to read!"
                    </p>
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <img src="{{ asset('images/reader.jpg') }}" alt="Reader 3"
                            class="w-12 h-12 rounded-full object-cover border-2 border-rose-400">
                        <div>
                            <h4 class="font-semibold text-indigo-700">Elena M.</h4>
                            <p class="text-gray-500 text-sm">Student & Reader</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final Call-to-Action -->
    <section
        class="relative py-24 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white text-center rounded-3xl mx-4 md:mx-0 shadow-2xl overflow-hidden"
        data-aos="fade-up">

        <!-- Decorative Background Shapes -->
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-white opacity-10 rounded-full animate-pulse-slow"></div>
        <div class="absolute -bottom-16 -right-16 w-96 h-96 bg-white opacity-10 rounded-full animate-pulse-slow"></div>

        <div class="relative container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 drop-shadow-lg">
                 Ready to Dive In?
            </h2>
            <p class="text-lg md:text-xl max-w-3xl mx-auto mb-12 drop-shadow-md">
                Join Lexicon today and explore a universe of books, ideas, and connections. Your next adventure starts here!
            </p>
            <a href="{{ route('register') }}"
                class="inline-block bg-white text-indigo-600 font-bold px-10 py-4 rounded-full shadow-xl transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:-translate-y-1">
                Get Started
            </a>
        </div>
    </section>

    <!-- Optional Animation CSS -->
    <style>
        @keyframes pulseSlow {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.15;
            }
        }

        .animate-pulse-slow {
            animation: pulseSlow 6s ease-in-out infinite;
        }
    </style>

    <!-- Floating Logo Animation & Hover Wave -->
    <style>
        .floating-logo {
            animation: floatLogo 3s ease-in-out infinite;
        }

        @keyframes floatLogo {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .book-card {
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }

        .book-card:hover {
            transform: translateY(-10px) rotate(-1deg) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        @keyframes hoverWave {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px) rotate(1deg);
            }
        }

        .animate-hover-wave:hover {
            animation: hoverWave 0.4s ease-in-out;
        }
    </style>

    <!-- AOS Animation Script -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 1000,
                once: false, // Animate every time element enters viewport
                mirror: true, // Animates again when scrolling back up
            });
        });
    </script>
@endsection
