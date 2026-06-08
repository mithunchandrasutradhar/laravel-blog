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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('collection_name')->default('default');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type', 100)->nullable();
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('size')->default(0)->comment('File size in bytes');
            $table->timestamps();

            $table->index(['model_type', 'model_id'], 'media_model_type_model_id_index');
            $table->index('collection_name');
            $table->index('disk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
