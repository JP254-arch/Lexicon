@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

            {{-- Book Title --}}
            <div class="px-6 py-6 border-b">
                <h1 class="text-3xl font-bold text-indigo-600 text-center">{{ $book->title }}</h1>
            </div>

            <div class="px-6 py-6 grid md:grid-cols-3 gap-6">

                {{-- Cover --}}
                <div class="md:col-span-1 flex justify-center">
                    @php
                        $coverData = $book->cover ?? null;
                        $coverUrl =
                            is_array($coverData) && !empty($coverData['path'])
                                ? ($coverData['type'] === 'upload'
                                    ? asset('storage/' . ltrim($coverData['path'], '/'))
                                    : $coverData['path'])
                                : asset('images/default-cover.jpg');
                    @endphp

                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}"
                        class="rounded shadow-md w-full md:w-64 h-auto object-cover transition transform hover:scale-105 hover:shadow-xl">
                </div>

                {{-- Right Side --}}
                <div class="md:col-span-2 flex flex-col justify-between space-y-4 overflow-y-auto max-h-[80vh] pr-2">

                    {{-- Book Info --}}
                    <div class="space-y-2">
                        <p class="text-gray-700"><span class="font-semibold">Author:</span>
                            {{ $book->author->name ?? 'Unknown' }}</p>
                        <p class="text-gray-700"><span class="font-semibold">Category:</span>
                            {{ $book->category->name ?? 'Uncategorized' }}</p>
                        <p class="text-gray-700"><span class="font-semibold">Available Copies:</span>
                            {{ $book->available_copies }}</p>
                        @if ($book->published_at)
                            <p class="text-gray-500 text-sm">Published: {{ $book->published_at->format('M d, Y') }}</p>
                        @endif
                        <p class="text-gray-500 text-sm">Added: {{ $book->created_at->format('M d, Y') }}</p>
                    </div>

                    {{-- Borrow/Return --}}
                    @auth
                        @php
                            $loan = auth()
                                ->user()
                                ->loans()
                                ->where('book_id', $book->id)
                                ->where('status', 'borrowed')
                                ->first();
                        @endphp

                        @if (auth()->user()->role === 'member')
                            @if ($loan)
                                @php
                                    $daysBorrowed = now()->diffInDays($loan->created_at);
                                    $btnColor =
                                        $daysBorrowed <= 3
                                            ? 'bg-green-600 hover:bg-green-700'
                                            : ($daysBorrowed <= 7
                                                ? 'bg-orange-500 hover:bg-orange-600'
                                                : 'bg-red-600 hover:bg-red-700');
                                @endphp

                                <form action="{{ route('loans.return', $book->id) }}" method="POST">
                                    @csrf
                                    <button class="w-full px-4 py-2 text-white rounded-lg transition {{ $btnColor }}">
                                        Return
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('loans.borrow', $book->id) }}" method="POST">
                                    @csrf
                                    <button
                                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition transform hover:scale-105">
                                        Borrow
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth

                    {{-- Engagement Buttons --}}
                    <div class="flex items-center space-x-6 mt-4 text-gray-600">

                        {{-- Likes --}}
                        {{-- <button
                            class="like-btn flex items-center space-x-2 focus:outline-none transform transition duration-200 cursor-pointer"
                            data-book-id="{{ $book->id }}"
                            data-liked="{{ auth()->user() && $book->isLikedBy(auth()->user()) ? '1' : '0' }}">

                            <svg class="like-icon w-6 h-6 transition-all duration-300 transform"
                                fill="{{ $book->isLikedBy(auth()->user()) ? 'red' : 'none' }}"
                                stroke="{{ $book->isLikedBy(auth()->user()) ? 'red' : 'currentColor' }}"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20l7.682-7.318a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>

                            <span class="likes-count text-sm transition-all duration-300">
                                {{ $book->likes_count ?? $book->likesCount() }}
                            </span>
                        </button> --}}

                        {{-- VIEWS --}}
                        <div class="flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <span class="text-sm">{{ $book->views ?? 0 }}</span>
                        </div>

                    </div>

                    {{-- Description --}}
                    <div class="mt-4 text-gray-700">
                        <h3 class="font-semibold text-lg mb-2">Description</h3>
                        <p>{{ $book->description ?? 'No description available.' }}</p>
                    </div>

                    {{-- Back Button --}}
                    <div class="mt-6">
                        <a href="{{ route('books.index') }}"
                            class="w-full inline-block text-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            ← Back to Books
                        </a>
                    </div>

                </div>
            </div>
        </div>

        {{-- SCRIPTS --}}
        @push('scripts')
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const token = document.head.querySelector('meta[name="csrf-token"]').content;

                    /* LIKE BUTTON */
                    @push('scripts')
                        <
                        script
                        script
                        script
                        script >
                            document.addEventListener("DOMContentLoaded", () => {
                                const token = document.head.querySelector('meta[name="csrf-token"]').content;

                                document.querySelectorAll('.like-btn').forEach(button => {
                                    button.addEventListener('click', async function() {
                                        const bookId = this.dataset.bookId;
                                        const icon = this.querySelector('.like-icon');
                                        const count = this.querySelector('.likes-count');

                                        try {
                                            const response = await fetch(`/books/${bookId}/like`, {
                                                method: "POST",
                                                headers: {
                                                    "X-CSRF-TOKEN": token,
                                                    "Accept": "application/json",
                                                }
                                            });

                                            const data = await response.json();

                                            // Update heart color
                                            if (data.liked) {
                                                icon.setAttribute('fill', 'red');
                                                icon.setAttribute('stroke', 'red');
                                            } else {
                                                icon.setAttribute('fill', 'none');
                                                icon.setAttribute('stroke', 'currentColor');
                                            }

                                            // Update likes count
                                            count.textContent = data.likes_count;

                                            // Small scale animation for feedback
                                            icon.classList.add('scale-125');
                                            count.classList.add('scale-125');
                                            setTimeout(() => {
                                                icon.classList.remove('scale-125');
                                                count.classList.remove('scale-125');
                                            }, 150);

                                        } catch (err) {
                                            console.error(err);
                                        }
                                    });
                                });
                            }); <
                        />
                    @endpush

                });
            </script>
        @endpush
    @endsection
