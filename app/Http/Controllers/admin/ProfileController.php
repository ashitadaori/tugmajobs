<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the admin profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the admin's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'], // 2MB max
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image) {
                $oldImagePath = public_path('profile_img/' . $user->image);
                $oldThumbPath = public_path('profile_img/thumb/' . $user->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                if (file_exists($oldThumbPath)) {
                    unlink($oldThumbPath);
                }
            }

            // Generate unique filename
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Ensure directory exists
            $uploadPath = public_path('profile_img');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move uploaded file to public/profile_img
            $image->move($uploadPath, $filename);

            // Store just the filename (the accessor will build the full path)
            $user->image = $filename;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the change password form.
     *
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('admin.profile.password');
    }

    /**
     * Update the admin's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.profile.security')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Show the security settings page (password + 2FA).
     *
     * @return \Illuminate\View\View
     */
    public function security()
    {
        return view('admin.profile.security');
    }
} 