<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class LoanController extends Controller
{
    const DEFAULT_AMOUNT = 500; // Default borrow amount (KES)
    const FINE_PER_DAY = 70;    // Fine per overdue day (KES)

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
     *
     * Request expects 'payment_option' => 'instant'|'deferred'
     */
    public function borrow(Request $request, Book $book)
    {
        $user = Auth::user();

        // Prevent duplicate active borrow
        $existingLoan = $user->loans()
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingLoan) {
            return response()->json(['message' => 'You already borrowed this book.'], 400);
        }

        $paymentOption = $request->input('payment_option'); // 'instant' or 'deferred'
        $amount = $book->borrow_price ?? self::DEFAULT_AMOUNT;

        // If instant payment, create stripe session and return checkout url (frontend uses it)
        if ($paymentOption === 'instant') {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            try {
                $session = StripeSession::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'kes',
                                'product_data' => ['name' => $book->title],
                                // Stripe expects amount in cents (or smallest currency unit) — KES has no cents but Stripe expects integer
                                'unit_amount' => intval($amount * 100),
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => route('loans.borrow.success', ['book' => $book->id]),
                    'cancel_url' => route('books.index'),
                ]);
            } catch (\Throwable $e) {
                Log::error('Stripe session create failed: ' . $e->getMessage());
                return response()->json(['message' => 'Payment initialization failed.'], 500);
            }

            return response()->json(['checkoutUrl' => $session->url]);
        }

        // Deferred payment: create loan record with unpaid status
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
     * Stripe success callback for instant payment (after borrow)
     *
     * Marks loan as paid and creates loan if missing.
     */
    public function borrowSuccess(Book $book)
    {
        $user = Auth::user();

        // Create a loan if not exists or update existing to paid
        $loan = Loan::firstOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'borrowed'],
            [
                'amount' => $book->borrow_price ?? self::DEFAULT_AMOUNT,
                'due_at' => now()->addDays(14),
                'payment_status' => 'paid',
            ]
        );

        // Ensure payment_status is paid
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

        // Authorization: admin can return any, user only their own
        if ($user->role !== 'admin' && $loan->user_id !== $user->id) {
            abort(403);
        }

        // Cannot return if already returned
        if ($loan->status === 'returned') {
            return response()->json(['message' => 'Book already returned.'], 400);
        }

        // Require payment before return
        if ($loan->payment_status === 'unpaid') {
            return response()->json(['message' => 'Payment required to return the book.', 'loan_id' => $loan->id], 400);
        }

        $loan->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        return response()->json(['message' => 'Book returned successfully.']);
    }

    /**
     * Pay deferred loan (initiates Stripe checkout for an existing loan)
     */
    public function payDeferredLoan(Loan $loan)
    {
        $user = Auth::user();
        if ($loan->user_id !== $user->id) {
            abort(403);
        }

        // Calculate current fine (if overdue and still borrowed)
        $fine = 0;
        if ($loan->status === 'borrowed' && now()->gt($loan->due_at)) {
            $daysOverdue = now()->diffInDays($loan->due_at);
            $fine = $daysOverdue * self::FINE_PER_DAY;
        }

        // Amount to charge = loan amount + current fine
        $chargeAmount = ($loan->amount ?? self::DEFAULT_AMOUNT) + $fine;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'kes',
                            'product_data' => ['name' => $loan->book->title],
                            'unit_amount' => intval($chargeAmount * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('loans.pay.success', ['loan' => $loan->id]),
                'cancel_url' => route('books.index'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe session create failed (deferred): ' . $e->getMessage());
            return redirect()->route('books.index')->with('error', 'Payment initialization failed.');
        }

        // Option: store the chargeAmount and fine snapshot if desired (not required)
        // e.g. $loan->update(['pending_charge' => $chargeAmount, 'pending_fine' => $fine]);

        return redirect($session->url);
    }

    /**
     * Stripe success callback for deferred payment
     *
     * Marks loan as paid and (optionally) clears pending charge snapshot.
     */
    public function paySuccess(Loan $loan)
    {
        // Only the owner or admin should access this in practice; check guard if needed
        $loan->update(['payment_status' => 'paid']);

        // Optionally, record the payment (e.g. payment record) and final charged amount (amount + computed fine at payment time)
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
            // Keep amount stored separately; total can be overwritten for admin adjustments
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
     *
     * Computes fine and total amount (amount + fine) for each loan and returns view.
     */
    public function myLoans()
    {
        $user = Auth::user();
        $loans = $user->loans()->with('book')->latest()->get();

        foreach ($loans as $loan) {
            $fine = 0;
            if ($loan->status === 'borrowed' && $loan->due_at && now()->gt($loan->due_at)) {
                $daysOverdue = now()->diffInDays($loan->due_at);
                $fine = $daysOverdue * self::FINE_PER_DAY;
            }

            $baseAmount = $loan->amount ?? self::DEFAULT_AMOUNT;
            $loan->fine = $fine;
            $loan->total_amount = ($loan->total ?? $baseAmount) + $fine;
        }

        return view('user.loans', compact('loans'));
    }
}
