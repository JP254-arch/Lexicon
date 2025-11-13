@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">💳 My Loans</h2>

    <div class="bg-white shadow-md rounded-2xl p-6 space-y-4">
        @forelse($loans as $loan)
            @php
                // Calculate fine: 50 Ksh per overdue day
                $fine = 0;
                if (!$loan->is_paid && $loan->status === 'borrowed' && now()->gt($loan->due_at)) {
                    $daysOverdue = now()->diffInDays($loan->due_at);
                    $fine = $daysOverdue * 50;
                }
            @endphp

            <div class="flex justify-between items-center p-4 border rounded-lg hover:bg-gray-50 transition">
                <div>
                    <h3 class="font-semibold text-lg">{{ $loan->book->title }}</h3>
                    <p class="text-gray-600">
                        Status:
                        @if($loan->is_paid)
                            <span class="text-green-600 font-semibold">Paid ✅</span>
                        @elseif($fine > 0)
                            <span class="text-red-600 font-semibold">Overdue</span>
                        @else
                            <span class="text-yellow-600 font-semibold">{{ ucfirst($loan->status) }}</span>
                        @endif
                    </p>
                    <p class="text-gray-600">Due: {{ $loan->due_at?->format('M d, Y') }}</p>
                    @if($fine > 0)
                        <p class="text-red-600 font-semibold">Fine: Ksh {{ $fine }}</p>
                    @endif
                </div>

                <div>
                    @if(!$loan->is_paid)
                        <a href="{{ route('payment.checkout', ['loan' => $loan->id]) }}"
                           class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
                            Pay Now
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">You have no loans at the moment.</p>
        @endforelse

        <div class="mt-6">
            {{ $loans->links() }}
        </div>
    </div>
</div>
@endsection
