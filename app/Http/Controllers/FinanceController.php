<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|librarian']);
    }

    public function index()
    {
        // All payments, latest first
        $payments = Payment::with(['user', 'loan.book'])
            ->orderBy(column: 'created_at', 'desc')
            ->get();

        // Optional: total revenue
        $totalRevenue = $payments->sum('total');

        return view('admin.finance.index', compact('payments', 'totalRevenue'));
    }
}
