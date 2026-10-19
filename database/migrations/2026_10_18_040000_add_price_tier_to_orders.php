<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Record which package price an order used. Orders paid with the old bundle discount keep
     * discount_source 'packet'; new package orders use 'tier' and name their package size.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedSmallInteger('price_tier_min_photos')->nullable()->after('discount_percentage');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_discount_source_valid');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_discount_source_valid CHECK (discount_source IN ('packet', 'promo', 'tier'))");
        // IS NOT DISTINCT FROM keeps the check false, not NULL, when discount_source is NULL.
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_price_tier_matches_source CHECK ((discount_source IS NOT DISTINCT FROM 'tier') = (price_tier_min_photos IS NOT NULL))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_price_tier_min_photos_valid CHECK (price_tier_min_photos IN (2, 3, 4, 5, 10))');
    }

    /** Fails on PostgreSQL while any 'tier' order exists, because the old check does not allow it. */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_price_tier_min_photos_valid');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_price_tier_matches_source');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_discount_source_valid');
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_discount_source_valid CHECK (discount_source IN ('packet', 'promo'))");
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('price_tier_min_photos');
        });
    }
};
