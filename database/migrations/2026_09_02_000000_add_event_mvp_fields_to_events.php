<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add event discovery, location, scheduling, and lifecycle fields. */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('sport', 100)->nullable()->after('slug');
            $table->dateTime('starts_at')->nullable()->after('date_of_event');
            $table->dateTime('photos_live_at')->nullable()->after('starts_at');
            $table->string('timezone', 64)->default('America/New_York')->after('photos_live_at');
            $table->string('venue_name')->nullable()->after('timezone');
            $table->string('city', 120)->nullable()->after('venue_name');
            $table->char('state', 2)->nullable()->after('city');
            $table->char('country_code', 2)->default('US')->after('state');
            $table->string('status', 20)->default('draft')->after('published');

            $table->index(['status', 'date_of_event']);
            $table->index(['state', 'city']);
            $table->index('sport');
        });

        DB::table('events')->where('published', true)->update(['status' => 'published']);
    }

    /** Remove the Event MVP fields. */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex(['status', 'date_of_event']);
            $table->dropIndex(['state', 'city']);
            $table->dropIndex(['sport']);
            $table->dropColumn([
                'sport', 'starts_at', 'photos_live_at', 'timezone', 'venue_name',
                'city', 'state', 'country_code', 'status',
            ]);
        });
    }
};
