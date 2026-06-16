<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name'       => 'site_editor',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions([
            'posts.viewAny', 'posts.view', 'posts.create', 'posts.update',
            'posts.delete', 'posts.publish', 'posts.forceDelete', 'posts.restore',
            'categories.viewAny', 'categories.create', 'categories.update', 'categories.delete',
            'tags.viewAny', 'tags.create', 'tags.update', 'tags.delete',
            'comments.viewAny', 'comments.approve', 'comments.reject', 'comments.delete',
            'users.viewAny', 'users.view',
            'media.viewAny', 'media.upload', 'media.delete',
            'settings.view',
            'advertisements.viewAny',
            'subscribers.viewAny',
            'contact_messages.viewAny', 'contact_messages.delete',
        ]);
    }

    public function down(): void
    {
        $role = Role::where('name', 'site_editor')->first();
        if ($role) {
            $role->delete();
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
