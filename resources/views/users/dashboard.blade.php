@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto mt-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Welcome back, {{ auth()->user()->name }} 👋</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">📚 Your Borrowed Books</h2>

            @if ($loans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                        <thead class="bg-gray-100 text-gray-700 uppercase">
                            <tr>
                                <th class="py-3 px-4 text-left">Book Title</th>
                                <th class="py-3 px-4 text-left">Borrowed On</th>
                                <th class="py-3 px-4 text-left">Due Date</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Payment Status</th>
                                <th class="py-3 px-4 text-left">Amount (Ksh)</th>
                                <th class="py-3 px-4 text-left">Fine (Ksh)</th>
                                <th class="py-3 px-4 text-left">Total (Ksh)</th>
                                <th class="py-3 px-4 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loans as $loan)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-medium text-gray-800">
                                        {{ $loan->book->title ?? 'Unknown Book' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $loan->borrowed_at?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $loan->due_at?->format('M d, Y') ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $statusClass = match ($loan->status_label) {
                                                'Returned' => 'text-green-600',
                                                'Overdue' => 'text-red-600',
                                                default => 'text-yellow-600',
                                            };
                                        @endphp
                                        <span class="{{ $statusClass }} font-semibold">{{ $loan->status_label }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="{{ $loan->is_paid ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                            {{ $loan->payment_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ number_format($loan->amount ?? 500, 2) }}</td>
                                    <td class="py-3 px-4 text-red-600">
                                        {{ $loan->calculated_fine > 0 ? number_format($loan->calculated_fine, 2) : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold">{{ number_format($loan->calculated_total, 2) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($loan->status === 'borrowed')
                                            <form action="{{ route('loans.return', $loan->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                    Return
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 text-gray-600">
                    <p>You haven’t borrowed any books yet.</p>
                    <a href="{{ route('books.index') }}"
                        class="inline-block mt-4 px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Browse Books
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
