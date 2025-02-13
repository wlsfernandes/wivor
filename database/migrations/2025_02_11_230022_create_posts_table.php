<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_es')->nullable();
            ;
            $table->string('title_pt')->nullable();
            ;
            $table->text('content_en');
            $table->text('content_es')->nullable();
            ;
            $table->text('content_pt')->nullable();
            ;
            $table->string('slug')->unique(); // SEO-friendly URL
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('date')->nullable();
            $table->string('summary_en')->nullable();
            $table->string('summary_es')->nullable();
            $table->string('summary_pt')->nullable();
            $table->string('image_url')->nullable();
            $table->string('file_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
