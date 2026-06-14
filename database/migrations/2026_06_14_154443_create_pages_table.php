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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('show_in_footer')->default(false);
            $table->timestamps();
        });

        // Seed Terms and Privacy pages from existing settings
        $termsContent   = \App\Models\Setting::where('key', 'terms_content')->value('value');
        $privacyContent = \App\Models\Setting::where('key', 'privacy_content')->value('value');

        \DB::table('pages')->insert([
            [
                'title'          => 'Terms of Service',
                'slug'           => 'terms',
                'content'        => $termsContent ?? '',
                'meta_title'     => 'Terms of Service',
                'status'         => 'published',
                'show_in_footer' => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'title'          => 'Privacy Policy',
                'slug'           => 'privacy',
                'content'        => $privacyContent ?? '',
                'meta_title'     => 'Privacy Policy',
                'status'         => 'published',
                'show_in_footer' => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
