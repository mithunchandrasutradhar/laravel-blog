<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name', 100)->nullable()->after('name');
            $table->string('description', 500)->nullable()->after('display_name');
            $table->string('color', 7)->default('#6c757d')->after('description');
        });

        // Seed display names / descriptions / colors for built-in roles
        $built_in = [
            'admin'       => ['display_name' => 'Admin',       'description' => 'Full unrestricted access to everything.',                                                              'color' => '#dc3545'],
            'site_editor' => ['display_name' => 'Site Editor', 'description' => 'Manages all content — posts, categories, comments and media. Cannot manage users or system settings.', 'color' => '#6f42c1'],
            'author'      => ['display_name' => 'Author',      'description' => 'Creates and manages own posts, uploads media, and moderates comments on own posts.',                    'color' => '#0d6efd'],
            'editor'      => ['display_name' => 'Editor',      'description' => 'Content editor role.',                                                                                  'color' => '#20c997'],
            'user'        => ['display_name' => 'User',        'description' => 'Basic authenticated user with read access.',                                                            'color' => '#6c757d'],
        ];

        foreach ($built_in as $name => $meta) {
            DB::table('roles')->where('name', $name)->update($meta);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description', 'color']);
        });
    }
};
