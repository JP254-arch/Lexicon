@extends('layouts.app')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-10">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-md p-8 text-center">
        @if ($status === 'success')
            <div class="text-green-600 text-5xl mb-4">✅</div>
            <h2 class="text-2xl font-bold mb-2">Payment Successful</h2>
        @else
            <div class="text-red-600 text-5xl mb-4">❌</div>
            <h2 class="text-2xl font-bold mb-2">Payment Cancelled</h2>
        @endif

        <p class="text-gray-600 mb-6">{{ $message }}</p>

        <a href="{{ route('dashboard') }}"
           class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition">
            Back to Dashboard
        </a>
    </div>
</section>
@endsection
