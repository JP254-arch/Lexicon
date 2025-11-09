<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\BookRating;
use App\Models\BookReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function __construct()
    {
        // Allow public access to browsing & searching books
        $this->middleware(['auth', 'role:librarian|admin'])->except(['index', 'show', 'search']);
    }

    /**
     * Display a list of books (with optional search & category filters)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $searchBy = $request->query('search_by', 'all');
        $categoryId = $request->query('category');

        $booksQuery = Book::with(['author', 'category'])
            ->withCount(['likes', 'reviews', 'ratings'])
            ->when($q, function ($qb) use ($q, $searchBy) {
                if ($searchBy === 'all' || $searchBy === 'title') {
                    $qb->where('title', 'like', "%{$q}%");
                }
                if ($searchBy === 'all' || $searchBy === 'category') {
                    $qb->orWhereHas('category', fn($q2) => $q2->where('name', 'like', "%{$q}%"));
                }
                if ($searchBy === 'all' || $searchBy === 'author') {
                    $qb->orWhereHas('author', fn($q3) => $q3->where('name', 'like', "%{$q}%"));
                }
            })
            ->when($categoryId, fn($qb) => $qb->where('category_id', $categoryId))
            ->orderBy('title', 'asc');

        // Home page: show random 3 books
        if ($request->path() === '/') {
            $books = Book::with('author', 'category')->inRandomOrder()->take(3)->get();
            $categories = Category::orderBy('name', 'asc')->get();
            return view('home', compact('books', 'categories'));
        }

        // **Paginate 9 books per page**
        $books = $booksQuery->paginate(9)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('books.index', compact('books', 'categories', 'q', 'searchBy', 'categoryId'));
    }

    /**
     * Live search API for autocomplete
     */
    public function search(Request $request)
    {
        $q = $request->query('q');

        $books = Book::with('author', 'category')
            ->when($q, function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                    ->orWhereHas('category', fn($qb2) => $qb2->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('author', fn($qb3) => $qb3->where('name', 'like', "%{$q}%"));
            })
            ->limit(10)
            ->get();

        return response()->json(
            $books->map(fn($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author->name ?? 'Unknown',
                'category' => $book->category->name ?? 'Uncategorized',
            ])
        );
    }

    public function create()
    {
        $authors = Author::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();
        return view('books.form', compact('authors', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn',
            'author_id' => 'nullable|exists:authors,id',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'total_copies' => 'required|integer|min:1',
            'cover' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover'] = ['type' => 'upload', 'path' => $path];
        }

        $data['available_copies'] = $data['total_copies'];

        Book::create($data);

        return redirect()->route('books.index')->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->increment('views');
        $book->load(['author', 'category', 'ratings', 'reviews', 'likes']);
        $book->loadCount(['likes', 'reviews', 'ratings']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $authors = Author::orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();
        return view('books.form', compact('book', 'authors', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn,' . $book->id,
            'author_id' => 'nullable|exists:authors,id',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'total_copies' => 'required|integer|min:1',
            'cover' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover'] = ['type' => 'upload', 'path' => $path];
        }

        $diff = $data['total_copies'] - $book->total_copies;
        if ($diff !== 0) {
            $book->available_copies += $diff;
        }

        $book->update($data);

        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return back()->with('success', 'Book deleted successfully.');
    }

    public function toggleLike(Request $request, Book $book)
    {
        $user = Auth::user();
        if (!$user)
            return response()->json(['message' => 'Unauthorized'], 401);

        $liked = $book->likes()->toggle($user->id)['attached'] ?? false;
        $likesCount = $book->likes()->count();

        return response()->json(['liked' => $liked, 'likesCount' => $likesCount]);
    }

    public function rate(Request $request, Book $book)
    {
        $user = Auth::user();
        if (!$user)
            return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        BookRating::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'stars' => $data['stars'],
            'comment' => $data['comment'] ?? null,
        ]);

        $average = $book->ratings()->avg('stars');
        $count = $book->ratings()->count();

        return response()->json(['average' => round($average, 2), 'count' => $count]);
    }

    public function addReview(Request $request, Book $book)
    {
        $user = Auth::user();
        if (!$user)
            return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate(['review' => 'required|string|max:1000']);

        BookReview::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'content' => $data['review'],
        ]);

        $reviewsCount = $book->reviews()->count();

        return response()->json(['reviewsCount' => $reviewsCount]);
    }
}
