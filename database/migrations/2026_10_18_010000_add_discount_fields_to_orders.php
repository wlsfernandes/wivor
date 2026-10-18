<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freeze the pre-discount price and the applied discount on each order so it can be displayed and audited.
     *
     * Existing orders keep null values: they did not record their list price, so it is not reconstructed.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('list_unit_price_cents')->nullable()->after('unit_price_cents');
            $table->string('discount_source', 20)->nullable()->after('list_unit_price_cents');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_source');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_discount_source_valid CHECK (discount_source IN ('packet', 'promo'))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_discount_percentage_range CHECK (discount_percentage BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_discount_source_valid');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_discount_percentage_range');
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['list_unit_price_cents', 'discount_source', 'discount_percentage']);
        });
    }
};
