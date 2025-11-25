<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Carbon\Carbon;

class LoanController extends Controller
{
    const DEFAULT_AMOUNT = 500; // Default borrow amount (KES)
    const FINE_PER_DAY = 70;    // Fine per overdue day (KES)

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin / Librarian: View all loans (Manage Loans)
     */
    public function index()
    {
        $loans = Loan::with(['user', 'book'])->latest()->paginate(10);
        foreach ($loans as $loan) {
            $loan->fine = $loan->calculated_fine;
            $loan->total_amount = $loan->calculated_total;
        }
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

        $amount = $book->borrow_price ?? self::DEFAULT_AMOUNT;
        $paymentOption = $request->input('payment_option'); // 'instant' or 'deferred'

        if ($paymentOption === 'instant') {
            return $this->stripeCheckoutBorrow($book, $amount);
        }

        // Deferred payment
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'borrowed',
            'is_paid' => false,
            'amount' => $amount,
            'due_at' => now()->addDays(14),
        ]);

        return response()->json(['message' => 'Book borrowed successfully.', 'loan_id' => $loan->id]);
    }

    /**
     * Stripe checkout for borrowing
     */
    private function stripeCheckoutBorrow(Book $book, $amount)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'kes',
                            'product_data' => ['name' => $book->title],
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

    /**
     * Stripe success callback for instant borrow
     */
    public function borrowSuccess(Book $book)
    {
        $user = Auth::user();

        $loan = Loan::firstOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'borrowed'],
            [
                'amount' => $book->borrow_price ?? self::DEFAULT_AMOUNT,
                'due_at' => now()->addDays(14),
                'is_paid' => true,
            ]
        );

        // Record payment
        $this->storePayment($loan, 'Stripe');

        return redirect()->route('books.index')->with('success', 'Payment successful, book borrowed!');
    }

    /**
     * Return a book (redirects to Stripe if unpaid)
     */
    public function returnBook(Loan $loan)
    {
        $user = Auth::user();

        if ($user->role === 'member' && $loan->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($loan->status === 'returned') {
            return redirect()->back()->with('info', 'Book already returned.');
        }

        $fineDays = now()->gt($loan->due_at) ? now()->diffInDays($loan->due_at) : 0;
        $fine = $fineDays * self::FINE_PER_DAY;
        $totalAmount = ($loan->amount ?? 0) + $fine;

        if (!$loan->is_paid) {
            return $this->stripeCheckoutReturn($loan, $totalAmount);
        }

        // Already paid
        $loan->update(['status' => 'returned', 'returned_at' => now()]);
        return redirect($user->role === 'member' ? route('loans.my') : route('loans.index'))
            ->with('success', 'Book returned successfully.');
    }

    /**
     * Stripe checkout for returning a book
     */
    private function stripeCheckoutReturn(Loan $loan, $totalAmount)
    {
        $user = Auth::user();
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'kes',
                            'product_data' => ['name' => $loan->book->title . ' (Return Payment)'],
                            'unit_amount' => intval($totalAmount * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => $user->role === 'member'
                    ? route('loans.return.success', ['loan' => $loan->id])
                    : route('admin.loans.return.success', ['loan' => $loan->id]),
                'cancel_url' => $user->role === 'member'
                    ? route('loans.my')
                    : route('loans.index'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe payment redirect failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment initialization failed.');
        }

        return redirect($session->url);
    }

    /**
     * Stripe success callback for returning a book
     */
    public function returnSuccess(Loan $loan)
    {
        $user = Auth::user();

        $loan->update([
            'status' => 'returned',
            'returned_at' => now(),
            'is_paid' => true,
        ]);

        // Record payment
        $this->storePayment($loan, 'Stripe');

        return redirect(in_array($user->role, ['admin', 'librarian'])
            ? route('loans.index')
            : route('loans.my'))->with('success', 'Payment successful! Book returned.');
    }

    /**
     * Pay deferred loan (Stripe checkout)
     */
    public function payDeferredLoan($loanId)
    {
        $user = Auth::user();
        $loan = Loan::with('book')->findOrFail($loanId);

        if ($loan->user_id !== $user->id)
            abort(403);

        $chargeAmount = $loan->calculated_total;

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
                'cancel_url' => route('loans.my'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe deferred payment failed: ' . $e->getMessage());
            return redirect()->route('loans.my')->with('error', 'Payment initialization failed.');
        }

        return redirect($session->url);
    }

    /**
     * Stripe success callback for deferred payment
     */
    public function paySuccess(Loan $loan)
    {
        $loan->update(['is_paid' => true]);
        $this->storePayment($loan, 'Stripe');
        return redirect()->route('loans.my')->with('success', 'Payment successful!');
    }

    /**
     * Store payment in database
     */
    private function storePayment(Loan $loan, $method = 'Stripe')
    {
        $fineDays = now()->gt($loan->due_at) ? now()->diffInDays($loan->due_at) : 0;
        $fineTotal = $fineDays * self::FINE_PER_DAY;
        $borrowFee = $loan->amount ?? self::DEFAULT_AMOUNT;

        // Avoid duplicate payment records
        if (!Payment::where('loan_id', $loan->id)->where('method', $method)->exists()) {
            Payment::create([
                'user_id' => $loan->user_id,
                'loan_id' => $loan->id,
                'method' => $method,
                'reference' => strtoupper(uniqid($method . '-')),
                'borrow_fee' => $borrowFee,
                'fine_per_day' => self::FINE_PER_DAY,
                'fine_days' => $fineDays,
                'fine_total' => $fineTotal,
                'total' => $borrowFee + $fineTotal,
            ]);
        }
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
            'amount' => 'nullable|numeric|min:0',
            'is_paid' => 'required|boolean',
        ]);

        $loan->update([
            'status' => $request->status,
            'due_at' => $request->due_at,
            'amount' => $request->amount ?? $loan->amount,
            'is_paid' => $request->is_paid,
            'total' => ($request->amount ?? $loan->amount) + $loan->calculated_fine,
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
        return view('users.loans', compact('loans'));
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $stats = [
            'books' => Book::count(),
            'users' => \App\Models\User::count(),
            'authors' => \App\Models\Author::count(),
            'categories' => \App\Models\Category::count(),
            'active_loans' => Loan::where('status', 'borrowed')->count(),
        ];

        $recentLoans = Loan::with(['user', 'book'])->latest()->take(10)->get();
        foreach ($recentLoans as $loan) {
            $loan->fine = $loan->calculated_fine;
            $loan->total_amount = $loan->calculated_total;
        }

        return view('admin.dashboard', compact('stats', 'recentLoans'));
    }
}
