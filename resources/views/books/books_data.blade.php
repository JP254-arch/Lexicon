@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-3xl font-bold mb-6">📊 Books & Data</h1>

        {{-- Search Bar --}}
        <form action="{{ route('books.data') }}" method="GET" class="mb-6">
            <div class="flex items-center space-x-2">
                <input type="text" name="search" placeholder="Search books..." value="{{ request('search') }}"
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    🔍 Search
                </button>
            </div>
        </form>

        @php
            $booksQuery = \App\Models\Book::with('author', 'category')->latest();
            if (request('search')) {
                $searchTerm = request('search');
                $booksQuery
                    ->where('title', 'like', "%{$searchTerm}%")
                    ->orWhereHas('author', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            }
            $books = $booksQuery->get();
        @endphp

        @if ($books->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg text-sm">
                    <thead class="bg-gray-100 text-gray-600 uppercase">
                        <tr>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Author</th>
                            <th class="py-3 px-4 text-left">Category</th>
                            <th class="py-3 px-4 text-left">Available Copies</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($books as $book)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $book->title }}</td>
                                <td class="py-3 px-4">{{ $book->author->name ?? 'Unknown' }}</td>
                                <td class="py-3 px-4">{{ $book->category->name ?? 'Uncategorized' }}</td>
                                <td class="py-3 px-4">{{ $book->available_copies ?? 0 }}</td>
                                <td class="py-3 px-4 space-x-2">
                                    <a href="{{ route('books.show', $book->id) }}"
                                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                                        🔍 View Details
                                    </a>
                                    <a href="{{ route('books.edit', $book->id) }}"
                                        class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white text-xs font-medium px-3 py-1 rounded transition">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('books.destroy', $book->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded transition">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No books found.</p>
        @endif
    </div>
@endsection
