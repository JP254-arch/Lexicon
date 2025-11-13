@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6">✏️ Edit Loan</h2>

        <form action="{{ route('loans.update', $loan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">User</label>
                <input type="text" value="{{ $loan->user->name ?? 'N/A' }}" disabled
                    class="w-full p-3 border rounded-lg bg-gray-100 cursor-not-allowed">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Book</label>
                <input type="text" value="{{ $loan->book->title ?? 'Unknown' }}" disabled
                    class="w-full p-3 border rounded-lg bg-gray-100 cursor-not-allowed">
            </div>

            <div class="mb-4">
                <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
                <select name="status" id="status" class="w-full p-3 border rounded-lg">
                    <option value="borrowed" {{ $loan->status === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                    <option value="returned" {{ $loan->status === 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="due_at" class="block text-gray-700 font-semibold mb-2">Due Date</label>
                <input type="date" name="due_at" id="due_at" value="{{ $loan->due_at?->format('Y-m-d') }}"
                    class="w-full p-3 border rounded-lg">
            </div>

            <div class="mb-4">
                <label for="total" class="block text-gray-700 font-semibold mb-2">Amount (Ksh)</label>
                <input type="number" name="total" id="total" value="{{ $loan->total ?? 0 }}" step="0.01"
                    class="w-full p-3 border rounded-lg">
            </div>

            <button type="submit"
                class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">Update
                Loan</button>
            <a href="{{ route('admin.dashboard') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
@endsection
