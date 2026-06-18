<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // category_post pivot — add post_id index for reverse lookups
        // (loading all categories for a given post)
        Schema::table('category_post', function (Blueprint $table) {
            if (! $this->indexExists('category_post', 'category_post_post_id_index')) {
                $table->index('post_id');
            }
        });

        // post_views — frequently queried by post_id + date for analytics
        if (Schema::hasTable('post_views')) {
            Schema::table('post_views', function (Blueprint $table) {
                if (! $this->indexExists('post_views', 'post_views_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('category_post', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
        });

        if (Schema::hasTable('post_views')) {
            Schema::table('post_views', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
