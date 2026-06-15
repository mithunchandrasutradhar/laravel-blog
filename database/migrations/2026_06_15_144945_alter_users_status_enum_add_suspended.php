<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change status ENUM from ['active','inactive','banned'] to ['active','inactive','suspended']
        // Any existing 'banned' rows are mapped to 'suspended' first to avoid data loss.
        DB::statement("UPDATE users SET status = 'suspended' WHERE status = 'banned'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET status = 'banned' WHERE status = 'suspended'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','banned') NOT NULL DEFAULT 'active'");
    }
};
