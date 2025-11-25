@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6">✏️ Edit Loan</h2>

        <form action="{{ route('loans.update', $loan->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- User (read-only) --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">User</label>
                <input type="text" value="{{ $loan->user->name ?? 'N/A' }}" disabled
                    class="w-full p-3 border rounded-lg bg-gray-100 cursor-not-allowed">
            </div>

            {{-- Book (read-only) --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Book</label>
                <input type="text" value="{{ $loan->book->title ?? 'Unknown' }}" disabled
                    class="w-full p-3 border rounded-lg bg-gray-100 cursor-not-allowed">
            </div>

            {{-- Status --}}
            <div class="mb-4">
                <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
                <select name="status" id="status" class="w-full p-3 border rounded-lg">
                    <option value="borrowed" {{ $loan->status === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                    <option value="returned" {{ $loan->status === 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div class="mb-4">
    <label for="is_paid" class="block text-gray-700 font-semibold mb-2">Payment Status</label>
    <select name="is_paid" id="is_paid" class="w-full p-3 border rounded-lg">
        <option value="1" {{ $loan->is_paid ? 'selected' : '' }}>Paid</option>
        <option value="0" {{ !$loan->is_paid ? 'selected' : '' }}>Unpaid</option>
    </select>
</div>

            {{-- Due Date --}}
            <div class="mb-4">
                <label for="due_at" class="block text-gray-700 font-semibold mb-2">Due Date</label>
                <input type="date" name="due_at" id="due_at" value="{{ $loan->due_at?->format('Y-m-d') }}"
                    class="w-full p-3 border rounded-lg">
            </div>

            {{-- Amount --}}
            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-semibold mb-2">Amount (Ksh)</label>
                <input type="number" name="amount" id="amount" value="{{ $loan->amount ?? 0 }}" step="0.01"
                    class="w-full p-3 border rounded-lg">
            </div>

            {{-- Buttons --}}
            <div class="flex items-center">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                    Update Loan
                </button>
                <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}"
                    class="ml-4 text-gray-600 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
