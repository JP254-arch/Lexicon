@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold mb-6 text-indigo-600 text-center">All Books</h1>

        {{-- Search Form --}}
        <form method="GET" action="{{ route('books.index') }}" class="mb-6 flex justify-center space-x-2">
            <select name="search_by"
                class="border rounded-l-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all" {{ request('search_by') === 'all' ? 'selected' : '' }}>All</option>
                <option value="title" {{ request('search_by') === 'title' ? 'selected' : '' }}>Title</option>
                <option value="category" {{ request('search_by') === 'category' ? 'selected' : '' }}>Category</option>
            </select>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search..."
                class="border-t border-b border-l px-4 py-2 w-80 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 transition">
                Search
            </button>
        </form>

        @auth
            <div class="grid md:grid-cols-3 gap-8">
                @forelse($books as $book)
                    @php
                        $coverData = $book->cover ?? null;
                        $coverUrl =
                            is_array($coverData) && !empty($coverData['path'])
                                ? ($coverData['type'] === 'upload'
                                    ? asset('storage/' . ltrim($coverData['path'], '/'))
                                    : $coverData['path'])
                                : asset('images/default-cover.jpg');

                        $loan = auth()
                            ->user()
                            ->loans()
                            ->where('book_id', $book->id)
                            ->where('status', 'borrowed')
                            ->first();

                        $highlight = request('q');
                        $searchBy = request('search_by', 'all');
                        $titleHighlighted =
                            $highlight && ($searchBy === 'title' || $searchBy === 'all')
                                ? preg_replace("/($highlight)/i", '<span class="bg-yellow-200">$1</span>', $book->title)
                                : $book->title;
                        $categoryHighlighted = $book->category
                            ? ($highlight && ($searchBy === 'category' || $searchBy === 'all')
                                ? preg_replace(
                                    "/($highlight)/i",
                                    '<span class="bg-yellow-200">$1</span>',
                                    $book->category->name,
                                )
                                : $book->category->name)
                            : 'Uncategorized';
                    @endphp

                    <div class="bg-white shadow-md rounded-2xl overflow-hidden hover:shadow-lg transition flex flex-col">
                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}"
                            class="w-full h-48 object-cover {{ $coverUrl === asset('images/default-cover.jpg') ? 'opacity-70' : '' }}">
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xl font-semibold mb-2 text-gray-800">{!! $titleHighlighted !!}</h4>
                                <p class="text-gray-600 mb-2">by {{ $book->author->name ?? 'Unknown Author' }}</p>
                                <p class="text-gray-500 text-sm mb-4">Category: {!! $categoryHighlighted !!}</p>
                            </div>

                            <div class="mt-2 flex flex-col space-y-2">
                                <a href="{{ route('books.show', $book->id) }}"
                                    class="text-indigo-600 font-semibold hover:underline">
                                    View Details →
                                </a>

                                {{-- Borrow / Return Button with Payment Flow --}}
                                @if (auth()->user()->role === 'member')
                                    @if ($loan)
                                        @php
                                            $daysBorrowed = now()->diffInDays($loan->created_at);
                                            $fineAmount = 0;
                                            if (now()->gt($loan->due_at)) {
                                                $daysOverdue = now()->diffInDays($loan->due_at);
                                                $fineAmount = $daysOverdue * 50;
                                            }
                                            if ($daysBorrowed <= 3) {
                                                $btnColor = 'bg-green-600 hover:bg-green-700';
                                            } elseif ($daysBorrowed <= 7) {
                                                $btnColor = 'bg-orange-500 hover:bg-orange-600';
                                            } else {
                                                $btnColor = 'bg-red-600 hover:bg-red-700';
                                            }
                                        @endphp

                                        @if ($fineAmount > 0)
                                            <a href="{{ route('payment.checkout', $loan->id) }}"
                                                class="w-full block text-center px-4 py-2 text-white rounded-lg transition {{ $btnColor }}">
                                                Pay Ksh {{ $fineAmount }} & Return
                                            </a>
                                        @else
                                            <form action="{{ route('loans.return', $book->id) }}" method="POST"
                                                class="mt-2">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full px-4 py-2 text-white rounded-lg transition {{ $btnColor }}">
                                                    Return
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <form action="{{ route('loans.borrow', $book->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit"
                                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                                Borrow
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                {{-- Engagement row (Likes, Views, Reviews, Ratings) --}}
                                <div class="mt-3 flex items-center justify-between text-gray-600">
                                    {{-- Likes --}}
                                    <div class="flex items-center space-x-2">
                                        <button class="like-btn flex items-center space-x-2 focus:outline-none"
                                            data-book-id="{{ $book->id }}"
                                            data-liked="{{ auth()->user() && $book->isLikedBy(auth()->user()) ? '1' : '0' }}">
                                            <svg class="like-icon w-5 h-5"
                                                fill="{{ $book->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20l7.682-7.318a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                </path>
                                            </svg>
                                            <span
                                                class="likes-count text-sm">{{ $book->likes_count ?? $book->likesCount() }}</span>
                                        </button>
                                    </div>

                                    {{-- Views --}}
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">{{ $book->views ?? 0 }}</span>
                                    </div>

                                    {{-- Reviews --}}
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 3.866-4.03 7-9 7a9.96 9.96 0 01-4-.8L3 21l1.8-4.2A9.96 9.96 0 014 12c0-3.866 4.03-7 9-7s9 3.134 9 7z" />
                                        </svg>
                                        <span
                                            class="text-sm reviews-count">{{ $book->reviews_count ?? $book->reviewsCount() }}</span>
                                    </div>

                                    {{-- Star Ratings --}}
                                    <div class="flex items-center space-x-1 rating-wrapper" data-book-id="{{ $book->id }}">
                                        @php $avg = round($book->averageRating() ?? 0, 0); @endphp
                                        <div class="flex items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button class="star-btn" data-book-id="{{ $book->id }}"
                                                    data-stars="{{ $i }}" title="Give {{ $i }} stars">
                                                    @if ($i <= $avg)
                                                        <svg class="star w-4 h-4" fill="currentColor" viewBox="0 0 20 20"></svg>
                                                    @else
                                                        <svg class="star w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24"></svg>
                                                    @endif
                                                </button>
                                            @endfor
                                        </div>
                                        <span class="text-sm ml-2">{{ $book->ratings_count ?? 0 }}</span>
                                    </div>
                                </div>

                                {{-- Latest 2 Reviews --}}
                                <div class="mt-2 text-sm text-gray-700 space-y-1">
                                    @foreach ($book->reviews->take(2) as $review)
                                        <p><strong>{{ $review->user->name ?? 'Anonymous' }}:</strong> {{ $review->content }}
                                        </p>
                                    @endforeach
                                    @if ($book->reviews_count > 2)
                                        <p class="text-gray-400 text-xs">+{{ $book->reviews_count - 2 }} more reviews...</p>
                                    @endif
                                </div>

                                {{-- Add Review Form --}}
                                <form class="add-review-form mt-2" data-book-id="{{ $book->id }}">
                                    @csrf
                                    <input type="text" name="content" placeholder="Write a review..."
                                        class="w-full border rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <button type="submit"
                                        class="mt-1 bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition">Submit</button>
                                </form>

                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center col-span-3">No books available at the moment.</p>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8 flex justify-center">
                {{ $books->appends(['q' => request('q'), 'search_by' => request('search_by')])->links() }}
            </div>
        @else
            <div class="text-center py-20">
                <p class="text-gray-500 text-lg">You must <a href="{{ route('login') }}"
                        class="text-indigo-600 underline">login</a> to view all books.</p>
            </div>
        @endauth
    </div>

    @push('scripts')
        <script>
            // Your existing JS for likes, ratings, and reviews remains unchanged
        </script>
    @endpush
@endsection
