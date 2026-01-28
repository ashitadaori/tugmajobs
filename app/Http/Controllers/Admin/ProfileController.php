<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

        // Custom validation with better error messages
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'], // 5MB max
        ], [
            'image.image' => 'The file must be an image (jpg, jpeg, png, gif, webp).',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, gif, webp.',
            'image.max' => 'The image must not be larger than 5MB.',
        ]);

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                // Log upload attempt
                Log::info('Admin profile image upload attempt', [
                    'user_id' => $user->id,
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime' => $image->getMimeType(),
                ]);

                // Check if the file is valid
                if (!$image->isValid()) {
                    Log::error('Invalid image upload', ['error' => $image->getErrorMessage()]);
                    return redirect()->back()
                        ->with('error', 'Image upload failed: ' . $image->getErrorMessage())
                        ->withInput();
                }

                // Delete old image if it exists
                if ($user->image) {
                    $oldImagePath = public_path('profile_img/' . $user->image);
                    $oldThumbPath = public_path('profile_img/thumb/' . $user->image);
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                    if (file_exists($oldThumbPath)) {
                        @unlink($oldThumbPath);
                    }
                }

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Ensure directory exists
                $uploadPath = public_path('profile_img');
                if (!file_exists($uploadPath)) {
                    if (!mkdir($uploadPath, 0755, true)) {
                        Log::error('Failed to create profile_img directory');
                        return redirect()->back()
                            ->with('error', 'Failed to create upload directory. Please contact administrator.')
                            ->withInput();
                    }
                }

                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    Log::error('Upload directory not writable', ['path' => $uploadPath]);
                    return redirect()->back()
                        ->with('error', 'Upload directory is not writable. Please contact administrator.')
                        ->withInput();
                }

                // Move uploaded file to public/profile_img
                $image->move($uploadPath, $filename);

                // Verify file was uploaded
                if (!file_exists($uploadPath . '/' . $filename)) {
                    Log::error('File move failed', ['expected_path' => $uploadPath . '/' . $filename]);
                    return redirect()->back()
                        ->with('error', 'Failed to save the uploaded image.')
                        ->withInput();
                }

                // Store just the filename (the accessor will build the full path)
                $user->image = $filename;

                Log::info('Admin profile image uploaded successfully', [
                    'user_id' => $user->id,
                    'filename' => $filename
                ]);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->save();

            return redirect()->route('admin.profile.edit')
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            Log::error('Admin profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update profile: ' . $e->getMessage())
                ->withInput();
        }
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