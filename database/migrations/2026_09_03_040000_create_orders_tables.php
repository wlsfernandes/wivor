<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create the sales order ledger and its immutable line items. */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->string('access_token', 64)->unique();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('photographer_id')->constrained()->restrictOnDelete();
            $table->string('customer_email')->nullable();
            $table->char('currency', 3)->default('usd');
            $table->unsignedInteger('photo_count');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('subtotal_cents');
            $table->decimal('commission_percentage', 5, 2);
            $table->unsignedInteger('commission_cents');
            $table->unsignedInteger('photographer_allocation_cents');
            $table->unsignedInteger('stripe_fee_cents')->nullable();
            $table->unsignedInteger('total_cents');
            $table->string('payment_status', 20)->default('pending')->index();
            $table->string('fulfillment_status', 20)->default('pending')->index();
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_connected_account_id')->nullable();
            $table->string('stripe_application_fee_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('photographer_id')->constrained()->restrictOnDelete();
            $table->uuid('photo_uuid');
            $table->string('original_key', 700);
            $table->unsignedInteger('unit_price_cents');
            $table->string('download_status', 20)->default('pending')->index();
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /** Drop the order ledger tables. */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
