<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Admin users per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles')->withCount('posts');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        $users = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a new user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'bio'      => $request->bio,
            'status'   => $request->status ?? 'active',
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            $user->assignRole('reader');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show a user detail page.
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    /**
     * Show the edit form for a user.
     */
    public function edit(User $user): View
    {
        $user->loadCount(['posts', 'comments']);
        $roles       = Role::orderBy('name')->get();
        $currentRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Update a user's information and role.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->only(['name', 'email', 'bio', 'status']);

        // Optional password change
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Optional profile image — stored in the Users media folder
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $file       = $request->file('profile_image');
            $safeName   = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path       = $file->storeAs('media/users', $safeName, 'public');

            $data['profile_image'] = $path;

            $usersFolder = MediaFolder::where('slug', 'users')->first();
            Media::create([
                'name'            => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name'       => $path,
                'mime_type'       => $file->getMimeType(),
                'disk'            => 'public',
                'size'            => $file->getSize(),
                'collection_name' => 'users',
                'folder_id'       => $usersFolder?->id,
            ]);
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = null;
        }

        $user->update($data);

        // Update role
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user account (cannot delete own account).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Soft-delete or hard-delete based on your preference.
        // Posts are kept with user_id intact for archival purposes.
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
