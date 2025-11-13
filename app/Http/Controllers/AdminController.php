<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use App\Models\Loan;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|librarian']);
    }

    public function index()
    {
        // Fetch key stats
        $stats = [
            'books' => Book::count(),
            'users' => User::count(),
            'authors' => Author::count(),
            'categories' => Category::count(), // ✅ Added this line
            'active_loans' => Loan::where('status', 'borrowed')->count(),
        ];

        // Fetch recent loan activity
        $recentLoans = Loan::with(['user', 'book'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLoans'));
    }
}
