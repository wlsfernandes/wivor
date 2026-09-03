<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographers', function (Blueprint $table): void {
            $table->boolean('stripe_transfers_active')->default(false)->after('stripe_onboarding_status');
            $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_transfers_active');
            $table->boolean('stripe_requirements_due')->default(false)->after('stripe_payouts_enabled');
            $table->timestamp('stripe_requirements_deadline_at')->nullable()->after('stripe_requirements_due');
            $table->timestamp('stripe_last_synced_at')->nullable()->after('stripe_requirements_deadline_at');
            $table->string('stripe_last_event_id')->nullable()->after('stripe_last_synced_at');
            $table->timestamp('stripe_setup_started_at')->nullable()->after('stripe_last_event_id');
            $table->timestamp('stripe_ready_at')->nullable()->after('stripe_setup_started_at');
            $table->timestamp('stripe_restricted_at')->nullable()->after('stripe_ready_at');
            $table->timestamp('stripe_disabled_at')->nullable()->after('stripe_restricted_at');
        });

        // The old "complete" placeholder was not backed by Stripe capability synchronization.
        DB::table('photographers')
            ->where('stripe_onboarding_status', 'complete')
            ->update(['stripe_onboarding_status' => 'incomplete']);
    }

    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_transfers_active',
                'stripe_payouts_enabled',
                'stripe_requirements_due',
                'stripe_requirements_deadline_at',
                'stripe_last_synced_at',
                'stripe_last_event_id',
                'stripe_setup_started_at',
                'stripe_ready_at',
                'stripe_restricted_at',
                'stripe_disabled_at',
            ]);
        });
    }
};
