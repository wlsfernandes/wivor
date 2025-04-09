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
        Schema::dropIfExists('posts');

        Schema::dropIfExists('post_types');

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // SEO-friendly URL
            $table->boolean('published')->default(false);
            
            $table->timestamp('published_at')->nullable();
            $table->timestamp('date_of_event')->nullable();
            
            $table->string('image_url')->nullable();
            $table->string('file_url')->nullable();

            $table->text('content'); 
            $table->text('summary')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
