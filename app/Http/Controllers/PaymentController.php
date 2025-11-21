<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    // Create Stripe Checkout session
    public function checkout(Request $request, Loan $loan)
    {
        if ($loan->is_paid) {
            return back()->with('info', 'This loan has already been paid.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'kes',
                        'product_data' => [
                            'name' => 'Book Borrowing Fee - ' . $loan->book->title,
                        ],
                        'unit_amount' => ($loan->total ?? 500) * 100, // Stripe uses cents
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('payment.success', ['loan' => $loan->id]),
            'cancel_url' => route('payment.cancel', ['loan' => $loan->id]),
        ]);

        return redirect($session->url);
    }

    // Success page
    public function success(Loan $loan)
    {
        // Mark loan as paid
        $loan->update([
            'is_paid' => true,
            'status' => 'borrowed', // keep active until return
        ]);

        // Calculate fine (if any)
        $fineDays = $loan->late_days ?? 0;
        $finePerDay = 70;
        $fineTotal = $fineDays * $finePerDay;
        $borrowFee = 70; // Default borrow fee

        // Record payment in payments table
        $payment = Payment::create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'method' => 'Stripe',
            'reference' => 'STRIPE-' . rand(100000, 999999),
            'borrow_fee' => $borrowFee,
            'fine_per_day' => $finePerDay,
            'fine_days' => $fineDays,
            'fine_total' => $fineTotal,
            'total' => $borrowFee + $fineTotal,
        ]);

        return view('payments.checkout', [
            'loan' => $loan,
            'status' => 'success',
            'message' => 'Payment successful! You can now proceed to borrow or return your book.',
            'payment' => $payment, // pass payment for optional PDF download
        ]);
    }

    // Cancel page
    public function cancel(Loan $loan)
    {
        return view('payments.checkout', [
            'loan' => $loan,
            'status' => 'cancelled',
            'message' => 'Payment cancelled. You can try again later.',
        ]);
    }

    // Admin PDF receipt download
    public function downloadReceipt(Payment $payment)
    {
        // Load related models
        $payment->load('loan.book', 'user');

        // Generate PDF
        $pdf = Pdf::loadView('pdf.receipt', compact('payment'));

        return $pdf->download('Receipt_' . $payment->id . '.pdf');
    }
}
