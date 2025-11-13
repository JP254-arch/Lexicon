<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

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
                        'unit_amount' => $loan->total * 100, // Stripe uses cents
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
        $loan->update([
            'is_paid' => true,
            'status' => 'borrowed', // keep active until return
        ]);

        return view('payments.checkout', [
            'loan' => $loan,
            'status' => 'success',
            'message' => 'Payment successful! You can now proceed to borrow or return your book.',
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
}
