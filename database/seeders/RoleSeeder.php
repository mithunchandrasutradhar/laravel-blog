<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * All application permissions, grouped by resource.
     *
     * @var array<string, list<string>>
     */
    private array $permissions = [
        'posts' => [
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            'posts.publish',
            'posts.forceDelete',
            'posts.restore',
        ],
        'categories' => [
            'categories.viewAny',
            'categories.create',
            'categories.update',
            'categories.delete',
        ],
        'tags' => [
            'tags.viewAny',
            'tags.create',
            'tags.update',
            'tags.delete',
        ],
        'comments' => [
            'comments.viewAny',
            'comments.approve',
            'comments.reject',
            'comments.delete',
        ],
        'users' => [
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.ban',
        ],
        'media' => [
            'media.viewAny',
            'media.upload',
            'media.delete',
        ],
        'settings' => [
            'settings.view',
            'settings.update',
        ],
        'advertisements' => [
            'advertisements.viewAny',
            'advertisements.create',
            'advertisements.update',
            'advertisements.delete',
        ],
        'subscribers' => [
            'subscribers.viewAny',
            'subscribers.delete',
        ],
        'contact_messages' => [
            'contact_messages.viewAny',
            'contact_messages.delete',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        $allPermissions = collect($this->permissions)->flatten()->unique();

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // -----------------------------------------------------------------------
        // Admin role — full access to everything
        // -----------------------------------------------------------------------
        $adminRole = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(Permission::all());

        // -----------------------------------------------------------------------
        // Author role — can manage own posts, tags, upload media
        // -----------------------------------------------------------------------
        $authorRole = Role::firstOrCreate([
            'name'       => 'author',
            'guard_name' => 'web',
        ]);
        $authorRole->syncPermissions([
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            'categories.viewAny',
            'tags.viewAny',
            'tags.create',
            'media.viewAny',
            'media.upload',
            'media.delete',
            'comments.viewAny',
        ]);

        // -----------------------------------------------------------------------
        // User role — basic authenticated-user permissions
        // -----------------------------------------------------------------------
        $userRole = Role::firstOrCreate([
            'name'       => 'user',
            'guard_name' => 'web',
        ]);
        $userRole->syncPermissions([
            'posts.viewAny',
            'posts.view',
            'categories.viewAny',
            'tags.viewAny',
            'media.upload',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permission Count'],
            [
                ['admin',  $adminRole->permissions()->count()],
                ['author', $authorRole->permissions()->count()],
                ['user',   $userRole->permissions()->count()],
            ]
        );
    }
}
