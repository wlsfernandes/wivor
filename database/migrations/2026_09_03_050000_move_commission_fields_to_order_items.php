<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Move per-photographer commission/allocation/payout fields from the order to its items, so one order can pay multiple photographers. */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->unsignedInteger('commission_cents')->default(0)->after('unit_price_cents');
            $table->unsignedInteger('photographer_allocation_cents')->default(0)->after('commission_cents');
            $table->string('stripe_transfer_id')->nullable()->after('download_expires_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['photographer_id']);
            $table->dropColumn([
                'photographer_id', 'commission_cents', 'photographer_allocation_cents',
                'stripe_connected_account_id', 'stripe_application_fee_id',
            ]);
        });
    }

    /** Restore the single order-level photographer and allocation fields. */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('photographer_id')->nullable()->after('event_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('commission_cents')->nullable();
            $table->unsignedInteger('photographer_allocation_cents')->nullable();
            $table->string('stripe_connected_account_id')->nullable();
            $table->string('stripe_application_fee_id')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['commission_cents', 'photographer_allocation_cents', 'stripe_transfer_id']);
        });
    }
};
