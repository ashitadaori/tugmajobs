<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'superadmin'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:admin,superadmin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(User $admin)
    {
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot edit your own account.');
        }

        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot edit your own account.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'role' => ['required', 'in:admin,superadmin'],
            'password' => ['nullable', Password::defaults()],
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $admin->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot remove your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin user removed successfully.');
    }
} 