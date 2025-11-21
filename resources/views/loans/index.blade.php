@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-10">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📘 Manage Loans</h2>

        <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">User</th>
                        <th class="px-6 py-3 text-left">Book Title</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Payment Status</th>
                        <th class="px-6 py-3 text-left">Due Date</th>
                        <th class="px-6 py-3 text-left">Fine (Ksh)</th>
                        <th class="px-6 py-3 text-left">Total (Ksh)</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($loans as $loan)
                        @php
                            // Initialize fine
                            $fine = 0;

                            // Calculate overdue fine (70 Ksh per day)
                            if ($loan->status === 'borrowed' && $loan->due_at && now()->gt($loan->due_at)) {
                                $daysOverdue = now()->diffInDays($loan->due_at);
                                $fine = $daysOverdue * 70; // Updated to 70 to match Recent Loans
                            }

                            $total = ($loan->amount ?? 0) + $fine;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $loop->iteration + ($loans->currentPage() - 1) * $loans->perPage() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $loan->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $loan->book->title ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($loan->status === 'returned')
                                    <span class="text-green-600 font-semibold">Returned</span>
                                @elseif($loan->status === 'borrowed' && now()->gt($loan->due_at))
                                    <span class="text-red-600 font-semibold">Overdue</span>
                                @else
                                    <span class="text-yellow-600 font-semibold">Borrowed</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="{{ $loan->is_paid ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                    {{ $loan->payment_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $loan->due_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-red-600">Ksh {{ $fine }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">Ksh {{ $total }}</td>
                            <td class="px-6 py-4 text-center space-x-2">
                                {{-- Return Loan button --}}
                                @if ($loan->status === 'borrowed')
                                    <form action="{{ route('admin.loans.return', $loan->id) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg">
                                            Return
                                        </button>
                                    </form>
                                @endif

                                {{-- Edit Loan button --}}
                                <a href="{{ route('loans.edit', $loan->id) }}"
                                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg">
                                    Edit
                                </a>

                                {{-- Delete Loan button --}}
                                <form action="{{ route('loans.destroy', $loan->id) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this loan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No loans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $loans->links() }}
        </div>
    </div>
@endsection
