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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['adsense', 'banner'])->default('banner');
            $table->enum('position', ['header', 'sidebar', 'in-article', 'footer'])->default('sidebar');
            $table->text('code')->nullable()->comment('Ad script / embed code for adsense or custom HTML');
            $table->string('image')->nullable()->comment('Banner image path');
            $table->string('url')->nullable()->comment('Click-through URL for banner ads');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('position');
            $table->index(['position', 'is_active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
