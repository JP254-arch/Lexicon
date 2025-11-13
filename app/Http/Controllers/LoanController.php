<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class LoanController extends Controller
{
    const DEFAULT_AMOUNT = 500; // Default borrow amount
    const FINE_PER_DAY = 70;    // Fine per overdue day

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin / Librarian: View all loans
     */
    public function index()
    {
        $loans = Loan::with(['user', 'book'])->latest()->paginate(10);
        return view('loans.index', compact('loans'));
    }

    /**
     * Borrow a book
     */
    public function borrow(Request $request, Book $book)
    {
        $user = Auth::user();

        $existingLoan = $user->loans()
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingLoan) {
            return response()->json(['message' => 'You already borrowed this book.'], 400);
        }

        $paymentOption = $request->input('payment_option'); // 'instant' or 'deferred'
        $amount = $book->borrow_price ?? self::DEFAULT_AMOUNT;

        if ($paymentOption === 'instant') {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'kes',
                            'product_data' => ['name' => $book->title],
                            'unit_amount' => $amount * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('loans.borrow.success', ['book' => $book->id]),
                'cancel_url' => route('books.index'),
            ]);

            return response()->json(['checkoutUrl' => $session->url]);
        }

        // Deferred payment
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
            'payment_status' => 'unpaid',
            'amount' => $amount,
            'due_at' => now()->addDays(14),
        ]);

        return response()->json(['message' => 'Book borrowed successfully.', 'loan_id' => $loan->id]);
    }

    /**
     * Stripe success callback for instant payment
     */
    public function borrowSuccess(Book $book)
    {
        $user = Auth::user();

        $loan = Loan::firstOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            [
                'status' => 'borrowed',
                'amount' => $book->borrow_price ?? self::DEFAULT_AMOUNT,
                'due_at' => now()->addDays(14),
                'payment_status' => 'paid',
            ]
        );

        if ($loan->payment_status !== 'paid') {
            $loan->update(['payment_status' => 'paid']);
        }

        return redirect()->route('books.index')->with('success', 'Payment successful, book borrowed!');
    }

    /**
     * Return a book (member / admin)
     */
    public function returnBook(Loan $loan)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $loan->user_id !== $user->id) {
            abort(403);
        }

        if ($loan->payment_status === 'unpaid') {
            return response()->json(['message' => 'Payment required to return the book.', 'loan_id' => $loan->id]);
        }

        $loan->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        return response()->json(['message' => 'Book returned successfully.']);
    }

    /**
     * Pay deferred loan
     */
    public function payDeferredLoan(Loan $loan)
    {
        $user = Auth::user();
        if ($loan->user_id !== $user->id)
            abort(403);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'kes',
                        'product_data' => ['name' => $loan->book->title],
                        'unit_amount' => $loan->amount * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('loans.pay.success', $loan->id),
            'cancel_url' => route('books.index'),
        ]);

        return redirect($session->url);
    }

    /**
     * Stripe success callback for deferred payment
     */
    public function paySuccess(Loan $loan)
    {
        $loan->update(['payment_status' => 'paid']);
        return redirect()->route('books.index')->with('success', 'Payment successful!');
    }

    /**
     * Admin: Edit loan
     */
    public function edit(Loan $loan)
    {
        return view('loans.edit', compact('loan'));
    }

    /**
     * Admin: Update loan
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'status' => 'required|in:borrowed,returned',
            'due_at' => 'nullable|date',
            'total' => 'nullable|numeric|min:0',
        ]);

        $loan->update([
            'status' => $request->status,
            'due_at' => $request->due_at,
            'total' => $request->total ?? $loan->amount,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan updated successfully.');
    }

    /**
     * Admin: Delete loan
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'Loan deleted successfully.');
    }

    /**
     * Member: My loans dashboard
     */
    public function myLoans()
    {
        $user = Auth::user();
        $loans = $user->loans()->with('book')->latest()->get();

        // Compute total_amount and fine dynamically
        foreach ($loans as $loan) {
            $fine = 0;
            if ($loan->status === 'borrowed' && now()->gt($loan->due_at)) {
                $daysOverdue = now()->diffInDays($loan->due_at);
                $fine = $daysOverdue * self::FINE_PER_DAY;
            }
            $loan->fine = $fine;
            $loan->total_amount = ($loan->total ?? $loan->amount ?? self::DEFAULT_AMOUNT) + $fine;
        }

        return view('user.loans', compact('loans'));
    }
}
