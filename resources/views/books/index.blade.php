@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold mb-6 text-indigo-600 text-center">All Books</h1>

        {{-- Search Bar --}}
        <form action="{{ route('books.index') }}" method="GET" class="mb-6">
            <div class="flex items-center space-x-2">
                <input type="text" name="q" placeholder="Search books by title, author, or category..."
                    value="{{ request('q') }}"
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                <select name="search_by"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" {{ request('search_by') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="title" {{ request('search_by') == 'title' ? 'selected' : '' }}>Title</option>
                    <option value="author" {{ request('search_by') == 'author' ? 'selected' : '' }}>Author</option>
                    <option value="category" {{ request('search_by') == 'category' ? 'selected' : '' }}>Category</option>
                </select>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    🔍 Search
                </button>
            </div>
        </form>



        {{-- Book Grid --}}
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($books as $book)
                @php
                    $loan = auth()->user()->loans()->where('book_id', $book->id)->where('status', 'borrowed')->first();
                    $coverUrl =
                        $book->cover && isset($book->cover['path'])
                            ? ($book->cover['type'] === 'upload'
                                ? asset('storage/' . ltrim($book->cover['path'], '/'))
                                : $book->cover['path'])
                            : asset('images/default-cover.jpg');
                @endphp

                <div class="bg-white shadow-md rounded-2xl overflow-hidden hover:shadow-lg transition flex flex-col">
                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}"
                        class="w-full h-48 object-cover {{ $coverUrl === asset('images/default-cover.jpg') ? 'opacity-70' : '' }}">
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xl font-semibold mb-2 text-gray-800">{{ $book->title }}</h4>
                            <p class="text-gray-600 mb-2">by {{ $book->author->name ?? 'Unknown Author' }}</p>
                            <p class="text-gray-500 text-sm mb-4">Category: {{ $book->category->name ?? 'Uncategorized' }}
                            </p>
                            <a href="{{ route('books.show', $book->id) }}"
                                class="text-indigo-600 font-semibold hover:underline">
                                View Details →
                            </a>
                        </div>

                        {{-- Borrow / Payment Buttons --}}
                        @if (auth()->user()->role === 'member')
                            @if (!$loan)
                                <button
                                    class="borrow-btn w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                                    data-book-id="{{ $book->id }}">
                                    Borrow
                                </button>
                            @else
                                <p class="text-green-600 font-semibold text-center py-2">Already Borrowed</p>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center col-span-3 text-gray-500">No books available.</p>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $books->links() }}
        </div>
    </div>

    {{-- Borrow Modal --}}
    <div id="borrowModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
            <h3 class="text-lg font-semibold mb-4">Borrow Book</h3>
            <div class="flex flex-col space-y-3">
                <button id="payInstantBtn" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">Pay
                    Instantly</button>
                <button id="payDeferredBtn" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg">Pay
                    on Return</button>
                <button id="closeModal" class="w-full bg-gray-300 hover:bg-gray-400 py-2 rounded-lg">Cancel</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const token = document.head.querySelector('meta[name="csrf-token"]').content;
                let selectedBookId = null;

                // Open borrow modal
                document.querySelectorAll('.borrow-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        selectedBookId = btn.dataset.bookId;
                        document.getElementById('borrowModal').classList.remove('hidden');
                    });
                });

                // Close modal
                document.getElementById('closeModal').addEventListener('click', () => {
                    document.getElementById('borrowModal').classList.add('hidden');
                });

                // Pay instantly
                document.getElementById('payInstantBtn').addEventListener('click', async () => {
                    if (!selectedBookId) return;
                    try {
                        const res = await fetch(`/books/${selectedBookId}/borrow`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_option: 'instant'
                            })
                        });
                        const json = await res.json();
                        if (json.checkoutUrl) window.location.href = json.checkoutUrl;
                        else if (json.message) alert(json.message);
                    } catch (err) {
                        console.error(err);
                        alert('Something went wrong.');
                    }
                });

                // Pay deferred
                document.getElementById('payDeferredBtn').addEventListener('click', async () => {
                    if (!selectedBookId) return;
                    try {
                        const res = await fetch(`/books/${selectedBookId}/borrow`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_option: 'deferred'
                            })
                        });
                        const json = await res.json();
                        if (json.message) {
                            alert(json.message);
                            document.getElementById('borrowModal').classList.add('hidden');
                            location.reload();
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Something went wrong.');
                    }
                });
            });
        </script>
    @endpush
@endsection
