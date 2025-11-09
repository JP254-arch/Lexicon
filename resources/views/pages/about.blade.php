@extends('layouts.app')

@section('content')
<!-- AOS Animation Styles -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="bg-gradient-to-br from-indigo-50 via-white to-rose-50 py-16 px-4">
    <div class="container mx-auto max-w-6xl">

        <!-- Hero Section -->
        <div class="text-center mb-16" data-aos="fade-up">
            <h1 class="text-5xl font-extrabold text-rose-600 mb-4">
                About <span class="text-indigo-700">Lexicon</span>
            </h1>
            <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                At <strong>Lexicon Library</strong>, we believe that every book has a voice — and every reader has a story.
                We’re more than a library; we’re a community built on curiosity, connection, and the joy of learning.
            </p>
        </div>

        <!-- Mission & Vision -->
        <div class="grid md:grid-cols-2 gap-10 mb-16">
            <div class="floating-card bg-white shadow-lg rounded-2xl p-8 border-t-4 border-rose-400"
                data-aos="fade-right">
                <h2 class="text-2xl font-bold text-rose-600 mb-4">🎯 Our Mission</h2>
                <p class="text-gray-700 leading-relaxed">
                    To connect readers with stories that enlighten, entertain, and empower.
                    We strive to make knowledge accessible for everyone through technology, collaboration, and love for books.
                </p>
            </div>
            <div class="floating-card bg-white shadow-lg rounded-2xl p-8 border-t-4 border-indigo-400"
                data-aos="fade-left">
                <h2 class="text-2xl font-bold text-indigo-600 mb-4">🌍 Our Vision</h2>
                <p class="text-gray-700 leading-relaxed">
                    We envision a world where libraries are not just buildings but living ecosystems —
                    hubs of creativity, culture, and connection, bridging the gap between digital learning and human interaction.
                </p>
            </div>
        </div>

        <!-- Our Story -->
        <div class="floating-card bg-gradient-to-r from-indigo-100 to-rose-100 rounded-2xl shadow-xl p-10 mb-16" data-aos="zoom-in">
            <h2 class="text-3xl font-extrabold text-center text-indigo-700 mb-6">📖 Our Story</h2>
            <p class="text-gray-700 max-w-4xl mx-auto leading-relaxed text-center">
                Lexicon was born from a simple idea — to make reading feel alive again.
                What started as a small local library has grown into a digital platform that connects book lovers,
                students, and lifelong learners. Whether you’re borrowing your first book or diving into rare collections,
                Lexicon is your home for discovery.
                <br><br>
                Through innovation and community, we’re rewriting what it means to “go to the library.”
            </p>
        </div>

        <!-- What We Offer -->
        <div class="text-center mb-20" data-aos="fade-up">
            <h2 class="text-3xl font-extrabold text-rose-600 mb-10">💎 What We Offer</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="floating-card bg-white rounded-2xl shadow-md p-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-semibold text-indigo-700 mb-2">Vast Collection</h3>
                    <p class="text-gray-600">
                        From timeless classics to modern favorites, our shelves (and servers) are full of treasures waiting to be explored.
                    </p>
                </div>
                <div class="floating-card bg-white rounded-2xl shadow-md p-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl mb-4">💡</div>
                    <h3 class="text-xl font-semibold text-indigo-700 mb-2">Smart Recommendations</h3>
                    <p class="text-gray-600">
                        Discover your next favorite read through our intelligent suggestion system based on your tastes and interests.
                    </p>
                </div>
                <div class="floating-card bg-white rounded-2xl shadow-md p-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl mb-4">🤝</div>
                    <h3 class="text-xl font-semibold text-indigo-700 mb-2">Community Connection</h3>
                    <p class="text-gray-600">
                        Join a thriving network of readers, authors, and thinkers — Lexicon brings the world closer through words.
                    </p>
                </div>
            </div>
        </div>

        <!-- Meet the Team -->
        <div class="text-center mb-20" data-aos="fade-up">
            <h2 class="text-3xl font-extrabold text-indigo-700 mb-10">👥 Meet the Lexicon Team</h2>
            <p class="text-gray-600 max-w-3xl mx-auto mb-12">
                Behind Lexicon is a passionate team of dreamers, developers, and book enthusiasts dedicated to reimagining the way people experience libraries and stories.
            </p>

            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-10">
                <div class="team-card bg-rose-50 rounded-2xl shadow-lg"
                     data-aos="zoom-in" data-aos-delay="100">
                    <img src="{{ asset('images/librarian.jpg') }}" alt="Team Member"
                         class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-rose-400 mb-4">
                    <h3 class="text-xl font-semibold text-indigo-700">Fenny Fenaya</h3>
                    <p class="text-gray-500 mb-2">Founder & Chief Librarian</p>
                    <p class="text-gray-600 text-sm">
                        Visionary behind Lexicon — Fenny believes that every great idea starts with a great book.
                    </p>
                </div>

                <div class="team-card bg-indigo-50 rounded-2xl shadow-lg"
                     data-aos="zoom-in" data-aos-delay="200">
                    <img src="{{ asset('images/Jp.jpg') }}" alt="Team Member"
                         class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-indigo-400 mb-4">
                    <h3 class="text-xl font-semibold text-indigo-700">JP Mbaga</h3>
                    <p class="text-gray-500 mb-2">Lead Developer</p>
                    <p class="text-gray-600 text-sm">
                        Turning ideas into code and experiences — he built the digital magic behind Lexicon.
                    </p>
                </div>

                <div class="team-card bg-rose-100 rounded-2xl shadow-lg"
                     data-aos="zoom-in" data-aos-delay="300">
                    <img src="{{ asset('images/manager.jpg') }}" alt="Team Member"
                         class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-rose-400 mb-4">
                    <h3 class="text-xl font-semibold text-indigo-700">Shirly Marion</h3>
                    <p class="text-gray-500 mb-2">Community Manager</p>
                    <p class="text-gray-600 text-sm">
                        She connects readers, authors, and thinkers — ensuring Lexicon feels like home to everyone.
                    </p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center" data-aos="fade-up" data-aos-delay="100">
            <h2 class="text-3xl font-extrabold text-rose-600 mb-4"> Join the Lexicon Experience</h2>
            <p class="text-gray-700 max-w-2xl mx-auto mb-8">
                Whether you’re a reader, writer, or explorer of ideas, Lexicon welcomes you.
                Step into our world — where learning never stops, and curiosity always finds a home.
            </p>
            <a href="{{ route('register') }}"
               class="inline-block bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white px-8 py-3 rounded-lg shadow-md font-semibold transition-all duration-200 transform hover:-translate-y-1">
                 Get Started
            </a>
        </div>

    </div>
</section>

<!-- Custom Floating & Hover Animations -->
<style>
.floating-card, .team-card {
    transition: transform 0.5s ease, box-shadow 0.5s ease;
}
.floating-card:hover, .team-card:hover {
    transform: translateY(-10px) rotate(-1deg) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}
</style>

<!-- AOS Animation Script -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 1000,
        once: false, // Animate every time element enters viewport
        mirror: true, // Animate again when scrolling back up
    });
});
</script>
@endsection
