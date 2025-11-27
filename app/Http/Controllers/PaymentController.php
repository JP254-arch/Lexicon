<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    const FINE_PER_DAY = 70; // Default fine per overdue day (KES)

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|librarian')->only(['downloadReceipt', 'finance']);
    }

    /**
     * Create Stripe checkout session for a loan (borrow or return)
     */
    public function checkout(Loan $loan)
    {
        if ($loan->is_paid) {
            return back()->with('info', 'This loan has already been paid.');
        }

        $borrowFee = $loan->amount ?? 500;
        $fineDays = now()->gt($loan->due_at) ? now()->diffInDays($loan->due_at) : 0;
        $fineTotal = $fineDays * self::FINE_PER_DAY;
        $total = $borrowFee + $fineTotal;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'kes',
                            'product_data' => [
                                'name' => $loan->book->title . ' Payment',
                            ],
                            'unit_amount' => intval($total * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('payment.success', ['loan' => $loan->id]),
                'cancel_url' => route('payment.cancel', ['loan' => $loan->id]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout creation failed: ' . $e->getMessage());
            return back()->with('error', 'Payment initialization failed.');
        }

        return redirect($session->url);
    }

    /**
     * Handle successful Stripe payment
     */
    public function success(Loan $loan)
    {
        $loan->update([
            'is_paid' => true,
            'status' => $loan->status === 'returned' ? 'returned' : 'borrowed',
            'returned_at' => $loan->status === 'returned' ? now() : null,
        ]);

        $payment = $this->storePaymentRecord($loan, 'Stripe');

        return view('payments.checkout', [
            'loan' => $loan,
            'status' => 'success',
            'message' => 'Payment successful!',
            'payment' => $payment,
        ]);
    }

    /**
     * Handle canceled Stripe payment
     */
    public function cancel(Loan $loan)
    {
        return view('payments.checkout', [
            'loan' => $loan,
            'status' => 'cancelled',
            'message' => 'Payment cancelled. You can try again later.',
        ]);
    }

    /**
     * Store a payment record in the database
     */
    private function storePaymentRecord(Loan $loan, $method = 'Stripe')
    {
        $borrowFee = $loan->amount ?? 500;
        $fineDays = now()->gt($loan->due_at) ? now()->diffInDays($loan->due_at) : 0;
        $fineTotal = $fineDays * self::FINE_PER_DAY;

        // Avoid duplicate payments
        $payment = Payment::firstOrCreate(
            ['loan_id' => $loan->id, 'method' => $method],
            [
                'user_id' => $loan->user_id,
                'borrow_fee' => $borrowFee,
                'fine_per_day' => self::FINE_PER_DAY,
                'fine_days' => $fineDays,
                'fine_total' => $fineTotal,
                'total' => $borrowFee + $fineTotal,
                'reference' => strtoupper(Str::random(10)),
            ]
        );

        return $payment;
    }

    /**
     * Download PDF receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        $payment->load('loan.book', 'user');
        $pdf = Pdf::loadView('pdf.receipt', compact('payment'));
        return $pdf->download('Receipt_' . $payment->id . '.pdf');
    }

    /**
     * Admin finance view with filters
     */
    public function finance(Request $request)
    {
        $query = Payment::with(['user', 'loan.book']);

        if ($request->filled('user_name')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->user_name . '%'));
        }

        if ($request->filled('book')) {
            $query->whereHas('loan.book', fn($q) => $q->where('title', 'like', '%' . $request->book . '%'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->latest()->get();
        $totalRevenue = $payments->sum('total');

        return view('admin.finance.index', compact('payments', 'totalRevenue'));
    }


    /**
     * Optional: Record manual/offline payment
     */
    public function storePayment(Request $request, Loan $loan)
    {
        $request->validate([
            'method' => 'required|string',
            'borrow_fee' => 'required|numeric|min:0',
            'fine_per_day' => 'nullable|numeric|min:0',
            'fine_days' => 'nullable|integer|min:0',
        ]);

        $fineTotal = ($request->fine_per_day ?? 0) * ($request->fine_days ?? 0);
        $total = $request->borrow_fee + $fineTotal;

        $payment = Payment::create([
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'method' => $request->method,
            'reference' => strtoupper(Str::random(10)),
            'borrow_fee' => $request->borrow_fee,
            'fine_per_day' => $request->fine_per_day ?? 0,
            'fine_days' => $request->fine_days ?? 0,
            'fine_total' => $fineTotal,
            'total' => $total,
        ]);

        if (!$loan->is_paid) {
            $loan->update(['is_paid' => true]);
        }

        return redirect()->route('payments.checkout', $loan->id)
            ->with(['success' => 'Payment recorded successfully.', 'payment' => $payment]);
    }
}
