<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Payment; // <-- Added Payment model

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
            'categories' => Category::count(),
            'active_loans' => Loan::where('status', 'borrowed')->count(),
            'total_revenue' => Payment::sum('total'), // <-- Total revenue for finance
        ];

        // Fetch recent loan activity
        $recentLoans = Loan::with(['user', 'book'])
            ->latest()
            ->take(5)
            ->get();

        // Fetch all payments for Finance table
        $payments = Payment::with(['user', 'loan.book'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLoans', 'payments'));
    }
}
