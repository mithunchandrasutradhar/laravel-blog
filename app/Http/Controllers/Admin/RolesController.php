<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesController extends Controller
{
    // These roles can never be deleted
    private const PROTECTED_FROM_DELETE = ['admin'];

    // The admin role is never shown in the editable list
    private const HIDDEN_ROLES = ['admin'];

    // Module display names — 'panel' must stay first (pinned in groupedPermissions)
    private const MODULE_LABELS = [
        'panel'            => 'Panel Access',
        'posts'            => 'Posts',
        'categories'       => 'Categories',
        'tags'             => 'Tags',
        'comments'         => 'Comments',
        'users'            => 'Users',
        'media'            => 'Media',
        'videos'           => 'Videos',
        'settings'         => 'Settings',
        'advertisements'   => 'Advertisements',
        'subscribers'      => 'Subscribers',
        'contact_messages' => 'Contact Messages',
    ];

    // Human-readable permission action labels
    private const PERMISSION_LABELS = [
        'admin'       => 'Admin Panel',
        'author'      => 'Author Panel',
        'viewAny'     => 'View List',
        'view'        => 'View Detail',
        'create'      => 'Create',
        'update'      => 'Edit',
        'delete'      => 'Delete',
        'publish'     => 'Publish',
        'forceDelete' => 'Force Delete',
        'restore'     => 'Restore',
        'approve'     => 'Approve',
        'reject'      => 'Reject',
        'ban'         => 'Ban',
        'upload'      => 'Upload',
    ];

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $roles = Role::whereNotIn('name', self::HIDDEN_ROLES)
            ->with('permissions')
            ->withCount('permissions')
            ->orderBy('id')
            ->get();

        $totalPermissions = Permission::count();

        return view('admin.roles.index', compact('roles', 'totalPermissions'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $allPermissions   = $this->groupedPermissions();
        $moduleLabels     = self::MODULE_LABELS;
        $permissionLabels = self::PERMISSION_LABELS;
        $assigned         = [];
        $totalPermissions = Permission::count();

        return view('admin.roles.create', compact(
            'allPermissions', 'moduleLabels', 'permissionLabels', 'assigned', 'totalPermissions'
        ));
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'display_name'  => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'color'         => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $slug = Str::slug($data['display_name'], '_');

        // Make slug unique
        $base  = $slug;
        $count = 1;
        while (Role::where('name', $slug)->exists()) {
            $slug = $base . '_' . $count++;
        }

        $role = Role::create([
            'name'         => $slug,
            'guard_name'   => 'web',
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'color'        => $data['color'] ?? '#6c757d',
        ]);

        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLogger::log('role.created', "Created role \"{$data['display_name']}\"", ['name' => $slug], $role);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$data['display_name']}\" created successfully.");
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function edit(Role $role): View
    {
        abort_if(in_array($role->name, self::HIDDEN_ROLES), 403, 'This role cannot be edited.');

        $allPermissions   = $this->groupedPermissions();
        $moduleLabels     = self::MODULE_LABELS;
        $permissionLabels = self::PERMISSION_LABELS;
        $assigned         = $role->permissions->pluck('name')->toArray();
        $totalPermissions = Permission::count();
        $isBuiltIn        = in_array($role->name, ['site_editor', 'author', 'user', 'editor']);

        return view('admin.roles.edit', compact(
            'role', 'allPermissions', 'moduleLabels', 'permissionLabels',
            'assigned', 'totalPermissions', 'isBuiltIn'
        ));
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::HIDDEN_ROLES), 403, 'This role cannot be edited.');

        $data = $request->validate([
            'display_name'  => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'color'         => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update([
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'color'        => $data['color'] ?? $role->color,
        ]);

        $role->syncPermissions($request->input('permissions', []));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLogger::log('role.updated', "Updated role \"{$role->display_name}\"", [], $role);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" updated successfully.");
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::PROTECTED_FROM_DELETE), 403, 'The admin role cannot be deleted.');

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete \"{$role->display_name}\" — {$userCount} user(s) are assigned this role. Reassign them first.");
        }

        $name = $role->display_name ?? $role->name;

        ActivityLogger::log('role.deleted', "Deleted role \"{$name}\"", ['name' => $role->name]);

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$name}\" deleted.");
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function groupedPermissions()
    {
        $grouped = Permission::all()->groupBy(function (Permission $p) {
            return explode('.', $p->name)[0];
        });

        // Pin 'panel' first so dashboard access is always at the top of the matrix
        $panel = $grouped->pull('panel');
        $sorted = $grouped->sortKeys();

        return $panel
            ? collect(['panel' => $panel])->merge($sorted)
            : $sorted;
    }
}
