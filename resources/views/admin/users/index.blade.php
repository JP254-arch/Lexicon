@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">👥 Manage Users</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 uppercase">
                <tr>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Email</th>
                    <th class="py-3 px-4 text-left">Current Role</th>
                    <th class="py-3 px-4 text-left">Change Role</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $user->name }}</td>
                        <td class="py-3 px-4">{{ $user->email }}</td>
                        <td class="py-3 px-4 capitalize font-medium">{{ $user->role }}</td>
                        <td class="py-3 px-4">
                            <form action="{{ route('users.updateRole', $user) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                                    <option value="member" {{ $user->role == 'member' ? 'selected' : '' }}>Member</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="librarian" {{ $user->role == 'librarian' ? 'selected' : '' }}>Librarian</option>
                                </select>
                            </form>
                        </td>
                        <td class="py-3 px-4">
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
