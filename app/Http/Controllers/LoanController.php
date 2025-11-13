<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:librarian|admin')->only(['index', 'returnLoan', 'destroy', 'edit', 'update']);
        $this->middleware('role:member')->only(['borrow', 'returnBook', 'myLoans']);
    }

    // Admin / Librarian - Manage all loans
    public function index()
    {
        $loans = Loan::with(['book', 'user'])->latest()->paginate(20);
        return view('loans.index', compact('loans'));
    }

    // Admin / Librarian - Edit loan form
    public function edit(Loan $loan)
    {
        return view('loans.edit', compact('loan'));
    }

    // Admin / Librarian - Update loan
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'status' => 'required|in:borrowed,returned',
            'due_at' => 'required|date',
            'total' => 'required|numeric|min:0',
        ]);

        $loan->update([
            'status' => $request->status,
            'due_at' => $request->due_at,
            'total' => $request->total,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Loan updated successfully.');
    }

    // Member - Borrow a book (with payment option)
    public function borrow(Request $request, Book $book)
    {
        if ($book->available_copies < 1) {
            return back()->with('error', 'No copies available.');
        }

        $request->validate([
            'payment_option' => 'required|in:instant,later',
        ]);

        $due_at = now()->addWeeks(2);

        DB::transaction(function () use ($book, $due_at, $request) {
            $isPaid = $request->payment_option === 'instant';

            Loan::create([
                'book_id' => $book->id,
                'user_id' => auth()->id(),
                'borrowed_at' => now(),
                'due_at' => $due_at,
                'amount' => 350,
                'fine' => 0,
                'total' => 350,
                'is_paid' => $isPaid,
                'status' => 'borrowed',
            ]);

            $book->decrement('available_copies');
        });

        return back()->with('success', $request->payment_option === 'instant' ?
            'Book borrowed and paid successfully.' :
            'Book borrowed successfully. Payment due on return.');
    }

    // Member - Return book (handles fines and unpaid balances)
    public function returnBook(Book $book)
    {
        $loan = auth()->user()->loans()
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if (!$loan) {
            return back()->with('error', 'No active loan found for this book.');
        }

        DB::transaction(function () use ($loan, $book) {
            $fine = now()->greaterThan($loan->due_at) ? now()->diffInDays($loan->due_at) * 20 : 0;
            $total = 350 + $fine;

            $loan->update([
                'returned_at' => now(),
                'fine' => $fine,
                'total' => $total,
                'status' => 'returned',
            ]);

            $book->increment('available_copies');
        });

        return back()->with('success', 'Book returned successfully.');
    }

    // Member - View own loans
    public function myLoans()
    {
        $loans = auth()->user()
            ->loans()
            ->with(['book.author', 'book.category'])
            ->latest()
            ->get();

        return view('users.dashboard', [
            'user' => auth()->user(),
            'loans' => $loans
        ]);
    }

    // Admin / Librarian - Mark loan as returned
    public function returnLoan(Loan $loan)
    {
        if ($loan->status === 'returned') {
            return back()->with('error', 'This loan has already been returned.');
        }

        DB::transaction(function () use ($loan) {
            $fine = now()->greaterThan($loan->due_at) ? now()->diffInDays($loan->due_at) * 20 : 0;
            $total = 350 + $fine;

            $loan->update([
                'returned_at' => now(),
                'fine' => $fine,
                'total' => $total,
                'status' => 'returned',
            ]);

            $loan->book->increment('available_copies');
        });

        return back()->with('success', 'Loan marked as returned.');
    }

    // Admin / Librarian - Delete a loan
    public function destroy(Loan $loan)
    {
        $loan->delete();
        return back()->with('success', 'Loan deleted successfully.');
    }
}
