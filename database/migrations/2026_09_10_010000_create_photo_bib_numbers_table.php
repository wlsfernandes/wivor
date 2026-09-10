<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->timestamp('bib_scanned_at')->nullable()->after('processed_at');
        });

        Schema::create('photo_bib_numbers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->string('bib_number', 5)->index();
            $table->decimal('confidence', 5, 2);
            $table->timestamps();

            $table->unique(['photo_id', 'bib_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_bib_numbers');

        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn('bib_scanned_at');
        });
    }
};
