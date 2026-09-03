<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->string('title', 160)->nullable()->after('original_filename');
            $table->string('alt_text', 250)->nullable()->after('title');
            $table->text('caption')->nullable()->after('alt_text');
            $table->string('copyright_notice')->nullable()->after('caption');
            $table->json('people')->nullable()->after('copyright_notice');
            $table->timestamp('people_publication_confirmed_at')->nullable()->after('people');
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn([
                'title',
                'alt_text',
                'caption',
                'copyright_notice',
                'people',
                'people_publication_confirmed_at',
            ]);
        });
    }
};
