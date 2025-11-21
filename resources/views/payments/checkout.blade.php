@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
        <h1 class="text-2xl font-bold mb-4">
            Payment {{ ucfirst($status) }}
        </h1>

        <p class="mb-6">{{ $message }}</p>

        @if ($status === 'success' && isset($payment))
            <div class="mb-6 p-4 border rounded-lg bg-green-50">
                <h2 class="font-semibold mb-2">Receipt Details</h2>

                <ul class="mb-4 text-gray-700">
                    <li><strong>Receipt #:</strong> {{ $payment->id }}</li>
                    <li><strong>Book:</strong> {{ $payment->loan->book->title }}</li>
                    <li><strong>Loan ID:</strong> {{ $payment->loan->id }}</li>
                    <li><strong>Borrow Fee:</strong> {{ $payment->borrow_fee }} RON</li>
                    <li><strong>Fine:</strong> {{ $payment->fine_total }} RON ({{ $payment->fine_days }} days)</li>
                    <li><strong>Total Paid:</strong> {{ $payment->total }} RON</li>
                    <li><strong>Payment Method:</strong> {{ $payment->method }}</li>
                    <li><strong>Reference:</strong> {{ $payment->reference }}</li>
                </ul>

                <a href="{{ route('receipt.download', $payment->id) }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Download PDF Receipt
                </a>
            </div>
        @endif

        <a href="{{ route('books.index') }}" class="inline-block mt-4 text-indigo-600 hover:underline">
            Return to Books
        </a>
    </div>
@endsection
