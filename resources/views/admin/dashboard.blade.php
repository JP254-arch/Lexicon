@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-3xl font-bold mb-6">👑 Admin Dashboard</h1>
        <p class="text-gray-600 mb-8">
            Welcome back, {{ auth()->user()->name }}. Here’s what’s happening in your library today.
        </p>

        {{-- Dashboard Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">

            {{-- Books --}}
            <div
                class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-indigo-700 uppercase">Books</h2>
                <p class="text-3xl font-bold text-indigo-800 mt-2">{{ $stats['books'] ?? 0 }}</p>
                <div class="mt-3 space-x-2">
                    <a href="{{ route('books.data') }}"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">📚
                        Manage Books & Data</a>
                    <a href="{{ route('books.create') }}"
                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">➕
                        Add Book</a>
                </div>
            </div>

            {{-- Users --}}
            <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-green-700 uppercase">Users</h2>
                <p class="text-3xl font-bold text-green-800 mt-2">{{ $stats['users'] ?? 0 }}</p>
                <a href="{{ route('users.manage') }}"
                    class="mt-3 inline-block bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Manage
                    Users</a>
            </div>

            {{-- Authors --}}
            <div
                class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-yellow-700 uppercase">Authors</h2>
                <p class="text-3xl font-bold text-yellow-800 mt-2">{{ $stats['authors'] ?? 0 }}</p>
                <a href="{{ route('authors.index') }}"
                    class="mt-3 inline-block bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Manage
                    Authors</a>
            </div>

            {{-- Categories --}}
            <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-purple-700 uppercase">Categories</h2>
                <p class="text-3xl font-bold text-purple-800 mt-2">{{ $stats['categories'] ?? 0 }}</p>
                <a href="{{ route('categories.index') }}"
                    class="mt-3 inline-block bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Manage
                    Categories</a>
            </div>

            {{-- Active Loans --}}
            <div
                class="bg-pink-50 border-l-4 border-pink-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-pink-700 uppercase">Active Loans</h2>
                <p class="text-3xl font-bold text-pink-800 mt-2">{{ $stats['active_loans'] ?? 0 }}</p>
                <a href="{{ route('loans.index') }}"
                    class="mt-3 inline-block bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Manage
                    Loans</a>
            </div>

            {{-- Finance --}}
            <div
                class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-5 shadow-sm hover:shadow-md transition cursor-pointer">
                <h2 class="text-sm font-semibold text-orange-700 uppercase">Finance</h2>
                <p class="text-3xl font-bold text-orange-800 mt-2">{{ number_format($stats['total_revenue'] ?? 0, 2) }} Ksh
                </p>
                <a href="{{ route('finance.index') }}"
                    class="mt-3 inline-block bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Manage
                    Finance</a>
            </div>

        </div>

        {{-- Recent Loans Table --}}
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <h2 class="text-xl font-semibold mb-4 px-6 pt-6">📖 Recent Loans</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">User</th>
                            <th class="px-6 py-3 text-left">Book Title</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Payment Status</th>
                            <th class="px-6 py-3 text-left">Amount (Ksh)</th>
                            <th class="px-6 py-3 text-left">Fine (Ksh)</th>
                            <th class="px-6 py-3 text-left">Total (Ksh)</th>
                            <th class="px-6 py-3 text-left">Due Date</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($recentLoans as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $loan->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $loan->book->title ?? 'Unknown' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match ($loan->status_label) {
                                            'Returned' => 'text-green-600',
                                            'Overdue' => 'text-red-600',
                                            default => 'text-yellow-600',
                                        };
                                    @endphp
                                    <span class="{{ $statusClass }} font-semibold">{{ $loan->status_label }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="{{ $loan->is_paid ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                        {{ $loan->is_paid ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ number_format($loan->amount ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">
                                    {{ $loan->calculated_fine > 0 ? number_format($loan->calculated_fine, 2) : '0.00' }}
                                </td>
                                <td class="px-6 py-4 font-semibold">{{ number_format($loan->calculated_total, 2) }}</td>
                                <td class="px-6 py-4">{{ $loan->due_at?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('loans.edit', $loan->id) }}"
                                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
