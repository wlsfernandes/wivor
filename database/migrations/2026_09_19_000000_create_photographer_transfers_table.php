<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Create one immutable photographer transfer obligation per paid order and photographer. */
    public function up(): void
    {
        Schema::create('photographer_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('photographer_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'photographer_id']);
        });
    }

    /** Drop the photographer transfer ledger. */
    public function down(): void
    {
        Schema::dropIfExists('photographer_transfers');
    }
};
