<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('folder_id')
                ->nullable()
                ->after('collection_name')
                ->constrained('media_folders')
                ->nullOnDelete();
        });

        // Seed the default folders
        DB::table('media_folders')->insert([
            ['name' => 'Categories',     'slug' => 'categories',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Posts',          'slug' => 'posts',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Advertisements', 'slug' => 'advertisements', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Users',          'slug' => 'users',          'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });

        Schema::dropIfExists('media_folders');
    }
};
