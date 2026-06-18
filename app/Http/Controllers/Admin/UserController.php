<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('users.viewAny'), 403);

        $query = User::with('roles')->withCount('posts');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        $users = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('users.create'), 403);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('users.create'), 403);

        $data = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'bio'      => $request->bio,
            'status'   => $request->status ?? 'active',
        ];

        if ($request->hasFile('profile_image')) {
            $file     = $request->file('profile_image');
            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $fileSize = $file->getSize();
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = Str::slug($origName) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('media/users', $safeName, 'public');

            if ($path !== false) {
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
        }

        $user = User::create($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            $user->assignRole('user');
        }

        ActivityLogger::log('user.created', "Created user \"{$user->name}\" ({$user->email})", ['role' => $request->role ?? 'user'], $user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('users.viewAny'), 403);

        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user): View
    {
        abort_if(! auth()->user()->hasPermissionTo('users.update'), 403);

        $user->loadCount(['posts', 'comments']);
        $roles       = Role::orderBy('name')->get();
        $currentRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('users.update'), 403);

        $data = $request->only(['name', 'email', 'bio', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

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
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = null;
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        ActivityLogger::log('user.updated', "Updated user \"{$user->name}\" ({$user->email})", [], $user);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('users.delete'), 403);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        ActivityLogger::log('user.deleted', "Deleted user \"{$user->name}\" ({$user->email})", ['id' => $user->id]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
