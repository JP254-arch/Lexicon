@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Book Title --}}
            <div class="px-6 py-6 border-b">
                <h1 class="text-3xl font-bold text-indigo-600 text-center">{{ $book->title }}</h1>
            </div>

            {{-- Book Content --}}
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

                {{-- Book Details --}}
                <div class="md:col-span-2 flex flex-col justify-between space-y-4 overflow-y-auto max-h-[80vh] pr-2">
                    {{-- Basic Info --}}
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

                    {{-- Borrow / Return --}}
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
                                    if ($daysBorrowed <= 3) {
                                        $btnColor = 'bg-green-600 hover:bg-green-700';
                                    } elseif ($daysBorrowed <= 7) {
                                        $btnColor = 'bg-orange-500 hover:bg-orange-600';
                                    } else {
                                        $btnColor = 'bg-red-600 hover:bg-red-700';
                                    }
                                @endphp
                                <form action="{{ route('loans.return', $book->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 text-white rounded-lg transition {{ $btnColor }}">
                                        Return
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('loans.borrow', $book->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition transform hover:scale-105">
                                        Borrow
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth

                    {{-- Engagement Row --}}
                    <div class="flex items-center space-x-6 mt-4 text-gray-600">
                        {{-- Likes --}}
                        <button
                            class="like-btn flex items-center space-x-2 focus:outline-none transform transition duration-200"
                            data-book-id="{{ $book->id }}"
                            data-liked="{{ auth()->user() && $book->isLikedBy(auth()->user()) ? '1' : '0' }}">
                            <svg class="like-icon w-6 h-6 transition-transform duration-200"
                                fill="{{ $book->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20l7.682-7.318a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                            <span class="likes-count text-sm">{{ $book->likes_count ?? $book->likesCount() }}</span>
                        </button>

                        {{-- Views --}}
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

                        {{-- Star Ratings --}}
                        <div class="flex items-center space-x-1 rating-wrapper" data-book-id="{{ $book->id }}">
                            @php $avg = round($book->averageRating() ?? 0, 0); @endphp
                            <div class="flex items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button class="star-btn hover:scale-110 transition-transform duration-150"
                                        data-book-id="{{ $book->id }}" data-stars="{{ $i }}"
                                        title="Give {{ $i }} stars">
                                        @if ($i <= $avg)
                                            <svg class="star w-5 h-5 text-yellow-400" fill="currentColor"
                                                viewBox="0 0 20 20"></svg>
                                        @else
                                            <svg class="star w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24"></svg>
                                        @endif
                                    </button>
                                @endfor
                            </div>
                            <span class="text-sm ml-2">{{ $book->ratings_count ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mt-4 text-gray-700">
                        <h3 class="font-semibold text-lg mb-2">Description</h3>
                        <p>{{ $book->description ?? 'No description available.' }}</p>
                    </div>

                    {{-- Reviews --}}
                    <div class="mt-4">
                        <h3 class="font-semibold text-lg mb-2">Reviews</h3>
                        <div class="space-y-1 max-h-60 overflow-y-auto pr-2">
                            @foreach ($book->reviews as $review)
                                <p><strong>{{ $review->user->name ?? 'Anonymous' }}:</strong> {{ $review->content }}</p>
                            @endforeach
                            @if ($book->reviews_count > $book->reviews->count())
                                <p class="text-gray-400 text-xs">+{{ $book->reviews_count - $book->reviews->count() }} more
                                    reviews...</p>
                            @endif
                        </div>

                        {{-- Add Review Form --}}
                        @auth
                            <form class="add-review-form mt-2" data-book-id="{{ $book->id }}">
                                @csrf
                                <input type="text" name="content" placeholder="Write a review..."
                                    class="w-full border rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required>
                                <button type="submit"
                                    class="mt-1 bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition transform hover:scale-105">Submit</button>
                            </form>
                        @endauth
                    </div>

                    {{-- BACK TO BOOKS BUTTON --}}
                    <div class="mt-6">
                        <a href="{{ route('books.index') }}"
                            class="w-full inline-block text-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            ← Back to Books
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const token = document.head.querySelector('meta[name="csrf-token"]').content;

                // Like button
                document.querySelector('.like-btn')?.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const btn = this;
                    const svg = btn.querySelector('svg.like-icon');
                    const bookId = btn.dataset.bookId;
                    try {
                        const res = await fetch(`/books/${bookId}/like`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });
                        const json = await res.json();
                        btn.querySelector('.likes-count').textContent = json.likesCount;
                        if (json.liked) {
                            svg.setAttribute('fill', 'currentColor');
                            svg.style.color = '#ef4444';
                        } else {
                            svg.setAttribute('fill', 'none');
                            svg.style.color = '';
                        }
                    } catch (err) {
                        console.error(err);
                    }
                });

                // Star rating buttons
                document.querySelectorAll('.star-btn').forEach(btn => {
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const bookId = this.dataset.bookId;
                        const stars = this.dataset.stars;
                        try {
                            const res = await fetch(`/books/${bookId}/rate`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    stars
                                })
                            });
                            const json = await res.json();
                            const wrapper = document.querySelector(
                                `.rating-wrapper[data-book-id="${bookId}"]`);
                            if (wrapper) {
                                const avg = Math.round(json.average);
                                wrapper.querySelectorAll('svg.star').forEach((svg, idx) => {
                                    svg.setAttribute('fill', idx < avg ? 'currentColor' :
                                        'none');
                                });
                                wrapper.querySelector('span').textContent = json.count;
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    });
                });

                // Add review
                document.querySelector('.add-review-form')?.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const bookId = this.dataset.bookId;
                    const input = this.querySelector('input[name="content"]');
                    const content = input.value.trim();
                    if (!content) return;
                    try {
                        const res = await fetch(`/books/${bookId}/review`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                review: content
                            })
                        });
                        const reviewsDiv = input.closest('form').previousElementSibling;
                        const p = document.createElement('p');
                        p.innerHTML = `<strong>${json.user}:</strong> ${json.content}`;
                        reviewsDiv.prepend(p);
                        input.value = '';
                    } catch (err) {
                        console.error(err);
                    }
                });
            })();
        </script>
    @endpush
@endsection
