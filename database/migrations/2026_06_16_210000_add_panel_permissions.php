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

        // Create the two panel-access permissions
        $adminPanel  = Permission::firstOrCreate(['name' => 'panel.admin',  'guard_name' => 'web']);
        $authorPanel = Permission::firstOrCreate(['name' => 'panel.author', 'guard_name' => 'web']);

        // admin → both panels
        $admin = Role::findByName('admin', 'web');
        if ($admin) {
            $admin->givePermissionTo([$adminPanel, $authorPanel]);
        }

        // content roles → author panel only
        foreach (['site_editor', 'author', 'editor'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($authorPanel);
            }
        }

        // user role → no panel permissions (intentionally omitted)

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('name', 'panel.admin')->delete();
        Permission::where('name', 'panel.author')->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
