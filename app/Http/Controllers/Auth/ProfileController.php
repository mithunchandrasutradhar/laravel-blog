<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile edit form.
     */
    public function edit(Request $request): View
    {
        return view('auth.profile', ['user' => $request->user()]);
    }

    /**
     * Update the user's profile information.
     *
     * Handles name, email, bio, and optional profile image upload.
     * If the email has changed the email_verified_at is cleared so the user
     * must re-verify.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->only(['name', 'bio']);

        // Handle email change
        if ($request->email !== $user->email) {
            $data['email']             = $request->email;
            $data['email_verified_at'] = null;
        }

        // Handle profile image upload — stored in the Users media folder
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $file       = $request->file('profile_image');
            $mimeType   = $file->getMimeType() ?? $file->getClientMimeType();
            $fileSize   = $file->getSize();
            $origName   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName   = Str::slug($origName) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('media/users', $safeName, 'public');

            if ($path === false) {
                return back()->withErrors(['profile_image' => 'Failed to save image. Check storage permissions.'])->withInput();
            }

            $data['profile_image'] = $path;

            $usersFolder = MediaFolder::where('slug', 'users')->first();
            Media::create([
                'name'            => $origName,
                'file_name'       => $path,
                'mime_type'       => $mimeType,
                'disk'            => 'public',
                'size'            => $fileSize,
                'collection_name' => 'users',
                'folder_id'       => $usersFolder?->id,
            ]);
        }

        // Handle password change
        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        $user->fill($data)->save();

        if (isset($data['email'])) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('profile.edit')
                ->with('status', 'profile-updated')
                ->with('info', 'Please verify your new email address.');
        }

        return redirect()->route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Your account has been deleted.');
    }
}
