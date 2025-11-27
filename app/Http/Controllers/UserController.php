<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Member Dashboard
    public function index()
    {
        $user = auth()->user();
        $loans = $user->loans()->with(['book'])->latest()->get();
        return view('users.dashboard', compact('user', 'loans'));
    }

    // Admin - Manage Users
    public function manage()
    {
        $users = User::where('id', '!=', auth()->id())->get(); // Exclude current admin
        return view('admin.users.index', compact('users'));
    }

    // Update User Role
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:member,librarian,admin'
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $request->role]);
        return back()->with('success', "{$user->name}'s role updated to {$request->role}.");
    }

    // Delete User
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', "{$user->name} has been deleted successfully.");
    }
}
