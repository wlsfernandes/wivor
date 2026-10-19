<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store optional per-event package prices: the price each photo costs once an order
     * reaches 2, 3, 4, 5 or 10 photos. The single-photo price stays on events.price_cents.
     */
    public function up(): void
    {
        Schema::create('event_price_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('min_photos');
            $table->unsignedInteger('price_cents');
            $table->timestamps();

            // One price per package size per event; the leading event_id also serves the foreign-key lookups.
            $table->unique(['event_id', 'min_photos']);
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE event_price_tiers ADD CONSTRAINT event_price_tiers_min_photos_valid CHECK (min_photos IN (2, 3, 4, 5, 10))');
        DB::statement('ALTER TABLE event_price_tiers ADD CONSTRAINT event_price_tiers_price_cents_minimum CHECK (price_cents >= 50)');
    }

    public function down(): void
    {
        Schema::dropIfExists('event_price_tiers');
    }
};
